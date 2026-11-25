<?php

declare(strict_types=1);

class DeliveryController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $deliveryModel = new Delivery();
        $riderModel = new Rider();

        $filters = [
            'status_id' => $_GET['status_id'] ?? '',
            'rider_id' => $_GET['rider_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $restrictRiderId = Session::isRider() ? Session::riderId() : null;
        $deliveries = $deliveryModel->getFiltered($filters, $restrictRiderId);

        $this->render('delivery_list_view', [
            'pageTitle' => Session::isAdmin() ? 'Deliveries' : 'My Deliveries',
            'pageSubtitle' => Session::isAdmin() ? 'Manage parcels, rider assignments, and proof verification' : 'Update assigned deliveries and submit proof',
            'deliveries' => $deliveries,
            'statuses' => $deliveryModel->getAllStatuses(),
            'riders' => Session::isAdmin() ? $riderModel->getAllWithUsers() : [],
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('delivery_form_view', [
            'pageTitle' => 'Register Delivery',
            'pageSubtitle' => 'Create a new parcel record and optionally assign a rider',
            'delivery' => null,
            'customers' => (new Customer())->getActiveCustomers(),
            'riders' => (new Rider())->getActiveRiders(),
            'errors' => [],
            'old' => [
                'tracking_code' => 'MEM-' . date('Y') . '-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                'customer_id' => '',
                'rider_id' => '',
                'parcel_description' => '',
                'delivery_address' => '',
            ],
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=deliveries');
        }

        $data = [
            'tracking_code' => strtoupper(trim($_POST['tracking_code'] ?? '')),
            'customer_id' => $_POST['customer_id'] ?? '',
            'rider_id' => $_POST['rider_id'] ?? '',
            'parcel_description' => trim($_POST['parcel_description'] ?? ''),
            'delivery_address' => trim($_POST['delivery_address'] ?? ''),
        ];

        $validator = new Validator();
        $validator->required('tracking_code', $data['tracking_code'], 'Tracking code')
                  ->pattern('tracking_code', $data['tracking_code'], '/^[A-Z0-9\-]{5,40}$/', 'Tracking code must be 5-40 uppercase letters, numbers, or hyphens.')
                  ->required('customer_id', $data['customer_id'], 'Customer')
                  ->required('parcel_description', $data['parcel_description'], 'Parcel description')
                  ->required('delivery_address', $data['delivery_address'], 'Delivery address');

        if ($validator->fails()) {
            $this->render('delivery_form_view', [
                'pageTitle' => 'Register Delivery',
                'pageSubtitle' => 'Create a new parcel record and optionally assign a rider',
                'delivery' => null,
                'customers' => (new Customer())->getActiveCustomers(),
                'riders' => (new Rider())->getActiveRiders(),
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            $delivery = new Delivery();
            $delivery->setTrackingCode($data['tracking_code']);
            $delivery->setCustomerId((int) $data['customer_id']);
            $delivery->setRiderId($data['rider_id'] !== '' ? (int) $data['rider_id'] : null);
            $delivery->setParcelDescription($data['parcel_description']);
            $delivery->setDeliveryAddress($data['delivery_address']);

            $id = $delivery->create((int) Session::userId());
            Session::setFlash('success', 'Delivery registered successfully.');
            $this->redirect('index.php?page=deliveries&action=detail&id=' . $id);
        } catch (RuntimeException $e) {
            $this->render('delivery_form_view', [
                'pageTitle' => 'Register Delivery',
                'pageSubtitle' => 'Create a new parcel record and optionally assign a rider',
                'delivery' => null,
                'customers' => (new Customer())->getActiveCustomers(),
                'riders' => (new Rider())->getActiveRiders(),
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $delivery = (new Delivery())->findDetailed($id);

        if (!$delivery) {
            Session::setFlash('error', 'Delivery not found.');
            $this->redirect('index.php?page=deliveries');
        }

        $this->render('delivery_form_view', [
            'pageTitle' => 'Edit Delivery',
            'pageSubtitle' => 'Update parcel details or rider assignment',
            'delivery' => $delivery,
            'customers' => (new Customer())->getActiveCustomers(),
            'riders' => (new Rider())->getActiveRiders(),
            'errors' => [],
            'old' => $delivery,
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=deliveries');
        }

        $id = (int) ($_POST['delivery_id'] ?? 0);
        $deliveryModel = new Delivery();
        $existing = $deliveryModel->findDetailed($id);
        if (!$existing) {
            Session::setFlash('error', 'Delivery not found.');
            $this->redirect('index.php?page=deliveries');
        }

        $data = [
            'delivery_id' => $id,
            'tracking_code' => strtoupper(trim($_POST['tracking_code'] ?? '')),
            'customer_id' => $_POST['customer_id'] ?? '',
            'rider_id' => $_POST['rider_id'] ?? '',
            'parcel_description' => trim($_POST['parcel_description'] ?? ''),
            'delivery_address' => trim($_POST['delivery_address'] ?? ''),
        ];

        $validator = new Validator();
        $validator->required('tracking_code', $data['tracking_code'], 'Tracking code')
                  ->pattern('tracking_code', $data['tracking_code'], '/^[A-Z0-9\-]{5,40}$/', 'Tracking code must be 5-40 uppercase letters, numbers, or hyphens.')
                  ->required('customer_id', $data['customer_id'], 'Customer')
                  ->required('parcel_description', $data['parcel_description'], 'Parcel description')
                  ->required('delivery_address', $data['delivery_address'], 'Delivery address');

        if ($validator->fails()) {
            $this->render('delivery_form_view', [
                'pageTitle' => 'Edit Delivery',
                'pageSubtitle' => 'Update parcel details or rider assignment',
                'delivery' => $existing,
                'customers' => (new Customer())->getActiveCustomers(),
                'riders' => (new Rider())->getActiveRiders(),
                'errors' => $validator->errors(),
                'old' => $data,
            ]);
            return;
        }

        try {
            $deliveryModel->setTrackingCode($data['tracking_code']);
            $deliveryModel->setCustomerId((int) $data['customer_id']);
            $deliveryModel->setRiderId($data['rider_id'] !== '' ? (int) $data['rider_id'] : null);
            $deliveryModel->setParcelDescription($data['parcel_description']);
            $deliveryModel->setDeliveryAddress($data['delivery_address']);

            $deliveryModel->updateDetails($id, (int) Session::userId());
            Session::setFlash('success', 'Delivery updated successfully.');
            $this->redirect('index.php?page=deliveries&action=detail&id=' . $id);
        } catch (RuntimeException $e) {
            $this->render('delivery_form_view', [
                'pageTitle' => 'Edit Delivery',
                'pageSubtitle' => 'Update parcel details or rider assignment',
                'delivery' => $existing,
                'customers' => (new Customer())->getActiveCustomers(),
                'riders' => (new Rider())->getActiveRiders(),
                'errors' => ['db' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function detail(): void
    {
        $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findDetailed($id);

        if (!$delivery) {
            Session::setFlash('error', 'Delivery not found.');
            $this->redirect('index.php?page=deliveries');
        }

        if (Session::isRider() && (int) $delivery['rider_id'] !== (int) Session::riderId()) {
            Session::setFlash('error', 'You can only view deliveries assigned to you.');
            $this->redirect('index.php?page=deliveries');
        }

        $events = (new DeliveryEvent())->getForDelivery($id);
        $allowedNext = Delivery::getAllowedNextStatuses($delivery['status_name']);

        $this->render('delivery_detail_view', [
            'pageTitle' => 'Delivery ' . $delivery['tracking_code'],
            'pageSubtitle' => 'Audit trail, proof submission, and status controls',
            'delivery' => $delivery,
            'events' => $events,
            'allowedNext' => $allowedNext,
            'currentRate' => (new Setting())->getDeliveryRate(),
        ]);
    }

    public function transition(): void
    {
        $this->requireLogin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=deliveries');
        }

        $id = (int) ($_POST['delivery_id'] ?? 0);
        $targetStatus = trim($_POST['target_status'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $proofNote = isset($_POST['proof_note']) ? trim($_POST['proof_note']) : null;
        $checks = $_POST['checks'] ?? [];
        if (is_array($checks) && !empty($checks)) {
            $checkSummary = 'Item verified (' . count($checks) . '/3 checks)';
            $note = $note !== '' ? ($note . ' — ' . $checkSummary) : $checkSummary;
        }

        try {
            (new Delivery())->transitionStatus(
                $id,
                $targetStatus,
                (int) Session::userId(),
                Session::role(),
                Session::riderId(),
                $note,
                $proofNote
            );
            Session::setFlash('success', "Status updated to {$targetStatus}.");
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=deliveries&action=detail&id=' . $id);
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('index.php?page=deliveries');
        }

        $id = (int) ($_POST['delivery_id'] ?? 0);
        try {
            (new Delivery())->deleteIfRegistered($id);
            Session::setFlash('success', 'Delivery deleted permanently.');
        } catch (RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('index.php?page=deliveries');
    }
}
