<?php

declare(strict_types=1);

abstract class BaseModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function find(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to fetch record.');
        }
    }

    public function all(string $orderBy = ''): array
    {
        try {
            $order = $orderBy !== '' ? " ORDER BY {$orderBy}" : " ORDER BY {$this->primaryKey} DESC";
            $sql = "SELECT * FROM {$this->table}{$order}";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load records.');
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Cannot delete record because it is referenced by other records.');
        }
    }

    protected function handleDbException(PDOException $e, string $friendlyMessage): never
    {
        error_log('DB Error [' . $e->getCode() . ']: ' . $e->getMessage());
        $sqlState = (string) $e->getCode();

        if ($sqlState === '23505') {
            throw new RuntimeException('A duplicate value already exists for a unique field.');
        }
        if ($sqlState === '23503') {
            throw new RuntimeException('Action blocked by a database relationship constraint.');
        }
        if ($sqlState === '23514') {
            throw new RuntimeException('A value violates a database check constraint.');
        }
        if ($sqlState === '23502') {
            throw new RuntimeException('A required database field was left empty.');
        }

        throw new RuntimeException($friendlyMessage);
    }
}
