<?php
$rate = (float) ($currentRate ?? 75.00);
$quota = (int) ($dailyQuota ?? 10);
?>
<div class="auth-split">
    <section class="auth-aside">
        <div class="auth-aside-top">
            <a href="index.php?page=auth" class="auth-brand">
                <span class="auth-mark">M</span>
                <span class="auth-wordmark">MEMOLOG</span>
            </a>
            <button type="button" class="auth-aside-close" data-toggle-aside title="Close panel" aria-label="Close panel">
                <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="auth-aside-body anim-in">
            <h1 class="auth-display" data-i18n-html="login_hero">Audit every parcel.<br><span>Verify every peso.</span></h1>
            <p class="auth-lead" data-i18n-text="Track deliveries, verify items, and monitor your daily quota in one clean workspace.">
                Track deliveries, verify items, and monitor your daily quota in one clean workspace.
            </p>

            <div class="metric-strip">
                <div class="metric-cell">
                    <span class="metric-k" data-i18n-text="RATE / DROP">RATE / DROP</span>
                    <strong class="metric-v">P<?= number_format($rate, 2) ?></strong>
                </div>
                <div class="metric-cell">
                    <span class="metric-k" data-i18n-text="DAILY QUOTA">DAILY QUOTA</span>
                    <strong class="metric-v"><?= $quota ?></strong>
                </div>
            </div>

            <div class="demo-panel">
                <div class="demo-panel-head" data-i18n-text="QUICK DEMO LOGIN">QUICK DEMO LOGIN</div>
                <div class="demo-chips">
                    <button type="button" class="demo-chip" data-demo-email="admin@memolog.local" data-demo-pass="Password123!">
                        <span class="demo-role">ADMIN</span>
                        <span>admin@memolog.local</span>
                    </button>
                    <button type="button" class="demo-chip" data-demo-email="marco@memolog.local" data-demo-pass="Password123!">
                        <span class="demo-role">RIDER</span>
                        <span>marco@memolog.local</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="auth-aside-foot">
            <span>MEMOLOG</span>
            <span>v2.0</span>
        </div>
    </section>

    <section class="auth-main">
        <div class="auth-topbar">
            <div class="auth-controls">
                <button type="button" class="btn alt sm auth-show-aside-btn" data-toggle-aside>
                    <svg class="i-xs" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>
                    <span data-i18n-text="Show Info">Show Info</span>
                </button>
                <div class="seg" aria-label="Language">
                    <button type="button" data-set-lang="en">EN</button>
                    <button type="button" data-set-lang="fil">FIL</button>
                </div>
                <div class="seg" aria-label="Device layout">
                    <button type="button" data-set-device="mobile" title="Mobile"><svg class="i-xs" viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/></svg><span>Mobile</span></button>
                    <button type="button" data-set-device="tablet" title="Tablet"><svg class="i-xs" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M12 17h.01"/></svg><span>Tablet</span></button>
                    <button type="button" data-set-device="pc" title="PC"><svg class="i-xs" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg><span>PC</span></button>
                </div>
                <div class="seg" aria-label="Theme">
                    <button type="button" data-set-theme="dark">Dark</button>
                    <button type="button" data-set-theme="light">Light</button>
                    <button type="button" data-set-theme="sand">Sand</button>
                </div>
            </div>
        </div>

        <div class="auth-form-wrap auth-form-narrow anim-in">
            <div class="auth-form-head">
                <h2 data-i18n-text="Sign In">Sign In</h2>
                <p data-i18n-text="Enter your email and password to continue.">Enter your email and password to continue.</p>
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert-box <?= htmlspecialchars($flash['type']) ?>">
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['auth'])): ?>
                <div class="alert-box error">
                    <span><?= htmlspecialchars($errors['auth']) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?page=auth&action=login" data-validate="true" data-action-loader="Signing in..." novalidate>
                <div class="form-group" style="margin-bottom:16px;">
                    <label for="login_email" data-i18n-text="Email Address">Email Address</label>
                    <input type="email" id="login_email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="admin@memolog.local" data-label="Email Address" required>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin-bottom:22px;">
                    <label for="login_password" data-i18n-text="Password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="login_password" name="password" placeholder="Enter password"
                               data-label="Password" required>
                        <button type="button" class="pw-toggle" data-toggle-pw="login_password" aria-label="Show password">Show</button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary-cta big">
                    <span data-i18n-text="Sign In">Sign In</span>
                    <svg class="i-sm btn-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div class="auth-cta-card">
                <div>
                    <b data-i18n-text="Want to become a rider?">Want to become a rider?</b>
                    <small data-i18n-text="Apply and get notified on Gmail.">Apply and get notified on Gmail.</small>
                </div>
                <a href="index.php?page=auth&action=register" class="btn alt sm">
                    <span data-i18n-text="Apply">Apply</span>
                    <svg class="i-xs" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>
</div>
