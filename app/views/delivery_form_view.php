<?php
$isEdit = !empty($delivery);
$actionUrl = $isEdit ? 'index.php?page=deliveries&action=update' : 'index.php?page=deliveries&action=store';
$confirmMsg = $isEdit ? 'Save changes to this delivery?' : 'Register this new delivery?';
?>

<div class="card">
    <h2><?= $isEdit ? 'Edit Delivery Record' : 'New Delivery Record' ?></h2>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert-box error"><span><?= htmlspecialchars($errors['db']) ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?= $actionUrl ?>" data-validate="true" data-confirm="<?= $confirmMsg ?>" data-confirm-title="Confirm Save" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="tracking_code">Tracking Code *</label>
                <input type="text" id="tracking_code" name="tracking_code"
                       value="<?= htmlspecialchars($old['tracking_code'] ?? '') ?>"
                       placeholder="MEM-2026-0005"
                       pattern="[A-Za-z0-9\-]{5,40}"
                       data-pattern-msg="Use 5-40 letters, numbers, or hyphens."
                       data-label="Tracking code" required>
                <?php if (!empty($errors['tracking_code'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['tracking_code']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="customer_id">Customer (Recipient) *</label>
                <select id="customer_id" name="customer_id" class="f" data-label="Customer" required>
                    <option value="">Select customer...</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['customer_id'] ?>" <?= ((string) ($old['customer_id'] ?? '') === (string) $c['customer_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['full_name'] . ' (' . $c['contact_no'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['customer_id'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['customer_id']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="rider_id">Assign Rider (Optional)</label>
                <select id="rider_id" name="rider_id" class="f">
                    <option value="">Leave Unassigned (Registered)</option>
                    <?php foreach ($riders as $r): ?>
                        <option value="<?= (int) $r['rider_id'] ?>" <?= ((string) ($old['rider_id'] ?? '') === (string) $r['rider_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['rider_code'] . ' - ' . $r['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="parcel_description">Parcel Description *</label>
                <input type="text" id="parcel_description" name="parcel_description"
                       value="<?= htmlspecialchars($old['parcel_description'] ?? '') ?>"
                       placeholder="e.g. Box of documents, Fragile electronics"
                       data-label="Parcel description" required>
                <?php if (!empty($errors['parcel_description'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['parcel_description']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group full">
                <label for="delivery_address">Delivery Address *</label>
                <textarea id="delivery_address" name="delivery_address" rows="3"
                          placeholder="Full street address, barangay, city"
                          data-label="Delivery address" required><?= htmlspecialchars($old['delivery_address'] ?? '') ?></textarea>
                <?php if (!empty($errors['delivery_address'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['delivery_address']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="btns" style="margin-top:var(--s4);">
            <button type="submit" class="btn"><?= $isEdit ? 'Update Delivery' : 'Register Delivery' ?></button>
            <a href="index.php?page=deliveries" class="btn alt">Cancel</a>
        </div>
    </form>
</div>
