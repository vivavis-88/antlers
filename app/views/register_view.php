<?php
$rate = (float) ($currentRate ?? 75.00);
$quota = (int) ($dailyQuota ?? 10);
$selectedVehicle = $old['vehicle_type'] ?? 'Motorcycle';

$vehicles = [
    'Motorcycle' => '<svg viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3"/><circle cx="18.5" cy="17.5" r="3"/><path d="M15 6h3l2 5h-5l-3-5H9l-2 5H4"/><path d="M8.5 17.5h6l2-6.5"/></svg>',
    'E-Bike / Scooter' => '<svg viewBox="0 0 24 24"><circle cx="6" cy="17.5" r="2.5"/><circle cx="18" cy="17.5" r="2.5"/><path d="M8.5 17.5h6.5l2-9.5h-2.5"/><path d="M13 8l-2 4h3l-1.5 3"/></svg>',
    'Bicycle' => '<svg viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3"/><circle cx="18.5" cy="17.5" r="3"/><path d="M12 17.5V11l-3-3h4l3 6.5"/><path d="M5.5 17.5 9 11h6"/></svg>',
    'Sedan / Car' => '<svg viewBox="0 0 24 24"><path d="M5 16H3v-4l2.5-5h13L21 12v4h-2"/><circle cx="7.5" cy="16.5" r="2"/><circle cx="16.5" cy="16.5" r="2"/><path d="M9.5 16.5h5"/></svg>',
    'Delivery Van' => '<svg viewBox="0 0 24 24"><path d="M2 7h12v9H2z"/><path d="M14 10h4.5L22 13.5V16h-8"/><circle cx="6.5" cy="17" r="2"/><circle cx="17.5" cy="17" r="2"/></svg>',
];
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
            <h1 class="auth-display" data-i18n-html="reg_hero">Join the fleet.<br><span>Earn per drop.</span></h1>
            <p class="auth-lead" data-i18n-text="Apply with your Gmail. Once approved, you can verify deliveries and track your daily quota.">
                Apply with your Gmail. Once approved, you can verify deliveries and track your daily quota.
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

            <ol class="onboard-steps">
                <li>
                    <span class="step-num">01</span>
                    <b data-i18n-text="Fill out your rider details">Fill out your rider details</b>
                </li>
                <li>
                    <span class="step-num">02</span>
                    <b data-i18n-text="Get approved via Gmail">Get approved via Gmail</b>
                </li>
                <li>
                    <span class="step-num">03</span>
                    <b data-i18n-text="Verify items and hit your quota">Verify items and hit your quota</b>
                </li>
            </ol>
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
            <a href="index.php?page=auth" class="btn alt sm">
                <span data-i18n-text="Sign In">Sign In</span>
            </a>
        </div>

        <div class="auth-form-wrap anim-in">
            <div class="auth-form-head">
                <h2 data-i18n-text="Apply as a Rider">Apply as a Rider</h2>
                <p data-i18n-text="Enter your details below to apply.">Enter your details below to apply.</p>
            </div>

            <?php if (!empty($errors['db'])): ?>
                <div class="alert-box error"><span><?= htmlspecialchars($errors['db']) ?></span></div>
            <?php endif; ?>

            <form method="post" action="index.php?page=auth&action=submitApplication" data-validate="true" data-action-loader="Submitting application..." novalidate>
                <div class="form-grid" style="margin-bottom:20px;">
                    <div class="form-group">
                        <label for="full_name" data-i18n-text="Full Name *">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                               placeholder="Juan Dela Cruz" data-label="Full Name" required>
                        <?php if (!empty($errors['full_name'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['full_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email" data-i18n-text="Gmail Address *">Gmail Address *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               placeholder="yourname@gmail.com" data-label="Email Address" required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="contact_no" data-i18n-text="Mobile Number *">Mobile Number *</label>
                        <input type="text" id="contact_no" name="contact_no" value="<?= htmlspecialchars($old['contact_no'] ?? '') ?>"
                               placeholder="0917 123 4567" data-label="Contact Number" required>
                        <?php if (!empty($errors['contact_no'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['contact_no']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="license_no" data-i18n-text="License / ID Number *">License / ID Number *</label>
                        <input type="text" id="license_no" name="license_no" value="<?= htmlspecialchars($old['license_no'] ?? '') ?>"
                               placeholder="N01-23-456789" data-label="License Number" required>
                        <?php if (!empty($errors['license_no'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['license_no']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label data-i18n-text="Vehicle Type *">Vehicle Type *</label>
                    <div class="vehicle-grid" role="radiogroup" aria-label="Vehicle Type">
                        <?php foreach ($vehicles as $vName => $vIcon): ?>
                            <?php $isChecked = ($selectedVehicle === $vName); ?>
                            <label class="vehicle-tile <?= $isChecked ? 'selected' : '' ?>">
                                <input type="radio" name="vehicle_type" value="<?= htmlspecialchars($vName) ?>" <?= $isChecked ? 'checked' : '' ?>>
                                <span class="vehicle-icon"><?= $vIcon ?></span>
                                <span class="vehicle-name"><?= htmlspecialchars($vName) ?></span>
                                <span class="vehicle-dot"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom:24px;">
                    <div class="form-group">
                        <label for="reg_password" data-i18n-text="Password *">Password *</label>
                        <div class="pw-wrap">
                            <input type="password" id="reg_password" name="password" placeholder="Min. 6 characters"
                                   minlength="6" data-label="Password" data-pw-strength required>
                            <button type="button" class="pw-toggle" data-toggle-pw="reg_password" aria-label="Show password">Show</button>
                        </div>
                        <div class="pw-meter" aria-hidden="true">
                            <div class="pw-meter-bar" id="pwStrengthBar"></div>
                        </div>
                        <span class="pw-hint" id="pwStrengthText" data-i18n-text="Use 6+ characters">Use 6+ characters</span>
                        <?php if (!empty($errors['password'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="reg_confirm" data-i18n-text="Confirm Password *">Confirm Password *</label>
                        <div class="pw-wrap">
                            <input type="password" id="reg_confirm" name="confirm_password" placeholder="Re-enter password"
                                   minlength="6" data-label="Confirm Password" required>
                            <button type="button" class="pw-toggle" data-toggle-pw="reg_confirm" aria-label="Show password">Show</button>
                        </div>
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <span class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-cta big">
                    <span data-i18n-text="Submit Application">Submit Application</span>
                    <svg class="i-sm btn-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </section>
</div>
