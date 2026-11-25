<?php

declare(strict_types=1);

class RiderApplication extends BaseModel
{
    protected string $table = 'rider_applications';
    protected string $primaryKey = 'application_id';

    private ?int $applicationId = null;
    private string $fullName = '';
    private string $email = '';
    private string $passwordHash = '';
    private string $contactNo = '';
    private string $vehicleType = 'Motorcycle';
    private string $licenseNo = '';
    private string $status = 'Pending';

    public function getApplicationId(): ?int { return $this->applicationId; }
    public function setApplicationId(?int $applicationId): void { $this->applicationId = $applicationId; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): void { $this->fullName = trim($fullName); }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = strtolower(trim($email)); }

    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }

    public function getContactNo(): string { return $this->contactNo; }
    public function setContactNo(string $contactNo): void { $this->contactNo = trim($contactNo); }

    public function getVehicleType(): string { return $this->vehicleType; }
    public function setVehicleType(string $vehicleType): void { $this->vehicleType = trim($vehicleType); }

    public function getLicenseNo(): string { return $this->licenseNo; }
    public function setLicenseNo(string $licenseNo): void { $this->licenseNo = strtoupper(trim($licenseNo)); }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = trim($status); }

    public function submit(string $fullName, string $email, string $password, string $contactNo, string $vehicleType, string $licenseNo): int
    {
        $normalizedEmail = strtolower(trim($email));

        try {
            $userCheck = $this->db->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
            $userCheck->execute([':email' => $normalizedEmail]);
            if ($userCheck->fetchColumn() !== false) {
                throw new RuntimeException('An account with this email already exists. Please sign in instead.');
            }

            $appCheck = $this->db->prepare('SELECT status FROM rider_applications WHERE LOWER(email) = LOWER(:email) LIMIT 1');
            $appCheck->execute([':email' => $normalizedEmail]);
            $existingStatus = $appCheck->fetchColumn();
            if ($existingStatus !== false) {
                if ($existingStatus === 'Pending') {
                    throw new RuntimeException('A rider application with this email is already pending Administrator approval.');
                }
                if ($existingStatus === 'Approved') {
                    throw new RuntimeException('This email has already been approved. Please sign in.');
                }
                // Allow re-applying if previously rejected by updating the record back to Pending
                $update = $this->db->prepare(
                    "UPDATE rider_applications
                     SET full_name = :full_name,
                         password_hash = :password_hash,
                         contact_no = :contact_no,
                         vehicle_type = :vehicle_type,
                         license_no = :license_no,
                         status = 'Pending',
                         admin_note = NULL,
                         reviewed_by = NULL,
                         reviewed_at = NULL,
                         email_sent = FALSE,
                         created_at = NOW()
                     WHERE LOWER(email) = LOWER(:email)
                     RETURNING application_id"
                );
                $update->execute([
                    ':full_name' => trim($fullName),
                    ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    ':contact_no' => trim($contactNo),
                    ':vehicle_type' => trim($vehicleType),
                    ':license_no' => strtoupper(trim($licenseNo)),
                    ':email' => $normalizedEmail,
                ]);
                return (int) $update->fetchColumn();
            }

            $stmt = $this->db->prepare(
                "INSERT INTO rider_applications (full_name, email, password_hash, contact_no, vehicle_type, license_no, status)
                 VALUES (:full_name, :email, :password_hash, :contact_no, :vehicle_type, :license_no, 'Pending')
                 RETURNING application_id"
            );
            $stmt->execute([
                ':full_name' => trim($fullName),
                ':email' => $normalizedEmail,
                ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
                ':contact_no' => trim($contactNo),
                ':vehicle_type' => trim($vehicleType),
                ':license_no' => strtoupper(trim($licenseNo)),
            ]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to submit rider application.');
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM rider_applications WHERE LOWER(email) = LOWER(:email) LIMIT 1');
            $stmt->execute([':email' => strtolower(trim($email))]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to check application status.');
        }
    }

    public function getAll(?string $statusFilter = null): array
    {
        try {
            $where = '';
            $params = [];
            if (in_array($statusFilter, ['Pending', 'Approved', 'Rejected'], true)) {
                $where = 'WHERE a.status = :status';
                $params[':status'] = $statusFilter;
            }

            $sql = "SELECT a.*, u.full_name AS reviewed_by_name
                    FROM rider_applications a
                    LEFT JOIN users u ON u.user_id = a.reviewed_by
                    {$where}
                    ORDER BY CASE WHEN a.status = 'Pending' THEN 0 ELSE 1 END, a.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load rider applications.');
        }
    }

    public function getPendingList(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM rider_applications
                 WHERE status = 'Pending'
                 ORDER BY created_at ASC
                 LIMIT :lim"
            );
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load pending rider applications.');
        }
    }

    public function countPending(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM rider_applications WHERE status = 'Pending'")->fetchColumn();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to count pending rider applications.');
        }
    }

    public function generateNextRiderCode(): string
    {
        try {
            $maxId = (int) $this->db->query('SELECT COALESCE(MAX(rider_id), 0) FROM riders')->fetchColumn();
            $candidate = $maxId + 1;
            for ($i = 0; $i < 50; $i++) {
                $code = 'RDR-' . str_pad((string) ($candidate + $i), 3, '0', STR_PAD_LEFT);
                $stmt = $this->db->prepare('SELECT 1 FROM riders WHERE rider_code = :code LIMIT 1');
                $stmt->execute([':code' => $code]);
                if ($stmt->fetchColumn() === false) {
                    return $code;
                }
            }
            return 'RDR-' . random_int(100, 999);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to generate rider code.');
        }
    }

    public function approve(int $applicationId, int $adminUserId, ?string $riderCode = null, string $adminNote = ''): array
    {
        try {
            $app = $this->find($applicationId);
            if (!$app) {
                throw new RuntimeException('Rider application not found.');
            }
            if ($app['status'] !== 'Pending') {
                throw new RuntimeException('Only pending applications can be approved.');
            }

            $code = ($riderCode !== null && trim($riderCode) !== '')
                ? strtoupper(trim($riderCode))
                : $this->generateNextRiderCode();

            $this->db->beginTransaction();

            $roleId = (int) $this->db->query("SELECT role_id FROM roles WHERE role_name = 'Rider' LIMIT 1")->fetchColumn();

            $userStmt = $this->db->prepare(
                'INSERT INTO users (role_id, full_name, email, password_hash, is_active)
                 VALUES (:role_id, :full_name, :email, :password_hash, TRUE)
                 RETURNING user_id'
            );
            $userStmt->execute([
                ':role_id' => $roleId,
                ':full_name' => $app['full_name'],
                ':email' => $app['email'],
                ':password_hash' => $app['password_hash'],
            ]);
            $userId = (int) $userStmt->fetchColumn();

            $riderStmt = $this->db->prepare(
                'INSERT INTO riders (user_id, rider_code, contact_no, is_active)
                 VALUES (:user_id, :rider_code, :contact_no, TRUE)'
            );
            $riderStmt->execute([
                ':user_id' => $userId,
                ':rider_code' => $code,
                ':contact_no' => $app['contact_no'],
            ]);

            $this->db->prepare(
                "UPDATE rider_applications
                 SET status = 'Approved',
                     admin_note = :note,
                     reviewed_by = :admin_id,
                     reviewed_at = NOW()
                 WHERE application_id = :id"
            )->execute([
                ':note' => trim($adminNote) !== '' ? trim($adminNote) : "Approved with rider code {$code}",
                ':admin_id' => $adminUserId,
                ':id' => $applicationId,
            ]);

            $this->db->commit();

            $mailer = new Mailer();
            $mailResult = $mailer->sendRiderApprovalEmail($app['email'], $app['full_name'], $code, trim($adminNote));

            if (!empty($mailResult['sent'])) {
                $this->db->prepare('UPDATE rider_applications SET email_sent = TRUE WHERE application_id = :id')
                    ->execute([':id' => $applicationId]);
            }

            return [
                'rider_code' => $code,
                'email' => $app['email'],
                'mail' => $mailResult,
            ];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to approve rider application.');
        }
    }

    public function reject(int $applicationId, int $adminUserId, string $reason): array
    {
        if (trim($reason) === '') {
            throw new RuntimeException('Please provide a reason for rejecting this application.');
        }

        try {
            $app = $this->find($applicationId);
            if (!$app) {
                throw new RuntimeException('Rider application not found.');
            }
            if ($app['status'] !== 'Pending') {
                throw new RuntimeException('Only pending applications can be rejected.');
            }

            $this->db->prepare(
                "UPDATE rider_applications
                 SET status = 'Rejected',
                     admin_note = :note,
                     reviewed_by = :admin_id,
                     reviewed_at = NOW()
                 WHERE application_id = :id"
            )->execute([
                ':note' => trim($reason),
                ':admin_id' => $adminUserId,
                ':id' => $applicationId,
            ]);

            $mailer = new Mailer();
            $mailResult = $mailer->sendRiderRejectionEmail($app['email'], $app['full_name'], trim($reason));

            if (!empty($mailResult['sent'])) {
                $this->db->prepare('UPDATE rider_applications SET email_sent = TRUE WHERE application_id = :id')
                    ->execute([':id' => $applicationId]);
            }

            return [
                'email' => $app['email'],
                'mail' => $mailResult,
            ];
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to reject rider application.');
        }
    }
}
