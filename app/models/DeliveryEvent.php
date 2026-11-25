<?php

declare(strict_types=1);

class DeliveryEvent extends BaseModel
{
    protected string $table = 'delivery_events';
    protected string $primaryKey = 'event_id';

    private ?int $eventId = null;
    private int $deliveryId = 0;
    private int $statusId = 0;
    private int $changedBy = 0;
    private string $note = '';

    public function getEventId(): ?int { return $this->eventId; }
    public function setEventId(?int $eventId): void { $this->eventId = $eventId; }

    public function getDeliveryId(): int { return $this->deliveryId; }
    public function setDeliveryId(int $deliveryId): void { $this->deliveryId = $deliveryId; }

    public function getStatusId(): int { return $this->statusId; }
    public function setStatusId(int $statusId): void { $this->statusId = $statusId; }

    public function getChangedBy(): int { return $this->changedBy; }
    public function setChangedBy(int $changedBy): void { $this->changedBy = $changedBy; }

    public function getNote(): string { return $this->note; }
    public function setNote(string $note): void { $this->note = trim($note); }

    public function log(int $deliveryId, int $statusId, int $changedBy, string $note = ''): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO delivery_events (delivery_id, status_id, changed_by, note)
                 VALUES (:delivery_id, :status_id, :changed_by, :note)'
            );
            $stmt->execute([
                ':delivery_id' => $deliveryId,
                ':status_id' => $statusId,
                ':changed_by' => $changedBy,
                ':note' => trim($note) !== '' ? trim($note) : null,
            ]);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to record delivery audit event.');
        }
    }

    public function getForDelivery(int $deliveryId): array
    {
        try {
            $sql = 'SELECT e.event_id, e.note, e.created_at, s.status_name, u.full_name AS actor_name, r.role_name AS actor_role
                    FROM delivery_events e
                    JOIN delivery_statuses s ON s.status_id = e.status_id
                    JOIN users u ON u.user_id = e.changed_by
                    JOIN roles r ON r.role_id = u.role_id
                    WHERE e.delivery_id = :delivery_id
                    ORDER BY e.created_at DESC, e.event_id DESC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':delivery_id' => $deliveryId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load audit trail.');
        }
    }

    public function getRecent(int $limit = 8): array
    {
        try {
            $sql = 'SELECT e.event_id, e.note, e.created_at, d.delivery_id, d.tracking_code,
                           s.status_name, u.full_name AS actor_name
                    FROM delivery_events e
                    JOIN deliveries d ON d.delivery_id = e.delivery_id
                    JOIN delivery_statuses s ON s.status_id = e.status_id
                    JOIN users u ON u.user_id = e.changed_by
                    ORDER BY e.created_at DESC, e.event_id DESC
                    LIMIT :lim';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load recent events.');
        }
    }
}
