<?php if (Session::isRider() && !empty($riderStats)): ?>
    <?php
    $quota = max(1, (int) ($dailyQuota ?? 10));
    $todayDone = (int) ($riderStats['today_verified_count'] ?? 0);
    $todayMoney = (float) ($riderStats['today_earnings'] ?? 0);
    $pct = min(100, (int) round(($todayDone / $quota) * 100));
    $reached = $todayDone >= $quota;
    ?>
    <div class="card quota-card anim-in" style="margin-bottom:var(--s4);">
        <div class="quota-head">
            <div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="margin:0;">Daily Quota Tracker</h2>
                    <span class="cpill <?= $reached ? 'ok' : 'info' ?>">
                        <?= $reached ? 'Daily Target Hit!' : max(0, $quota - $todayDone) . ' more to target' ?>
                    </span>
                </div>
                <p class="muted" style="margin-top:4px;"><?= date('l, F j, Y') ?></p>
            </div>
            <div class="quota-numbers">
                <span class="quota-big"><?= $todayDone ?> <small>/ <?= $quota ?></small></span>
                <span class="mny" style="font-size:13px;">Today: P<?= number_format($todayMoney, 2) ?></span>
            </div>
        </div>
        <div class="quota-track" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="quota-fill <?= $reached ? 'done' : '' ?>" data-progress="<?= $pct ?>" style="width:0%;"></div>
        </div>
    </div>
<?php endif; ?>

<div class="card anim-in">
    <form method="get" action="index.php" class="row">
        <input type="hidden" name="page" value="reports">
        <label>
            Date From
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        </label>
        <label>
            Date To
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
        </label>
        <button type="button" class="btn alt sm" data-fill-today title="Fill today's date">
            <svg class="i-xs" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span data-i18n-text="Fill Date Today">Fill Date Today</span>
        </button>
        <button type="submit" class="btn sm" data-i18n-text="Apply">Apply</button>
        <a href="index.php?page=reports" class="btn alt sm" data-i18n-text="Clear">Clear</a>
    </form>
</div>

<?php if (Session::isAdmin() && !empty($statusSummary)): ?>
    <div class="card slcard anim-in">
        <div class="sl-head">
            <h2>Delivery Summary by Status</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="ptbl">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th class="n">Total Deliveries</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $grandCount = 0; foreach ($statusSummary as $row): $grandCount += (int) $row['total_count']; ?>
                        <tr>
                            <td class="nm"><?= htmlspecialchars($row['status_name']) ?></td>
                            <td class="n"><?= (int) $row['total_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="n"><?= $grandCount ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="card slcard anim-in">
    <div class="sl-head">
        <h2><?= Session::isAdmin() ? 'Rider Earnings Summary (Verified Deliveries Only)' : 'My Verified Earnings Summary' ?></h2>
    </div>
    <div style="overflow-x:auto;">
        <table class="ptbl">
            <thead>
                <tr>
                    <th>Rider Code</th>
                    <th>Rider Name</th>
                    <th>Account</th>
                    <th class="n">Verified Count</th>
                    <th class="n">Total Earnings</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalVerified = 0;
                $totalPayout = 0.0;
                foreach ($earningsReport as $er):
                    $totalVerified += (int) $er['verified_count'];
                    $totalPayout += (float) $er['total_earnings'];
                ?>
                    <tr>
                        <td class="nm"><?= htmlspecialchars($er['rider_code']) ?></td>
                        <td><?= htmlspecialchars($er['rider_name']) ?></td>
                        <td>
                            <span class="cpill <?= ((bool) $er['is_active']) ? 'ok' : 'bad' ?>">
                                <?= ((bool) $er['is_active']) ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="n"><?= (int) $er['verified_count'] ?></td>
                        <td class="n mny">P<?= number_format((float) $er['total_earnings'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Grand Total</td>
                    <td class="n"><?= $totalVerified ?></td>
                    <td class="n mny">P<?= number_format($totalPayout, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
