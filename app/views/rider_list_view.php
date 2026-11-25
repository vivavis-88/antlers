<div class="card slcard anim-in">
    <div class="sl-head">
        <div style="display:flex;align-items:center;gap:10px;">
            <h2>Rider Applications</h2>
            <?php if (!empty($pendingAppCount)): ?>
                <span class="cpill warn"><?= (int) $pendingAppCount ?> Pending</span>
            <?php endif; ?>
        </div>
        <form method="get" action="index.php" class="btns">
            <input type="hidden" name="page" value="riders">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <select name="app_status" class="f" style="height:32px;padding:0 10px;width:auto;">
                <option value="">All Applications</option>
                <option value="Pending" <?= ($appFilter === 'Pending') ? 'selected' : '' ?>>Pending Only</option>
                <option value="Approved" <?= ($appFilter === 'Approved') ? 'selected' : '' ?>>Approved Only</option>
                <option value="Rejected" <?= ($appFilter === 'Rejected') ? 'selected' : '' ?>>Rejected Only</option>
            </select>
            <button type="submit" class="btn alt sm">Filter</button>
        </form>
    </div>

    <div class="sl-body">
        <?php if (empty($applications)): ?>
            <p class="muted">No rider applications yet. New applicants can apply from the Sign In page.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="ptbl">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Gmail / Email</th>
                            <th>Contact &amp; Vehicle</th>
                            <th>License</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <?php
                            $pillClass = match ($app['status']) {
                                'Approved' => 'ok',
                                'Rejected' => 'bad',
                                default => 'warn',
                            };
                            ?>
                            <tr>
                                <td class="nm"><?= htmlspecialchars($app['full_name']) ?></td>
                                <td><?= htmlspecialchars($app['email']) ?></td>
                                <td>
                                    <?= htmlspecialchars($app['contact_no']) ?><br>
                                    <span class="muted" style="font-size:11.5px;"><?= htmlspecialchars($app['vehicle_type']) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($app['license_no']) ?></code></td>
                                <td>
                                    <span class="cpill <?= $pillClass ?>"><?= htmlspecialchars($app['status']) ?></span>
                                    <?php if ($app['status'] !== 'Pending'): ?>
                                        <div class="muted" style="font-size:11px;margin-top:4px;">
                                            <?= !empty($app['email_sent']) ? 'Gmail sent' : 'Logged locally' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                                <td>
                                    <?php if ($app['status'] === 'Pending'): ?>
                                        <div class="app-actions">
                                            <form method="post" action="index.php?page=riders&action=approveApplication" class="inline-action-form"
                                                  data-confirm="Approve <?= htmlspecialchars($app['full_name']) ?> as an active rider and send a Gmail notification to <?= htmlspecialchars($app['email']) ?>?"
                                                  data-confirm-title="Approve Rider Application">
                                                <input type="hidden" name="application_id" value="<?= (int) $app['application_id'] ?>">
                                                <input type="hidden" name="admin_note" value="Welcome to MEMOLOG!">
                                                <button type="submit" class="btn ok sm">Approve &amp; Notify</button>
                                            </form>
                                            <form method="post" action="index.php?page=riders&action=rejectApplication" class="inline-action-form"
                                                  data-validate="true"
                                                  data-confirm="Reject <?= htmlspecialchars($app['full_name']) ?>'s application and notify <?= htmlspecialchars($app['email']) ?>?"
                                                  data-confirm-title="Reject Application">
                                                <input type="hidden" name="application_id" value="<?= (int) $app['application_id'] ?>">
                                                <input type="text" name="admin_note" placeholder="Rejection reason..." class="inline-reason" data-label="Rejection reason" required>
                                                <button type="submit" class="btn red sm">Reject</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="muted" style="font-size:12px;">
                                            <?= htmlspecialchars($app['admin_note'] ?? 'Reviewed') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card slcard anim-in">
    <div class="sl-head">
        <h2>Active &amp; Registered Rider Accounts</h2>
        <a href="index.php?page=riders&action=create" class="btn sm">+ Add Rider</a>
    </div>

    <form method="get" action="index.php" class="sl-filters">
        <input type="hidden" name="page" value="riders">
        <label>
            Account Status
            <select name="status" class="f">
                <option value="">All Riders</option>
                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
            </select>
        </label>
        <button type="submit" class="btn sm">Filter</button>
        <a href="index.php?page=riders" class="btn alt sm">Reset</a>
    </form>

    <div class="sl-body">
        <table class="js-datatable" id="itemsTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact No</th>
                    <th>Deliveries</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riders as $r): ?>
                    <tr>
                        <td class="nm"><?= htmlspecialchars($r['rider_code']) ?></td>
                        <td><?= htmlspecialchars($r['full_name']) ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= htmlspecialchars($r['contact_no']) ?></td>
                        <td><?= (int) $r['delivery_count'] ?></td>
                        <td>
                            <span class="cpill <?= ((bool) $r['is_active']) ? 'ok' : 'bad' ?>">
                                <?= ((bool) $r['is_active']) ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btns">
                                <a href="index.php?page=riders&action=edit&id=<?= (int) $r['rider_id'] ?>" class="btn alt sm">Edit</a>
                                <form method="post" action="index.php?page=riders&action=delete" style="display:inline;"
                                      data-confirm="<?= ((int) $r['delivery_count'] > 0) ? 'This rider has deliveries and will be deactivated instead of deleted. Continue?' : 'Permanently delete rider ' . htmlspecialchars($r['full_name']) . '?' ?>"
                                      data-confirm-title="Remove Rider">
                                    <input type="hidden" name="rider_id" value="<?= (int) $r['rider_id'] ?>">
                                    <button type="submit" class="btn red sm"><?= ((int) $r['delivery_count'] > 0) ? 'Deactivate' : 'Delete' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
