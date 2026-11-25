<?php

declare(strict_types=1);

class Customer extends BaseModel
{
    protected string $table = 'customers';
    protected string $primaryKey = 'customer_id';

    private ?int $customerId = null;
    private string $fullName = '';
    private string $contactNo = '';
    private bool $isActive = true;

    public function getCustomerId(): ?int { return $this->customerId; }
    public function setCustomerId(?int $customerId): void { $this->customerId = $customerId; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): void { $this->fullName = trim($fullName); }

    public function getContactNo(): string { return $this->contactNo; }
    public function setContactNo(string $contactNo): void { $this->contactNo = trim($contactNo); }

    public function getIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }

    public function getAllWithCounts(?string $statusFilter = null): array
    {
        try {
            $where = '';
            if ($statusFilter === 'active') {
                $where = 'WHERE c.is_active = TRUE';
            } elseif ($statusFilter === 'inactive') {
                $where = 'WHERE c.is_active = FALSE';
            }

            $sql = "SELECT c.*,
                           (SELECT COUNT(*) FROM deliveries d WHERE d.customer_id = c.customer_id) AS delivery_count
                    FROM customers c
                    {$where}
                    ORDER BY c.customer_id DESC";
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load customers.');
        }
    }

    public function getActiveCustomers(): array
    {
        return $this->getAllWithCounts('active');
    }

    public function create(): int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO customers (full_name, contact_no, is_active)
                 VALUES (:full_name, :contact_no, :is_active)
                 RETURNING customer_id'
            );
            $stmt->execute([
                ':full_name' => $this->fullName,
                ':contact_no' => $this->contactNo,
                ':is_active' => $this->isActive ? 'true' : 'false',
            ]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to create customer.');
        }
    }

    public function update(int $customerId): void
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE customers SET full_name = :full_name, contact_no = :contact_no, is_active = :is_active
                 WHERE customer_id = :id'
            );
            $stmt->execute([
                ':full_name' => $this->fullName,
                ':contact_no' => $this->contactNo,
                ':is_active' => $this->isActive ? 'true' : 'false',
                ':id' => $customerId,
            ]);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to update customer.');
        }
    }

    public function deleteOrDeactivate(int $customerId): string
    {
        try {
            $countStmt = $this->db->prepare('SELECT COUNT(*) FROM deliveries WHERE customer_id = :id');
            $countStmt->execute([':id' => $customerId]);
            $hasDeliveries = (int) $countStmt->fetchColumn() > 0;

            if ($hasDeliveries) {
                $this->db->prepare('UPDATE customers SET is_active = FALSE WHERE customer_id = :id')->execute([':id' => $customerId]);
                return 'Customer has existing deliveries and was deactivated instead of deleted.';
            }

            $this->deleteById($customerId);
            return 'Customer deleted permanently.';
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to remove customer.');
        }
    }
}
