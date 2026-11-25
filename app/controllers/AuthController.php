<?php

declare(strict_types=1);

class AuthController extends BaseController
{
    private function getAuthShowcaseData(): array
    {
        try {
            $setting = new Setting();
            return [
                'currentRate' => $setting->getDeliveryRate(),
                'dailyQuota' => $setting->getDailyQuota(),
            ];
        } catch (Throwable $e) {
            return [
                'currentRate' => 75.00,
                'dailyQuota' => 10,
            ];
        }
    }

    public function index(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('index.php?page=dashboard');
        }

        $this->render('login_view', array_merge([
            'pageTitle' => 'Sign In',
            'hideShell' => true,
            'errors' => [],
            'old' => ['email' => ''],
        ], $this->getAuthShowcaseData()));
    }

    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=auth');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = new Validator();
        $validator->required('email', $email, 'Email')
                  ->email('email', $email, 'Email')
                  ->required('password', $password, 'Password');

        if ($validator->fails()) {
            $this->render('login_view', [
                'pageTitle' => 'Sign In',
                'hideShell' => true,
                'errors' => $validator->errors(),
                'old' => ['email' => $email],
            ]);
            return;
        }

        try {
            $userModel = new User();
            $user = $userModel->authenticate($email, $password);

            if (!$user) {
                $app = (new RiderApplication())->findByEmail($email);
                $msg = 'Invalid email or password.';
                if ($app && $app['status'] === 'Pending') {
                    $msg = 'Your rider application is still pending Administrator approval. You will be notified on your Gmail once approved.';
                } elseif ($app && $app['status'] === 'Rejected') {
                    $msg = 'Your rider application was not approved.' . (!empty($app['admin_note']) ? ' Reason: ' . $app['admin_note'] : '');
                }

                $this->render('login_view', [
                    'pageTitle' => 'Sign In',
                    'hideShell' => true,
                    'errors' => ['auth' => $msg],
                    'old' => ['email' => $email],
                ]);
                return;
            }

            Session::loginUser($user);
            Session::setFlash('success', 'Welcome back, ' . $user['full_name'] . '.');
            $this->redirect('index.php?page=dashboard');
        } catch (RuntimeException $e) {
            $this->render('login_view', [
                'pageTitle' => 'Sign In',
                'hideShell' => true,
                'errors' => ['auth' => $e->getMessage()],
                'old' => ['email' => $email],
            ]);
        }
    }

    public function register(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('index.php?page=dashboard');
        }

        $this->render('register_view', array_merge([
            'pageTitle' => 'Rider Application',
            'hideShell' => true,
            'errors' => [],
            'old' => [
                'full_name' => '',
                'email' => '',
                'contact_no' => '',
                'vehicle_type' => 'Motorcycle',
                'license_no' => '',
            ],
        ], $this->getAuthShowcaseData()));
    }

    public function submitApplication(): void
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=auth&action=register');
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
            'vehicle_type' => trim($_POST['vehicle_type'] ?? 'Motorcycle'),
            'license_no' => strtoupper(trim($_POST['license_no'] ?? '')),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
        ];

        $validator = new Validator();
        $validator->required('full_name', $data['full_name'], 'Full Name')
                  ->required('email', $data['email'], 'Email Address')
                  ->email('email', $data['email'], 'Email Address')
                  ->required('contact_no', $data['contact_no'], 'Contact Number')
                  ->required('vehicle_type', $data['vehicle_type'], 'Vehicle Type')
                  ->required('license_no', $data['license_no'], 'License Number')
                  ->required('password', $data['password'], 'Password')
                  ->minLength('password', $data['password'], 6, 'Password')
                  ->required('confirm_password', $data['confirm_password'], 'Confirm Password');

        if ($data['password'] !== '' && $data['password'] !== $data['confirm_password']) {
            $validator->addError('confirm_password', 'Passwords do not match.');
        }

        if ($validator->fails()) {
            $this->render('register_view', [
                'pageTitle' => 'Rider Application',
                'hideShell' => true,
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            (new RiderApplication())->submit(
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['contact_no'],
                $data['vehicle_type'],
                $data['license_no']
            );
            Session::setFlash('success', 'Rider application submitted! An Administrator will review it and notify you on your Gmail (' . $data['email'] . ') once approved.');
            $this->redirect('index.php?page=auth');
        } catch (RuntimeException $e) {
            $this->render('register_view', [
                'pageTitle' => 'Rider Application',
                'hideShell' => true,
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function logout(): void
    {
        Session::logout();
        Session::start();
        Session::setFlash('success', 'You have been signed out.');
        $this->redirect('index.php?page=auth');
    }
}
