<div class="card slcard">
    <div class="sl-head">
        <h2>Customer Records</h2>
        <a href="index.php?page=customers&action=create" class="btn sm">+ Add Customer</a>
    </div>

    <form method="get" action="index.php" class="sl-filters">
        <input type="hidden" name="page" value="customers">
        <label>
            Status
            <select name="status" class="f">
                <option value="">All Customers</option>
                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
            </select>
        </label>
        <button type="submit" class="btn sm">Filter</button>
        <a href="index.php?page=customers" class="btn alt sm">Reset</a>
    </form>

    <div class="sl-body">
        <table class="js-datatable" id="customersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Contact Number</th>
                    <th>Deliveries</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>#<?= (int) $c['customer_id'] ?></td>
                        <td class="nm"><?= htmlspecialchars($c['full_name']) ?></td>
                        <td><?= htmlspecialchars($c['contact_no']) ?></td>
                        <td><?= (int) $c['delivery_count'] ?></td>
                        <td>
                            <span class="cpill <?= ((bool) $c['is_active']) ? 'ok' : 'bad' ?>">
                                <?= ((bool) $c['is_active']) ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btns">
                                <a href="index.php?page=customers&action=edit&id=<?= (int) $c['customer_id'] ?>" class="btn alt sm">Edit</a>
                                <form method="post" action="index.php?page=customers&action=delete" style="display:inline;"
                                      data-confirm="<?= ((int) $c['delivery_count'] > 0) ? 'This customer has deliveries and will be deactivated instead of deleted. Continue?' : 'Permanently delete customer ' . htmlspecialchars($c['full_name']) . '?' ?>"
                                      data-confirm-title="Remove Customer">
                                    <input type="hidden" name="customer_id" value="<?= (int) $c['customer_id'] ?>">
                                    <button type="submit" class="btn red sm"><?= ((int) $c['delivery_count'] > 0) ? 'Deactivate' : 'Delete' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
