<?php

declare(strict_types=1);

class RiderUser extends User
{
    public function getDashboardData(): array
    {
        $deliveryModel = new Delivery();
        $settingModel = new Setting();
        $riderId = $this->getRiderId() ?? 0;

        return [
            'mode' => 'Rider',
            'stats' => $deliveryModel->getRiderDashboardStats($riderId),
            'daily_quota' => $settingModel->getDailyQuota(),
            'current_rate' => $settingModel->getDeliveryRate(),
            'assigned_deliveries' => $deliveryModel->getActiveForRider($riderId),
            'recent_verified' => $deliveryModel->getVerifiedForRider($riderId, 5),
        ];
    }

    public function getMenuItems(): array
    {
        return [
            ['page' => 'dashboard', 'label' => 'Overview', 'icon' => 'home'],
            ['page' => 'deliveries', 'label' => 'My Deliveries', 'icon' => 'box'],
            ['page' => 'reports', 'label' => 'My Earnings', 'icon' => 'chart'],
        ];
    }
}
