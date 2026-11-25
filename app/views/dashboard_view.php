<?php
$mode = $dashboardData['mode'] ?? 'Rider';
$stats = $dashboardData['stats'] ?? [];
?>

<?php if ($mode === 'Administrator'): ?>
    <div class="grid" style="margin-bottom:var(--s4);">
        <div class="stat anim-in">
            <span class="k">Total Deliveries</span>
            <div class="v" data-count-to="<?= (int) ($stats['total_deliveries'] ?? 0) ?>"><?= (int) ($stats['total_deliveries'] ?? 0) ?></div>
            <span class="muted">All recorded parcels</span>
        </div>
        <div class="stat anim-in">
            <span class="k">Pending Rider Applications</span>
            <div class="v" style="color:var(--qty);" data-count-to="<?= (int) ($dashboardData['pending_applications_count'] ?? 0) ?>"><?= (int) ($dashboardData['pending_applications_count'] ?? 0) ?></div>
            <span class="muted"><a href="index.php?page=riders" class="text-link">Review &amp; notify Gmail &rarr;</a></span>
        </div>
        <div class="stat anim-in">
            <span class="k">Active Riders</span>
            <div class="v" data-count-to="<?= (int) ($dashboardData['active_riders'] ?? 0) ?>"><?= (int) ($dashboardData['active_riders'] ?? 0) ?></div>
            <span class="muted">Rate: P<?= number_format((float) ($dashboardData['current_rate'] ?? 0), 2) ?> &middot; Quota: <?= (int) ($dashboardData['daily_quota'] ?? 10) ?>/day</span>
        </div>
        <div class="stat inv anim-in">
            <span class="k">Verified Payouts</span>
            <div class="v mny">P<?= number_format((float) ($stats['total_payout'] ?? 0), 2) ?></div>
            <span class="muted"><?= (int) ($stats['verified_count'] ?? 0) ?> verified deliveries</span>
        </div>
    </div>

    <?php if (!empty($dashboardData['pending_applications'])): ?>
        <div class="card anim-in" style="margin-bottom:var(--s4);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--s3);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <h2 style="margin:0;">New Rider Applications</h2>
                    <span class="cpill warn"><?= (int) ($dashboardData['pending_applications_count'] ?? 0) ?> Awaiting Review</span>
                </div>
                <a href="index.php?page=riders" class="btn sm">Manage Applications</a>
            </div>
            <?php foreach ($dashboardData['pending_applications'] as $app): ?>
                <div class="li">
                    <div>
                        <b><?= htmlspecialchars($app['full_name']) ?></b>
                        <small><?= htmlspecialchars($app['email']) ?> &middot; <?= htmlspecialchars($app['vehicle_type']) ?> &middot; License: <?= htmlspecialchars($app['license_no']) ?></small>
                    </div>
                    <a href="index.php?page=riders" class="btn ok sm">Review</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div class="card s2 anim-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--s3);">
                <h2 style="margin:0;">Pending Proof Verification</h2>
                <a href="index.php?page=deliveries" class="btn alt sm">View All</a>
            </div>
            <?php if (empty($dashboardData['pending_verification'])): ?>
                <p class="muted">No deliveries awaiting admin review.</p>
            <?php else: ?>
                <?php foreach ($dashboardData['pending_verification'] as $item): ?>
                    <div class="li">
                        <div>
                            <b><?= htmlspecialchars($item['tracking_code']) ?></b>
                            <small><?= htmlspecialchars($item['rider_name'] ?? 'Unassigned') ?> &middot; Proof: <?= htmlspecialchars($item['proof_note'] ?? '') ?></small>
                        </div>
                        <a href="index.php?page=deliveries&action=detail&id=<?= (int) $item['delivery_id'] ?>" class="btn sm">Review</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card s2 anim-in">
            <h2>Recent Audit Trail</h2>
            <?php if (empty($dashboardData['recent_events'])): ?>
                <p class="muted">No audit events recorded yet.</p>
            <?php else: ?>
                <?php foreach ($dashboardData['recent_events'] as $ev): ?>
                    <div class="li">
                        <div>
                            <b><?= htmlspecialchars($ev['tracking_code']) ?> &rarr; <?= htmlspecialchars($ev['status_name']) ?></b>
                            <small>By <?= htmlspecialchars($ev['actor_name']) ?> &middot; <?= htmlspecialchars($ev['note'] ?? '') ?></small>
                        </div>
                        <span class="muted" style="font-size:11.5px;"><?= date('M d, H:i', strtotime($ev['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <?php
    $dailyQuota = max(1, (int) ($dashboardData['daily_quota'] ?? 10));
    $todayVerified = (int) ($stats['today_verified_count'] ?? 0);
    $todayEarnings = (float) ($stats['today_earnings'] ?? 0);
    $quotaPct = min(100, (int) round(($todayVerified / $dailyQuota) * 100));
    $quotaRemaining = max(0, $dailyQuota - $todayVerified);
    $quotaReached = $todayVerified >= $dailyQuota;
    ?>

    <div class="card quota-card anim-in" style="margin-bottom:var(--s4);">
        <div class="quota-head">
            <div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="margin:0;">Today's Delivery Quota</h2>
                    <span class="cpill <?= $quotaReached ? 'ok' : 'info' ?>">
                        <?= $quotaReached ? 'Quota Reached!' : "{$quotaRemaining} left to reach target" ?>
                    </span>
                </div>
                <p class="muted" style="margin-top:4px;">
                    <?= date('l, F j, Y') ?> &middot; Rate: P<?= number_format((float) ($dashboardData['current_rate'] ?? 0), 2) ?> / verified delivery
                </p>
            </div>
            <div class="quota-numbers">
                <span class="quota-big"><?= $todayVerified ?> <small>/ <?= $dailyQuota ?></small></span>
                <span class="mny" style="font-size:13px;">Today: P<?= number_format($todayEarnings, 2) ?></span>
            </div>
        </div>
        <div class="quota-track" role="progressbar" aria-valuenow="<?= $quotaPct ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="quota-fill <?= $quotaReached ? 'done' : '' ?>" data-progress="<?= $quotaPct ?>" style="width:0%;"></div>
        </div>
        <div class="quota-foot">
            <span><?= $quotaPct ?>% of daily target completed</span>
            <a href="index.php?page=deliveries" class="text-link">Verify assigned items &rarr;</a>
        </div>
    </div>

    <div class="grid" style="margin-bottom:var(--s4);">
        <div class="stat anim-in">
            <span class="k">Active Tasks</span>
            <div class="v" style="color:var(--qty);" data-count-to="<?= (int) ($stats['active_count'] ?? 0) ?>"><?= (int) ($stats['active_count'] ?? 0) ?></div>
            <span class="muted">Assigned or in transit</span>
        </div>
        <div class="stat anim-in">
            <span class="k">Verified Today</span>
            <div class="v" data-count-to="<?= $todayVerified ?>"><?= $todayVerified ?></div>
            <span class="muted">Toward <?= $dailyQuota ?>-item daily quota</span>
        </div>
        <div class="stat anim-in">
            <span class="k">Total Verified</span>
            <div class="v" data-count-to="<?= (int) ($stats['verified_count'] ?? 0) ?>"><?= (int) ($stats['verified_count'] ?? 0) ?></div>
            <span class="muted">All-time verified items</span>
        </div>
        <div class="stat inv anim-in">
            <span class="k">Total Verified Earnings</span>
            <div class="v mny">P<?= number_format((float) ($stats['total_earnings'] ?? 0), 2) ?></div>
            <span class="muted">Credited automatically on verification</span>
        </div>
    </div>

    <div class="grid">
        <div class="card s2 anim-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--s3);">
                <h2 style="margin:0;">My Active Deliveries</h2>
                <a href="index.php?page=deliveries" class="btn alt sm">Open List</a>
            </div>
            <?php if (empty($dashboardData['assigned_deliveries'])): ?>
                <p class="muted">No active deliveries assigned to you right now.</p>
            <?php else: ?>
                <?php foreach ($dashboardData['assigned_deliveries'] as $d): ?>
                    <div class="li">
                        <div>
                            <b><?= htmlspecialchars($d['tracking_code']) ?> &middot; <?= htmlspecialchars($d['customer_name']) ?></b>
                            <small><?= htmlspecialchars($d['delivery_address']) ?> &middot; <span class="cpill info" style="height:20px;font-size:10.5px;"><?= htmlspecialchars($d['status_name']) ?></span></small>
                        </div>
                        <a href="index.php?page=deliveries&action=detail&id=<?= (int) $d['delivery_id'] ?>" class="btn sm">Verify / Update</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card s2 anim-in">
            <h2>Recent Verified Earnings</h2>
            <?php if (empty($dashboardData['recent_verified'])): ?>
                <p class="muted">No verified deliveries yet.</p>
            <?php else: ?>
                <?php foreach ($dashboardData['recent_verified'] as $v): ?>
                    <div class="li">
                        <div>
                            <b><?= htmlspecialchars($v['tracking_code']) ?></b>
                            <small><?= htmlspecialchars($v['customer_name']) ?> &middot; <?= date('M d, Y', strtotime($v['verified_at'])) ?></small>
                        </div>
                        <span class="mny">P<?= number_format((float) $v['rate_applied'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
