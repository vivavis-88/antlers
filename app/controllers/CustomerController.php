<?php

declare(strict_types=1);

class CustomerController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $statusFilter = $_GET['status'] ?? '';

        $this->render('customer_list_view', [
            'pageTitle' => 'Customers',
            'pageSubtitle' => 'Manage parcel recipients for delivery registration',
            'customers' => (new Customer())->getAllWithCounts($statusFilter),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('customer_form_view', [
            'pageTitle' => 'Add Customer',
            'pageSubtitle' => 'Register a parcel recipient',
            'customer' => null,
            'errors' => [],
            'old' => ['full_name' => '', 'contact_no' => '', 'is_active' => true],
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=customers');
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
        ];

        $validator = new Validator();
        $validator->required('full_name', $data['full_name'], 'Full name')
                  ->required('contact_no', $data['contact_no'], 'Contact number');

        if ($validator->fails()) {
            $this->render('customer_form_view', [
                'pageTitle' => 'Add Customer',
                'pageSubtitle' => 'Register a parcel recipient',
                'customer' => null,
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            $customer = new Customer();
            $customer->setFullName($data['full_name']);
            $customer->setContactNo($data['contact_no']);
            $customer->setIsActive(true);
            $customer->create();

            Session::setFlash('success', 'Customer added successfully.');
            $this->redirect('index.php?page=customers');
        } catch (RuntimeException $e) {
            $this->render('customer_form_view', [
                'pageTitle' => 'Add Customer',
                'pageSubtitle' => 'Register a parcel recipient',
                'customer' => null,
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $customer = (new Customer())->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer not found.');
            $this->redirect('index.php?page=customers');
        }

        $this->render('customer_form_view', [
            'pageTitle' => 'Edit Customer',
            'pageSubtitle' => 'Update customer contact details',
            'customer' => $customer,
            'errors' => [],
            'old' => $customer,
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=customers');
        }

        $id = (int) ($_POST['customer_id'] ?? 0);
        $customerModel = new Customer();
        $existing = $customerModel->find($id);
        if (!$existing) {
            Session::setFlash('error', 'Customer not found.');
            $this->redirect('index.php?page=customers');
        }

        $data = [
            'customer_id' => $id,
            'full_name' => trim($_POST['full_name'] ?? ''),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
            'is_active' => isset($_POST['is_active']),
        ];

        $validator = new Validator();
        $validator->required('full_name', $data['full_name'], 'Full name')
                  ->required('contact_no', $data['contact_no'], 'Contact number');

        if ($validator->fails()) {
            $this->render('customer_form_view', [
                'pageTitle' => 'Edit Customer',
                'pageSubtitle' => 'Update customer contact details',
                'customer' => $existing,
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            $customerModel->setFullName($data['full_name']);
            $customerModel->setContactNo($data['contact_no']);
            $customerModel->setIsActive($data['is_active']);
            $customerModel->update($id);

            Session::setFlash('success', 'Customer updated successfully.');
            $this->redirect('index.php?page=customers');
        } catch (RuntimeException $e) {
            $this->render('customer_form_view', [
                'pageTitle' => 'Edit Customer',
                'pageSubtitle' => 'Update customer contact details',
                'customer' => $existing,
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=customers');
        }

        $id = (int) ($_POST['customer_id'] ?? 0);
        try {
            $msg = (new Customer())->deleteOrDeactivate($id);
            Session::setFlash('success', $msg);
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=customers');
    }
}
