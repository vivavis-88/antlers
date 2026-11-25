<?php

declare(strict_types=1);

class RiderController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $statusFilter = $_GET['status'] ?? '';
        $appFilter = $_GET['app_status'] ?? '';

        $appModel = new RiderApplication();

        $this->render('rider_list_view', [
            'pageTitle' => 'Riders & Applications',
            'pageSubtitle' => 'Review rider applications, send Gmail approval notices, and manage accounts',
            'riders' => (new Rider())->getAllWithUsers($statusFilter),
            'applications' => $appModel->getAll($appFilter),
            'pendingAppCount' => $appModel->countPending(),
            'statusFilter' => $statusFilter,
            'appFilter' => $appFilter,
        ]);
    }

    public function approveApplication(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=riders');
        }

        $appId = (int) ($_POST['application_id'] ?? 0);
        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            $result = (new RiderApplication())->approve($appId, (int) Session::userId(), null, $adminNote);
            $mailMsg = $result['mail']['message'] ?? '';
            Session::setFlash('success', "Rider approved with code {$result['rider_code']}. {$mailMsg}");
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=riders');
    }

    public function rejectApplication(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=riders');
        }

        $appId = (int) ($_POST['application_id'] ?? 0);
        $reason = trim($_POST['admin_note'] ?? '');

        try {
            $result = (new RiderApplication())->reject($appId, (int) Session::userId(), $reason);
            $mailMsg = $result['mail']['message'] ?? '';
            Session::setFlash('success', "Application rejected. {$mailMsg}");
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=riders');
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('rider_form_view', [
            'pageTitle' => 'Add Rider',
            'pageSubtitle' => 'Create a rider profile and login credentials',
            'rider' => null,
            'errors' => [],
            'old' => [
                'full_name' => '',
                'email' => '',
                'rider_code' => 'RDR-' . str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT),
                'contact_no' => '',
                'is_active' => true,
            ],
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=riders');
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'password' => $_POST['password'] ?? '',
            'rider_code' => strtoupper(trim($_POST['rider_code'] ?? '')),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
        ];

        $validator = new Validator();
        $validator->required('full_name', $data['full_name'], 'Full name')
                  ->required('email', $data['email'], 'Email')
                  ->email('email', $data['email'], 'Email')
                  ->required('password', $data['password'], 'Password')
                  ->minLength('password', $data['password'], 6, 'Password')
                  ->required('rider_code', $data['rider_code'], 'Rider code')
                  ->pattern('rider_code', $data['rider_code'], '/^[A-Z0-9\-]{3,30}$/', 'Rider code must be 3-30 uppercase letters, numbers, or hyphens.')
                  ->required('contact_no', $data['contact_no'], 'Contact number');

        if ($validator->fails()) {
            $this->render('rider_form_view', [
                'pageTitle' => 'Add Rider',
                'pageSubtitle' => 'Create a rider profile and login credentials',
                'rider' => null,
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            (new Rider())->createWithUser($data['full_name'], $data['email'], $data['password'], $data['rider_code'], $data['contact_no']);
            Session::setFlash('success', 'Rider added successfully.');
            $this->redirect('index.php?page=riders');
        } catch (RuntimeException $e) {
            $this->render('rider_form_view', [
                'pageTitle' => 'Add Rider',
                'pageSubtitle' => 'Create a rider profile and login credentials',
                'rider' => null,
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $rider = (new Rider())->findWithUser($id);

        if (!$rider) {
            Session::setFlash('error', 'Rider not found.');
            $this->redirect('index.php?page=riders');
        }

        $this->render('rider_form_view', [
            'pageTitle' => 'Edit Rider',
            'pageSubtitle' => 'Update rider details or account status',
            'rider' => $rider,
            'errors' => [],
            'old' => $rider,
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=riders');
        }

        $id = (int) ($_POST['rider_id'] ?? 0);
        $riderModel = new Rider();
        $existing = $riderModel->findWithUser($id);

        if (!$existing) {
            Session::setFlash('error', 'Rider not found.');
            $this->redirect('index.php?page=riders');
        }

        $data = [
            'rider_id' => $id,
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'password' => $_POST['password'] ?? '',
            'rider_code' => strtoupper(trim($_POST['rider_code'] ?? '')),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
            'is_active' => isset($_POST['is_active']),
        ];

        $validator = new Validator();
        $validator->required('full_name', $data['full_name'], 'Full name')
                  ->required('email', $data['email'], 'Email')
                  ->email('email', $data['email'], 'Email')
                  ->required('rider_code', $data['rider_code'], 'Rider code')
                  ->pattern('rider_code', $data['rider_code'], '/^[A-Z0-9\-]{3,30}$/', 'Rider code must be 3-30 uppercase letters, numbers, or hyphens.')
                  ->required('contact_no', $data['contact_no'], 'Contact number');

        if ($data['password'] !== '') {
            $validator->minLength('password', $data['password'], 6, 'Password');
        }

        if ($validator->fails()) {
            $this->render('rider_form_view', [
                'pageTitle' => 'Edit Rider',
                'pageSubtitle' => 'Update rider details or account status',
                'rider' => $existing,
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            $riderModel->updateWithUser(
                $id,
                $data['full_name'],
                $data['email'],
                $data['password'] !== '' ? $data['password'] : null,
                $data['rider_code'],
                $data['contact_no'],
                $data['is_active']
            );
            Session::setFlash('success', 'Rider updated successfully.');
            $this->redirect('index.php?page=riders');
        } catch (RuntimeException $e) {
            $this->render('rider_form_view', [
                'pageTitle' => 'Edit Rider',
                'pageSubtitle' => 'Update rider details or account status',
                'rider' => $existing,
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=riders');
        }

        $id = (int) ($_POST['rider_id'] ?? 0);
        try {
            $msg = (new Rider())->deleteOrDeactivate($id);
            Session::setFlash('success', $msg);
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=riders');
    }
}
