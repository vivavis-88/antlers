<?php
$isEdit = !empty($customer);
$actionUrl = $isEdit ? 'index.php?page=customers&action=update' : 'index.php?page=customers&action=store';
$confirmMsg = $isEdit ? 'Save changes to this customer?' : 'Add this new customer?';
?>

<div class="card">
    <h2><?= $isEdit ? 'Edit Customer' : 'New Customer' ?></h2>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert-box error"><span><?= htmlspecialchars($errors['db']) ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?= $actionUrl ?>" data-validate="true" data-confirm="<?= $confirmMsg ?>" data-confirm-title="Confirm Save" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="customer_id" value="<?= (int) $customer['customer_id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                       placeholder="Recipient full name"
                       data-label="Full name" required>
                <?php if (!empty($errors['full_name'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['full_name']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="contact_no">Contact Number *</label>
                <input type="text" id="contact_no" name="contact_no"
                       value="<?= htmlspecialchars($old['contact_no'] ?? '') ?>"
                       placeholder="09175550101"
                       data-label="Contact number" required>
                <?php if (!empty($errors['contact_no'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['contact_no']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isEdit): ?>
                <div class="form-group full">
                    <label class="checkline">
                        <input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>>
                        Active Customer (selectable when registering new deliveries)
                    </label>
                </div>
            <?php endif; ?>
        </div>

        <div class="btns" style="margin-top:var(--s4);">
            <button type="submit" class="btn"><?= $isEdit ? 'Update Customer' : 'Add Customer' ?></button>
            <a href="index.php?page=customers" class="btn alt">Cancel</a>
        </div>
    </form>
</div>
