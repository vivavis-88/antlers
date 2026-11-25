<?php

declare(strict_types=1);

class ReportController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $deliveryModel = new Delivery();
        $restrictRiderId = Session::isRider() ? Session::riderId() : null;

        $statusSummary = Session::isAdmin() ? $deliveryModel->getStatusSummaryReport($dateFrom, $dateTo) : [];
        $earningsReport = $deliveryModel->getRiderEarningsReport($dateFrom, $dateTo, $restrictRiderId);
        $riderStats = Session::isRider() ? $deliveryModel->getRiderDashboardStats((int) $restrictRiderId) : null;
        $dailyQuota = (new Setting())->getDailyQuota();

        $this->render('report_view', [
            'pageTitle' => Session::isAdmin() ? 'Reports & Earnings' : 'My Earnings & Quota',
            'pageSubtitle' => Session::isAdmin() ? 'Delivery status breakdown and verified rider payouts' : 'Track your daily quota and verified delivery payouts',
            'statusSummary' => $statusSummary,
            'earningsReport' => $earningsReport,
            'riderStats' => $riderStats,
            'dailyQuota' => $dailyQuota,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
