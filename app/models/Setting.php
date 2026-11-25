<?php

declare(strict_types=1);

class Setting extends BaseModel
{
    protected string $table = 'settings';
    protected string $primaryKey = 'setting_key';

    private string $settingKey = '';
    private string $settingValue = '';
    private ?int $updatedBy = null;

    public function getSettingKey(): string { return $this->settingKey; }
    public function setSettingKey(string $settingKey): void { $this->settingKey = trim($settingKey); }

    public function getSettingValue(): string { return $this->settingValue; }
    public function setSettingValue(string $settingValue): void { $this->settingValue = trim($settingValue); }

    public function getUpdatedBy(): ?int { return $this->updatedBy; }
    public function setUpdatedBy(?int $updatedBy): void { $this->updatedBy = $updatedBy; }

    public function getDeliveryRate(): float
    {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'delivery_rate' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            return $val !== false ? (float) $val : 0.00;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load delivery rate.');
        }
    }

    public function getRateDetails(): array
    {
        try {
            $sql = "SELECT s.setting_key, s.setting_value, s.updated_at, u.full_name AS updated_by_name
                    FROM settings s
                    LEFT JOIN users u ON u.user_id = s.updated_by
                    WHERE s.setting_key = 'delivery_rate'
                    LIMIT 1";
            $row = $this->db->query($sql)->fetch();
            return $row ?: ['setting_key' => 'delivery_rate', 'setting_value' => '0.00', 'updated_at' => null, 'updated_by_name' => 'System'];
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load setting details.');
        }
    }

    public function updateDeliveryRate(float $rate, int $userId): void
    {
        if ($rate < 0) {
            throw new RuntimeException('Delivery rate cannot be negative.');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO settings (setting_key, setting_value, updated_by, updated_at)
                 VALUES ('delivery_rate', :val, :uid, NOW())
                 ON CONFLICT (setting_key)
                 DO UPDATE SET setting_value = EXCLUDED.setting_value,
                               updated_by = EXCLUDED.updated_by,
                               updated_at = NOW()"
            );
            $stmt->execute([
                ':val' => number_format($rate, 2, '.', ''),
                ':uid' => $userId,
            ]);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to update delivery rate.');
        }
    }

    public function getDailyQuota(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'daily_quota' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            return $val !== false ? max(1, (int) $val) : 10;
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to load daily quota.');
        }
    }

    public function updateDailyQuota(int $quota, int $userId): void
    {
        if ($quota < 1) {
            throw new RuntimeException('Daily quota must be at least 1 delivery.');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO settings (setting_key, setting_value, updated_by, updated_at)
                 VALUES ('daily_quota', :val, :uid, NOW())
                 ON CONFLICT (setting_key)
                 DO UPDATE SET setting_value = EXCLUDED.setting_value,
                               updated_by = EXCLUDED.updated_by,
                               updated_at = NOW()"
            );
            $stmt->execute([
                ':val' => (string) $quota,
                ':uid' => $userId,
            ]);
        } catch (PDOException $e) {
            $this->handleDbException($e, 'Failed to update daily quota.');
        }
    }
}
