<?php

declare(strict_types=1);

class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'user_id';

    private ?int $userId = null;
    private int $roleId = 0;
    private string $roleName = '';
    private string $fullName = '';
    private string $email = '';
    private string $passwordHash = '';
    private bool $isActive = true;
    private ?int $riderId = null;

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

    public function getRoleId(): int { return $this->roleId; }
    public function setRoleId(int $roleId): void { $this->roleId = $roleId; }

    public function getRoleName(): string { return $this->roleName; }
    public function setRoleName(string $roleName): void { $this->roleName = $roleName; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): void { $this->fullName = trim($fullName); }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = strtolower(trim($email)); }

    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }

    public function getIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }

    public function getRiderId(): ?int { return $this->riderId; }
    public function setRiderId(?int $riderId): void { $this->riderId = $riderId; }

    public function authenticate(string $email, string $password): ?array
    {
        try {
            $sql = 'SELECT u.user_id, u.role_id, r.role_name, u.full_name, u.email, u.password_hash, u.is_active, rd.rider_id
                    FROM users u
                    JOIN roles r ON r.role_id = u.role_id
                    LEFT JOIN riders rd ON rd.user_id = u.user_id
                    WHERE LOWER(u.email) = LOWER(:email)
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => trim($email)]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                return null;
            }

            if (!(bool) $user['is_active']) {
                throw new RuntimeException('This account is inactive. Please contact the Administrator.');
            }

            return $user;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Authentication query failed.');
        }
    }

    public static function createTypedUser(array $sessionUser): User
    {
        $role = $sessionUser['role_name'] ?? '';
        $instance = ($role === 'Administrator') ? new AdministratorUser() : new RiderUser();
        $instance->setUserId((int) $sessionUser['user_id']);
        $instance->setRoleName($role);
        $instance->setFullName($sessionUser['full_name']);
        $instance->setEmail($sessionUser['email']);
        $instance->setRiderId(isset($sessionUser['rider_id']) ? (int) $sessionUser['rider_id'] : null);
        return $instance;
    }

    public function getDashboardData(): array
    {
        return [];
    }

    public function getMenuItems(): array
    {
        return [];
    }
}
