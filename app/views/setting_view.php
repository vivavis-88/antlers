<div class="card anim-in">
    <h2>Rate &amp; Daily Quota Configuration</h2>
    <p class="muted" style="margin-bottom:var(--s4);">
        When a delivery is verified, the current delivery rate is copied into <code>deliveries.rate_applied</code> so past earnings remain locked.
        The daily quota target powers the live progress tracker on every Rider's dashboard.
    </p>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert-box error"><span><?= htmlspecialchars($errors['db']) ?></span></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=settings&action=update" data-validate="true"
          data-confirm="Save updated delivery rate and daily rider quota?" data-confirm-title="Save Settings" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="delivery_rate">Delivery Rate (PHP) *</label>
                <input type="number" step="0.01" min="0" id="delivery_rate" name="delivery_rate"
                       value="<?= htmlspecialchars($rateInfo['setting_value'] ?? '0.00') ?>"
                       data-label="Delivery rate" required>
                <?php if (!empty($errors['delivery_rate'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['delivery_rate']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="daily_quota">Daily Rider Quota (Verified Deliveries / Day) *</label>
                <input type="number" step="1" min="1" id="daily_quota" name="daily_quota"
                       value="<?= (int) ($dailyQuota ?? 10) ?>"
                       data-label="Daily rider quota" required>
                <?php if (!empty($errors['daily_quota'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['daily_quota']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group full">
                <span class="muted">
                    Last updated by: <b><?= htmlspecialchars($rateInfo['updated_by_name'] ?? 'System') ?></b>
                    <?php if (!empty($rateInfo['updated_at'])): ?>
                        &middot; <?= date('M d, Y H:i', strtotime($rateInfo['updated_at'])) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="btns" style="margin-top:var(--s4);">
            <button type="submit" class="btn">Save Settings</button>
        </div>
    </form>
</div>

<div class="card anim-in">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--s3);">
        <h2 style="margin:0;">Gmail SMTP Notification Status</h2>
        <span class="cpill <?= !empty($mailConfigured) ? 'ok' : 'warn' ?>">
            <?= !empty($mailConfigured) ? 'Live Gmail SMTP Active' : 'Local Log Mode' ?>
        </span>
    </div>
    <p class="muted">
        <?php if (!empty($mailConfigured)): ?>
            Gmail SMTP credentials are configured in <code>config/mail.php</code>. Rider approval and rejection emails will be sent directly to each applicant's Gmail.
        <?php else: ?>
            To send live Gmail notifications when approving rider applications, enter your Gmail address and 16-character Google App Password in <code>config/mail.php</code>. Until then, outgoing notifications are safely logged to <code>storage/mail_log.txt</code>.
        <?php endif; ?>
    </p>
</div>
