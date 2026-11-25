<?php

declare(strict_types=1);

class SettingController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $settingModel = new Setting();

        $this->render('setting_view', [
            'pageTitle' => 'Operations Settings',
            'pageSubtitle' => 'Configure per-delivery payout rate, daily rider quota, and Gmail notifications',
            'rateInfo' => $settingModel->getRateDetails(),
            'dailyQuota' => $settingModel->getDailyQuota(),
            'mailConfigured' => (new Mailer())->isConfigured(),
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=settings');
        }

        $rateRaw = trim($_POST['delivery_rate'] ?? '');
        $quotaRaw = trim($_POST['daily_quota'] ?? '10');

        $validator = new Validator();
        $validator->required('delivery_rate', $rateRaw, 'Delivery rate')
                  ->positiveNumber('delivery_rate', $rateRaw, 'Delivery rate')
                  ->required('daily_quota', $quotaRaw, 'Daily rider quota')
                  ->positiveNumber('daily_quota', $quotaRaw, 'Daily rider quota');

        if ($quotaRaw !== '' && is_numeric($quotaRaw) && (int) $quotaRaw < 1) {
            $validator->addError('daily_quota', 'Daily rider quota must be at least 1.');
        }

        $settingModel = new Setting();

        if ($validator->fails()) {
            $this->render('setting_view', [
                'pageTitle' => 'Operations Settings',
                'pageSubtitle' => 'Configure per-delivery payout rate, daily rider quota, and Gmail notifications',
                'rateInfo' => $settingModel->getRateDetails(),
                'dailyQuota' => (int) $quotaRaw,
                'mailConfigured' => (new Mailer())->isConfigured(),
                'errors' => $validator->errors(),
            ]);
            return;
        }

        try {
            $uid = (int) Session::userId();
            $settingModel->updateDeliveryRate((float) $rateRaw, $uid);
            $settingModel->updateDailyQuota((int) $quotaRaw, $uid);
            Session::setFlash('success', 'Settings updated. Future verifications and rider quota targets will use these values.');
            $this->redirect('index.php?page=settings');
        } catch (RuntimeException $e) {
            $this->render('setting_view', [
                'pageTitle' => 'Operations Settings',
                'pageSubtitle' => 'Configure per-delivery payout rate, daily rider quota, and Gmail notifications',
                'rateInfo' => $settingModel->getRateDetails(),
                'dailyQuota' => (int) $quotaRaw,
                'mailConfigured' => (new Mailer())->isConfigured(),
                'errors' => ['db' => $e->getMessage()],
            ]);
        }
    }
}
