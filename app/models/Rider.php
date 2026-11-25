<?php

declare(strict_types=1);

class Rider extends BaseModel
{
    protected string $table = 'riders';
    protected string $primaryKey = 'rider_id';

    private ?int $riderId = null;
    private int $userId = 0;
    private string $riderCode = '';
    private string $contactNo = '';
    private bool $isActive = true;

    public function getRiderId(): ?int { return $this->riderId; }
    public function setRiderId(?int $riderId): void { $this->riderId = $riderId; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): void { $this->userId = $userId; }

    public function getRiderCode(): string { return $this->riderCode; }
    public function setRiderCode(string $riderCode): void { $this->riderCode = strtoupper(trim($riderCode)); }

    public function getContactNo(): string { return $this->contactNo; }
    public function setContactNo(string $contactNo): void { $this->contactNo = trim($contactNo); }

    public function getIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }

    public function getAllWithUsers(?string $statusFilter = null): array
    {
        try {
            $where = '';
            $params = [];
            if ($statusFilter === 'active') {
                $where = 'WHERE r.is_active = TRUE';
            } elseif ($statusFilter === 'inactive') {
                $where = 'WHERE r.is_active = FALSE';
            }

            $sql = "SELECT r.rider_id, r.user_id, r.rider_code, r.contact_no, r.is_active, r.created_at,
                           u.full_name, u.email,
                           (SELECT COUNT(*) FROM deliveries d WHERE d.rider_id = r.rider_id) AS delivery_count
                    FROM riders r
                    JOIN users u ON u.user_id = r.user_id
                    {$where}
                    ORDER BY r.rider_id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load riders.');
        }
    }

    public function getActiveRiders(): array
    {
        return $this->getAllWithUsers('active');
    }

    public function findWithUser(int $riderId): ?array
    {
        try {
            $sql = 'SELECT r.*, u.full_name, u.email, u.is_active AS user_active
                    FROM riders r
                    JOIN users u ON u.user_id = r.user_id
                    WHERE r.rider_id = :id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $riderId]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load rider details.');
        }
    }

    public function createWithUser(string $fullName, string $email, string $password, string $riderCode, string $contactNo): int
    {
        try {
            $this->db->beginTransaction();

            $roleStmt = $this->db->prepare("SELECT role_id FROM roles WHERE role_name = 'Rider' LIMIT 1");
            $roleStmt->execute();
            $roleId = (int) $roleStmt->fetchColumn();

            $userStmt = $this->db->prepare(
                'INSERT INTO users (role_id, full_name, email, password_hash, is_active)
                 VALUES (:role_id, :full_name, :email, :password_hash, TRUE)
                 RETURNING user_id'
            );
            $userStmt->execute([
                ':role_id' => $roleId,
                ':full_name' => trim($fullName),
                ':email' => strtolower(trim($email)),
                ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ]);
            $userId = (int) $userStmt->fetchColumn();

            $riderStmt = $this->db->prepare(
                'INSERT INTO riders (user_id, rider_code, contact_no, is_active)
                 VALUES (:user_id, :rider_code, :contact_no, TRUE)
                 RETURNING rider_id'
            );
            $riderStmt->execute([
                ':user_id' => $userId,
                ':rider_code' => strtoupper(trim($riderCode)),
                ':contact_no' => trim($contactNo),
            ]);
            $riderId = (int) $riderStmt->fetchColumn();

            $this->db->commit();
            return $riderId;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to create rider.');
        }
    }

    public function updateWithUser(int $riderId, string $fullName, string $email, ?string $password, string $riderCode, string $contactNo, bool $isActive): void
    {
        try {
            $existing = $this->findWithUser($riderId);
            if (!$existing) {
                throw new RuntimeException('Rider not found.');
            }

            $this->db->beginTransaction();

            $userSql = 'UPDATE users SET full_name = :full_name, email = :email, is_active = :is_active';
            $userParams = [
                ':full_name' => trim($fullName),
                ':email' => strtolower(trim($email)),
                ':is_active' => $isActive ? 'true' : 'false',
                ':user_id' => (int) $existing['user_id'],
            ];

            if ($password !== null && trim($password) !== '') {
                $userSql .= ', password_hash = :password_hash';
                $userParams[':password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $userSql .= ' WHERE user_id = :user_id';
            $this->db->prepare($userSql)->execute($userParams);

            $riderStmt = $this->db->prepare(
                'UPDATE riders SET rider_code = :rider_code, contact_no = :contact_no, is_active = :is_active
                 WHERE rider_id = :rider_id'
            );
            $riderStmt->execute([
                ':rider_code' => strtoupper(trim($riderCode)),
                ':contact_no' => trim($contactNo),
                ':is_active' => $isActive ? 'true' : 'false',
                ':rider_id' => $riderId,
            ]);

            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to update rider.');
        }
    }

    public function deleteOrDeactivate(int $riderId): string
    {
        try {
            $existing = $this->findWithUser($riderId);
            if (!$existing) {
                throw new RuntimeException('Rider not found.');
            }

            $countStmt = $this->db->prepare('SELECT COUNT(*) FROM deliveries WHERE rider_id = :id');
            $countStmt->execute([':id' => $riderId]);
            $hasDeliveries = (int) $countStmt->fetchColumn() > 0;

            $this->db->beginTransaction();
            if ($hasDeliveries) {
                $this->db->prepare('UPDATE riders SET is_active = FALSE WHERE rider_id = :id')->execute([':id' => $riderId]);
                $this->db->prepare('UPDATE users SET is_active = FALSE WHERE user_id = :uid')->execute([':uid' => (int) $existing['user_id']]);
                $this->db->commit();
                return 'Rider has existing deliveries and was deactivated instead of deleted.';
            }

            $this->db->prepare('DELETE FROM riders WHERE rider_id = :id')->execute([':id' => $riderId]);
            $this->db->prepare('DELETE FROM users WHERE user_id = :uid')->execute([':uid' => (int) $existing['user_id']]);
            $this->db->commit();
            return 'Rider deleted permanently.';
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->handleDbException($e, 'Failed to remove rider.');
        }
    }

    public function countActive(): int
    {
        try {
            return (int) $this->db->query('SELECT COUNT(*) FROM riders WHERE is_active = TRUE')->fetchColumn();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to count active riders.');
        }
    }
}
