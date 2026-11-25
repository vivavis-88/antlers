<?php

declare(strict_types=1);

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $typedUser = User::createTypedUser(Session::user());
        $dashboardData = $typedUser->getDashboardData();

        $this->render('dashboard_view', [
            'pageTitle' => 'Overview',
            'pageSubtitle' => Session::isAdmin() ? 'Operations & verification summary' : 'Your assigned deliveries & verified earnings',
            'dashboardData' => $dashboardData,
        ]);
    }
}
