<?php
function statusPillClass(string $status): string {
    return match ($status) {
        'Verified' => 'ok',
        'Failed', 'Cancelled', 'Verification Rejected' => 'bad',
        'Delivered (Pending Verification)' => 'warn',
        default => 'info',
    };
}
?>

<div class="card slcard">
    <div class="sl-head">
        <h2>Delivery Records</h2>
        <?php if (Session::isAdmin()): ?>
            <a href="index.php?page=deliveries&action=create" class="btn sm">+ Register Delivery</a>
        <?php endif; ?>
    </div>

    <form method="get" action="index.php" class="sl-filters">
        <input type="hidden" name="page" value="deliveries">
        <label>
            Status
            <select name="status_id" class="f">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= (int) $st['status_id'] ?>" <?= ((string) ($filters['status_id'] ?? '') === (string) $st['status_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['status_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php if (Session::isAdmin()): ?>
            <label>
                Rider
                <select name="rider_id" class="f">
                    <option value="">All Riders</option>
                    <?php foreach ($riders as $r): ?>
                        <option value="<?= (int) $r['rider_id'] ?>" <?= ((string) ($filters['rider_id'] ?? '') === (string) $r['rider_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['rider_code'] . ' - ' . $r['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endif; ?>

        <label>
            Date From
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
        </label>

        <label>
            Date To
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </label>

        <button type="button" class="btn alt sm" data-fill-today title="Fill today's date">
            <svg class="i-xs" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span data-i18n-text="Fill Date Today">Fill Date Today</span>
        </button>
        <button type="submit" class="btn sm" data-i18n-text="Filter">Filter</button>
        <a href="index.php?page=deliveries" class="btn alt sm" data-i18n-text="Reset">Reset</a>
    </form>

    <div class="sl-body">
        <table class="js-datatable" id="salesTable">
            <thead>
                <tr>
                    <th>Tracking</th>
                    <th>Customer</th>
                    <th>Rider</th>
                    <th>Status</th>
                    <th class="n">Rate</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $d): ?>
                    <tr>
                        <td class="nm"><?= htmlspecialchars($d['tracking_code']) ?></td>
                        <td><?= htmlspecialchars($d['customer_name']) ?></td>
                        <td><?= htmlspecialchars($d['rider_name'] ? ($d['rider_code'] . ' - ' . $d['rider_name']) : 'Unassigned') ?></td>
                        <td><span class="cpill <?= statusPillClass($d['status_name']) ?>"><?= htmlspecialchars($d['status_name']) ?></span></td>
                        <td class="n"><?= $d['rate_applied'] !== null ? '<span class="mny">P' . number_format((float) $d['rate_applied'], 2) . '</span>' : '<span class="muted">-</span>' ?></td>
                        <td><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                        <td>
                            <div class="btns">
                                <a href="index.php?page=deliveries&action=detail&id=<?= (int) $d['delivery_id'] ?>" class="btn alt sm">View</a>
                                <?php if (Session::isAdmin() && !in_array($d['status_name'], ['Verified', 'Failed', 'Cancelled'], true)): ?>
                                    <a href="index.php?page=deliveries&action=edit&id=<?= (int) $d['delivery_id'] ?>" class="btn alt sm">Edit</a>
                                <?php endif; ?>
                                <?php if (Session::isAdmin() && $d['status_name'] === 'Registered'): ?>
                                    <form method="post" action="index.php?page=deliveries&action=delete" style="display:inline;"
                                          data-confirm="Permanently delete delivery <?= htmlspecialchars($d['tracking_code']) ?>? This cannot be undone."
                                          data-confirm-title="Delete Delivery">
                                        <input type="hidden" name="delivery_id" value="<?= (int) $d['delivery_id'] ?>">
                                        <button type="submit" class="btn red sm">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
