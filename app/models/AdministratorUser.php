<?php

declare(strict_types=1);

class AdministratorUser extends User
{
    public function getDashboardData(): array
    {
        $deliveryModel = new Delivery();
        $riderModel = new Rider();
        $settingModel = new Setting();
        $appModel = new RiderApplication();

        return [
            'mode' => 'Administrator',
            'stats' => $deliveryModel->getAdminDashboardStats(),
            'active_riders' => $riderModel->countActive(),
            'current_rate' => $settingModel->getDeliveryRate(),
            'daily_quota' => $settingModel->getDailyQuota(),
            'pending_applications_count' => $appModel->countPending(),
            'pending_applications' => $appModel->getPendingList(5),
            'recent_events' => (new DeliveryEvent())->getRecent(8),
            'pending_verification' => $deliveryModel->getPendingVerificationList(),
        ];
    }

    public function getMenuItems(): array
    {
        return [
            ['page' => 'dashboard', 'label' => 'Overview', 'icon' => 'home'],
            ['page' => 'deliveries', 'label' => 'Deliveries', 'icon' => 'box'],
            ['page' => 'riders', 'label' => 'Riders', 'icon' => 'users'],
            ['page' => 'customers', 'label' => 'Customers', 'icon' => 'user'],
            ['page' => 'reports', 'label' => 'Reports', 'icon' => 'chart'],
            ['page' => 'settings', 'label' => 'Settings', 'icon' => 'sliders'],
        ];
    }
}
