<?php
$isEdit = !empty($rider);
$actionUrl = $isEdit ? 'index.php?page=riders&action=update' : 'index.php?page=riders&action=store';
$confirmMsg = $isEdit ? 'Save changes to this rider?' : 'Create this new rider account?';
?>

<div class="card">
    <h2><?= $isEdit ? 'Edit Rider Account' : 'New Rider Account' ?></h2>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert-box error"><span><?= htmlspecialchars($errors['db']) ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?= $actionUrl ?>" data-validate="true" data-confirm="<?= $confirmMsg ?>" data-confirm-title="Confirm Save" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="rider_id" value="<?= (int) $rider['rider_id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="rider_code">Rider Code *</label>
                <input type="text" id="rider_code" name="rider_code"
                       value="<?= htmlspecialchars($old['rider_code'] ?? '') ?>"
                       placeholder="RDR-003"
                       pattern="[A-Za-z0-9\-]{3,30}"
                       data-pattern-msg="Use 3-30 letters, numbers, or hyphens."
                       data-label="Rider code" required>
                <?php if (!empty($errors['rider_code'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['rider_code']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                       placeholder="Juan Rider"
                       data-label="Full name" required>
                <?php if (!empty($errors['full_name'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['full_name']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Login Email *</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       placeholder="rider@memolog.local"
                       data-label="Login email" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="contact_no">Contact Number *</label>
                <input type="text" id="contact_no" name="contact_no"
                       value="<?= htmlspecialchars($old['contact_no'] ?? '') ?>"
                       placeholder="09171234567"
                       data-label="Contact number" required>
                <?php if (!empty($errors['contact_no'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['contact_no']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password"><?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
                <input type="password" id="password" name="password"
                       placeholder="Minimum 6 characters"
                       minlength="6"
                       data-label="Password" <?= $isEdit ? '' : 'required' ?>>
                <?php if (!empty($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isEdit): ?>
                <div class="form-group" style="justify-content:center;">
                    <label class="checkline">
                        <input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>>
                        Active Rider Account (can log in and receive assignments)
                    </label>
                </div>
            <?php endif; ?>
        </div>

        <div class="btns" style="margin-top:var(--s4);">
            <button type="submit" class="btn"><?= $isEdit ? 'Update Rider' : 'Create Rider' ?></button>
            <a href="index.php?page=riders" class="btn alt">Cancel</a>
        </div>
    </form>
</div>
