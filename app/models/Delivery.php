<?php

declare(strict_types=1);

class Delivery extends BaseModel
{
    protected string $table = 'deliveries';
    protected string $primaryKey = 'delivery_id';

    private ?int $deliveryId = null;
    private string $trackingCode = '';
    private int $customerId = 0;
    private ?int $riderId = null;
    private int $statusId = 0;
    private string $parcelDescription = '';
    private string $deliveryAddress = '';
    private ?string $proofNote = null;

    private const ALLOWED_TRANSITIONS = [
        'Registered' => ['Assigned', 'Cancelled'],
        'Assigned' => ['Out for Delivery', 'Cancelled'],
        'Out for Delivery' => ['Verified', 'Delivered (Pending Verification)', 'Failed'],
        'Delivered (Pending Verification)' => ['Verified', 'Verification Rejected'],
        'Verification Rejected' => ['Out for Delivery'],
        'Verified' => [],
        'Failed' => [],
        'Cancelled' => [],
    ];

    public function getDeliveryId(): ?int { return $this->deliveryId; }
    public function setDeliveryId(?int $deliveryId): void { $this->deliveryId = $deliveryId; }

    public function getTrackingCode(): string { return $this->trackingCode; }
    public function setTrackingCode(string $trackingCode): void { $this->trackingCode = strtoupper(trim($trackingCode)); }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): void { $this->customerId = $customerId; }

    public function getRiderId(): ?int { return $this->riderId; }
    public function setRiderId(?int $riderId): void { $this->riderId = $riderId; }

    public function getStatusId(): int { return $this->statusId; }
    public function setStatusId(int $statusId): void { $this->statusId = $statusId; }

    public function getParcelDescription(): string { return $this->parcelDescription; }
    public function setParcelDescription(string $parcelDescription): void { $this->parcelDescription = trim($parcelDescription); }

    public function getDeliveryAddress(): string { return $this->deliveryAddress; }
    public function setDeliveryAddress(string $deliveryAddress): void { $this->deliveryAddress = trim($deliveryAddress); }

    public function getProofNote(): ?string { return $this->proofNote; }
    public function setProofNote(?string $proofNote): void { $this->proofNote = $proofNote !== null ? trim($proofNote) : null; }

    public static function getAllowedNextStatuses(string $currentStatus): array
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
    }

    public function getAllStatuses(): array
    {
        try {
            return $this->db->query('SELECT status_id, status_name FROM delivery_statuses ORDER BY status_id ASC')->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load statuses.');
        }
    }

    public function getStatusIdByName(string $statusName): int
    {
        try {
            $stmt = $this->db->prepare('SELECT status_id FROM delivery_statuses WHERE status_name = :name LIMIT 1');
            $stmt->execute([':name' => $statusName]);
            $id = $stmt->fetchColumn();
            if ($id === false) {
                throw new RuntimeException("Invalid delivery status: {$statusName}");
            }
            return (int) $id;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to resolve delivery status.');
        }
    }

    public function getFiltered(array $filters = [], ?int $restrictRiderId = null): array
    {
        try {
            $conditions = [];
            $params = [];

            if ($restrictRiderId !== null) {
                $conditions[] = 'd.rider_id = :restrict_rider_id';
                $params[':restrict_rider_id'] = $restrictRiderId;
            } elseif (!empty($filters['rider_id'])) {
                $conditions[] = 'd.rider_id = :rider_id';
                $params[':rider_id'] = (int) $filters['rider_id'];
            }

            if (!empty($filters['status_id'])) {
                $conditions[] = 'd.status_id = :status_id';
                $params[':status_id'] = (int) $filters['status_id'];
            }

            if (!empty($filters['date_from'])) {
                $conditions[] = 'd.created_at::date >= :date_from';
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $conditions[] = 'd.created_at::date <= :date_to';
                $params[':date_to'] = $filters['date_to'];
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $sql = "SELECT d.*, s.status_name,
                           c.full_name AS customer_name, c.contact_no AS customer_contact,
                           r.rider_code, u.full_name AS rider_name,
                           vu.full_name AS verified_by_name
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    JOIN customers c ON c.customer_id = d.customer_id
                    LEFT JOIN riders r ON r.rider_id = d.rider_id
                    LEFT JOIN users u ON u.user_id = r.user_id
                    LEFT JOIN users vu ON vu.user_id = d.verified_by
                    {$where}
                    ORDER BY d.created_at DESC, d.delivery_id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load deliveries.');
        }
    }

    public function findDetailed(int $deliveryId): ?array
    {
        try {
            $sql = 'SELECT d.*, s.status_name,
                           c.full_name AS customer_name, c.contact_no AS customer_contact,
                           r.rider_code, u.full_name AS rider_name,
                           vu.full_name AS verified_by_name
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    JOIN customers c ON c.customer_id = d.customer_id
                    LEFT JOIN riders r ON r.rider_id = d.rider_id
                    LEFT JOIN users u ON u.user_id = r.user_id
                    LEFT JOIN users vu ON vu.user_id = d.verified_by
                    WHERE d.delivery_id = :id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $deliveryId]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load delivery details.');
        }
    }

    public function create(int $actorUserId): int
    {
        try {
            $this->db->beginTransaction();

            $initialStatusName = $this->riderId !== null ? 'Assigned' : 'Registered';
            $initialStatusId = $this->getStatusIdByName($initialStatusName);

            $stmt = $this->db->prepare(
                'INSERT INTO deliveries (tracking_code, customer_id, rider_id, status_id, parcel_description, delivery_address)
                 VALUES (:tracking_code, :customer_id, :rider_id, :status_id, :parcel_description, :delivery_address)
                 RETURNING delivery_id'
            );
            $stmt->execute([
                ':tracking_code' => $this->trackingCode,
                ':customer_id' => $this->customerId,
                ':rider_id' => $this->riderId,
                ':status_id' => $initialStatusId,
                ':parcel_description' => $this->parcelDescription,
                ':delivery_address' => $this->deliveryAddress,
            ]);
            $deliveryId = (int) $stmt->fetchColumn();

            $eventModel = new DeliveryEvent();
            $registeredStatusId = $this->getStatusIdByName('Registered');
            $eventModel->log($deliveryId, $registeredStatusId, $actorUserId, 'Delivery registered');

            if ($this->riderId !== null) {
                $eventModel->log($deliveryId, $initialStatusId, $actorUserId, 'Assigned to rider on registration');
            }

            $this->db->commit();
            return $deliveryId;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to register delivery.');
        }
    }

    public function updateDetails(int $deliveryId, int $actorUserId): void
    {
        try {
            $existing = $this->findDetailed($deliveryId);
            if (!$existing) {
                throw new RuntimeException('Delivery not found.');
            }

            if (in_array($existing['status_name'], ['Verified', 'Failed', 'Cancelled'], true)) {
                throw new RuntimeException('Finalized deliveries cannot be edited.');
            }

            $this->db->beginTransaction();

            $newStatusId = (int) $existing['status_id'];
            $logAssignment = false;

            if ($existing['status_name'] === 'Registered' && $this->riderId !== null) {
                $newStatusId = $this->getStatusIdByName('Assigned');
                $logAssignment = true;
            } elseif ((int) ($existing['rider_id'] ?? 0) !== (int) ($this->riderId ?? 0) && $this->riderId !== null) {
                $logAssignment = true;
            }

            $stmt = $this->db->prepare(
                'UPDATE deliveries
                 SET tracking_code = :tracking_code,
                     customer_id = :customer_id,
                     rider_id = :rider_id,
                     status_id = :status_id,
                     parcel_description = :parcel_description,
                     delivery_address = :delivery_address
                 WHERE delivery_id = :id'
            );
            $stmt->execute([
                ':tracking_code' => $this->trackingCode,
                ':customer_id' => $this->customerId,
                ':rider_id' => $this->riderId,
                ':status_id' => $newStatusId,
                ':parcel_description' => $this->parcelDescription,
                ':delivery_address' => $this->deliveryAddress,
                ':id' => $deliveryId,
            ]);

            if ($logAssignment) {
                (new DeliveryEvent())->log($deliveryId, $newStatusId, $actorUserId, 'Rider assignment updated');
            }

            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to update delivery.');
        }
    }

    public function transitionStatus(int $deliveryId, string $targetStatusName, int $actorUserId, string $actorRole, ?int $actorRiderId, string $note = '', ?string $proofNote = null): void
    {
        try {
            $delivery = $this->findDetailed($deliveryId);
            if (!$delivery) {
                throw new RuntimeException('Delivery not found.');
            }

            $currentStatus = $delivery['status_name'];
            $allowed = self::getAllowedNextStatuses($currentStatus);

            if (!in_array($targetStatusName, $allowed, true)) {
                throw new RuntimeException("Transition from {$currentStatus} to {$targetStatusName} is not allowed.");
            }

            if ($actorRole === 'Rider') {
                if ((int) $delivery['rider_id'] !== (int) $actorRiderId) {
                    throw new RuntimeException('You can only update deliveries assigned to you.');
                }
                if (!in_array($targetStatusName, ['Out for Delivery', 'Verified', 'Delivered (Pending Verification)', 'Failed'], true)) {
                    throw new RuntimeException('Riders are not permitted to set this status.');
                }
            } elseif ($actorRole === 'Administrator') {
                if (in_array($targetStatusName, ['Out for Delivery', 'Delivered (Pending Verification)', 'Failed'], true)) {
                    throw new RuntimeException('Only the assigned rider can perform delivery execution updates.');
                }
            }

            if ($targetStatusName === 'Assigned' && empty($delivery['rider_id'])) {
                throw new RuntimeException('Select a rider before assigning the delivery.');
            }

            if ($targetStatusName === 'Delivered (Pending Verification)' && trim((string) $proofNote) === '') {
                throw new RuntimeException('Proof note or confirmation code is required when marking a delivery as Delivered.');
            }

            if ($targetStatusName === 'Verified' && $actorRole === 'Rider') {
                $effectiveProof = trim((string) ($proofNote ?? '')) !== '' ? trim((string) $proofNote) : trim((string) ($delivery['proof_note'] ?? ''));
                if ($effectiveProof === '') {
                    throw new RuntimeException('Proof note or confirmation code is required to verify this item.');
                }
            }

            if ($targetStatusName === 'Verification Rejected' && trim($note) === '') {
                throw new RuntimeException('A rejection reason is required when rejecting proof.');
            }

            $targetStatusId = $this->getStatusIdByName($targetStatusName);

            $this->db->beginTransaction();

            if ($targetStatusName === 'Delivered (Pending Verification)') {
                $stmt = $this->db->prepare('UPDATE deliveries SET status_id = :sid, proof_note = :proof WHERE delivery_id = :id');
                $stmt->execute([
                    ':sid' => $targetStatusId,
                    ':proof' => trim((string) $proofNote),
                    ':id' => $deliveryId,
                ]);
            } elseif ($targetStatusName === 'Verified') {
                $rate = (new Setting())->getDeliveryRate();
                $finalProof = trim((string) ($proofNote ?? '')) !== '' ? trim((string) $proofNote) : ($delivery['proof_note'] ?? null);
                $stmt = $this->db->prepare(
                    'UPDATE deliveries
                     SET status_id = :sid,
                         proof_note = COALESCE(:proof, proof_note),
                         verified_by = :uid,
                         verified_at = NOW(),
                         rate_applied = :rate
                     WHERE delivery_id = :id'
                );
                $stmt->execute([
                    ':sid' => $targetStatusId,
                    ':proof' => $finalProof,
                    ':uid' => $actorUserId,
                    ':rate' => number_format($rate, 2, '.', ''),
                    ':id' => $deliveryId,
                ]);
                if (trim($note) === '') {
                    $verifierLabel = ($actorRole === 'Rider') ? 'Item verified by rider' : 'Proof verified by administrator';
                    $note = $verifierLabel . '; rate P' . number_format($rate, 2) . ' applied';
                }
            } else {
                $stmt = $this->db->prepare('UPDATE deliveries SET status_id = :sid WHERE delivery_id = :id');
                $stmt->execute([
                    ':sid' => $targetStatusId,
                    ':id' => $deliveryId,
                ]);
            }

            (new DeliveryEvent())->log($deliveryId, $targetStatusId, $actorUserId, $note);

            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to change delivery status.');
        }
    }

    public function deleteIfRegistered(int $deliveryId): void
    {
        $delivery = $this->findDetailed($deliveryId);
        if (!$delivery) {
            throw new RuntimeException('Delivery not found.');
        }
        if ($delivery['status_name'] !== 'Registered') {
            throw new RuntimeException('Only deliveries in Registered status can be deleted. Use Cancel instead.');
        }
        $this->deleteById($deliveryId);
    }

    public function getAdminDashboardStats(): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) AS total_deliveries,
                        COUNT(*) FILTER (WHERE s.status_name = 'Delivered (Pending Verification)') AS pending_verification,
                        COUNT(*) FILTER (WHERE s.status_name = 'Verified') AS verified_count,
                        COALESCE(SUM(d.rate_applied) FILTER (WHERE s.status_name = 'Verified'), 0) AS total_payout
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id";
            return $this->db->query($sql)->fetch();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load dashboard statistics.');
        }
    }

    public function getRiderDashboardStats(int $riderId): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) FILTER (WHERE s.status_name IN ('Assigned', 'Out for Delivery', 'Verification Rejected')) AS active_count,
                        COUNT(*) FILTER (WHERE s.status_name = 'Delivered (Pending Verification)') AS pending_count,
                        COUNT(*) FILTER (WHERE s.status_name = 'Verified') AS verified_count,
                        COALESCE(SUM(d.rate_applied) FILTER (WHERE s.status_name = 'Verified'), 0) AS total_earnings,
                        COUNT(*) FILTER (WHERE s.status_name = 'Verified' AND d.verified_at::date = CURRENT_DATE) AS today_verified_count,
                        COALESCE(SUM(d.rate_applied) FILTER (WHERE s.status_name = 'Verified' AND d.verified_at::date = CURRENT_DATE), 0) AS today_earnings
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    WHERE d.rider_id = :rider_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':rider_id' => $riderId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load rider statistics.');
        }
    }

    public function getPendingVerificationList(): array
    {
        try {
            $sql = "SELECT d.delivery_id, d.tracking_code, d.proof_note, d.created_at,
                           c.full_name AS customer_name, u.full_name AS rider_name
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    JOIN customers c ON c.customer_id = d.customer_id
                    LEFT JOIN riders r ON r.rider_id = d.rider_id
                    LEFT JOIN users u ON u.user_id = r.user_id
                    WHERE s.status_name = 'Delivered (Pending Verification)'
                    ORDER BY d.created_at ASC
                    LIMIT 10";
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load pending verifications.');
        }
    }

    public function getActiveForRider(int $riderId): array
    {
        try {
            $sql = "SELECT d.delivery_id, d.tracking_code, d.delivery_address, s.status_name, c.full_name AS customer_name
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    JOIN customers c ON c.customer_id = d.customer_id
                    WHERE d.rider_id = :rider_id
                      AND s.status_name IN ('Assigned', 'Out for Delivery', 'Verification Rejected', 'Delivered (Pending Verification)')
                    ORDER BY d.created_at DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':rider_id' => $riderId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load assigned deliveries.');
        }
    }

    public function getVerifiedForRider(int $riderId, int $limit = 5): array
    {
        try {
            $sql = "SELECT d.delivery_id, d.tracking_code, d.verified_at, d.rate_applied, c.full_name AS customer_name
                    FROM deliveries d
                    JOIN delivery_statuses s ON s.status_id = d.status_id
                    JOIN customers c ON c.customer_id = d.customer_id
                    WHERE d.rider_id = :rider_id AND s.status_name = 'Verified'
                    ORDER BY d.verified_at DESC
                    LIMIT :lim";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':rider_id', $riderId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load verified deliveries.');
        }
    }

    public function getStatusSummaryReport(?string $dateFrom, ?string $dateTo): array
    {
        try {
            $conditions = [];
            $params = [];
            if (!empty($dateFrom)) {
                $conditions[] = 'd.created_at::date >= :date_from';
                $params[':date_from'] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $conditions[] = 'd.created_at::date <= :date_to';
                $params[':date_to'] = $dateTo;
            }
            $joinFilter = $conditions ? ' AND ' . implode(' AND ', $conditions) : '';

            $sql = "SELECT s.status_name, COUNT(d.delivery_id) AS total_count
                    FROM delivery_statuses s
                    LEFT JOIN deliveries d ON d.status_id = s.status_id{$joinFilter}
                    GROUP BY s.status_id, s.status_name
                    ORDER BY s.status_id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to generate delivery summary report.');
        }
    }

    public function getRiderEarningsReport(?string $dateFrom, ?string $dateTo, ?int $restrictRiderId = null): array
    {
        try {
            $filterConditions = ["s.status_name = 'Verified'"];
            $params = [];

            if (!empty($dateFrom)) {
                $filterConditions[] = 'd.verified_at::date >= :date_from';
                $params[':date_from'] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $filterConditions[] = 'd.verified_at::date <= :date_to';
                $params[':date_to'] = $dateTo;
            }

            $whereRider = '';
            if ($restrictRiderId !== null) {
                $whereRider = 'WHERE r.rider_id = :restrict_rider_id';
                $params[':restrict_rider_id'] = $restrictRiderId;
            }

            $filterSql = implode(' AND ', $filterConditions);

            $sql = "SELECT r.rider_id, r.rider_code, u.full_name AS rider_name, r.is_active,
                           COUNT(d.delivery_id) FILTER (WHERE {$filterSql}) AS verified_count,
                           COALESCE(SUM(d.rate_applied) FILTER (WHERE {$filterSql}), 0) AS total_earnings
                    FROM riders r
                    JOIN users u ON u.user_id = r.user_id
                    LEFT JOIN deliveries d ON d.rider_id = r.rider_id
                    LEFT JOIN delivery_statuses s ON s.status_id = d.status_id
                    {$whereRider}
                    GROUP BY r.rider_id, r.rider_code, u.full_name, r.is_active
                    ORDER BY total_earnings DESC, r.rider_code ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to generate rider earnings report.');
        }
    }
}
