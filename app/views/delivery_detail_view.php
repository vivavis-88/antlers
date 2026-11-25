<?php
$status = $delivery['status_name'];
$isAdmin = Session::isAdmin();
$isRider = Session::isRider();
?>

<div class="grid">
    <div class="card s2 anim-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--s3);">
            <h2 style="margin:0;">Parcel Details</h2>
            <span class="cpill <?= ($status === 'Verified') ? 'ok' : (in_array($status, ['Failed', 'Cancelled', 'Verification Rejected'], true) ? 'bad' : 'info') ?>">
                <?= htmlspecialchars($status) ?>
            </span>
        </div>

        <div class="li"><span>Tracking Code</span><b><?= htmlspecialchars($delivery['tracking_code']) ?></b></div>
        <div class="li"><span>Customer</span><b><?= htmlspecialchars($delivery['customer_name']) ?> (<?= htmlspecialchars($delivery['customer_contact']) ?>)</b></div>
        <div class="li"><span>Assigned Rider</span><b><?= htmlspecialchars($delivery['rider_name'] ? ($delivery['rider_code'] . ' - ' . $delivery['rider_name']) : 'Unassigned') ?></b></div>
        <div class="li"><span>Parcel</span><b><?= htmlspecialchars($delivery['parcel_description']) ?></b></div>
        <div class="li"><span>Address</span><b><?= htmlspecialchars($delivery['delivery_address']) ?></b></div>
        <div class="li"><span>Proof / Verification Note</span><b><?= htmlspecialchars($delivery['proof_note'] ?? 'Not submitted yet') ?></b></div>
        <div class="li">
            <span>Verified Rate</span>
            <b class="mny"><?= $delivery['rate_applied'] !== null ? 'P' . number_format((float) $delivery['rate_applied'], 2) : '-' ?></b>
        </div>
        <?php if (!empty($delivery['verified_at'])): ?>
            <div class="li">
                <span>Verified By</span>
                <b><?= htmlspecialchars($delivery['verified_by_name'] ?? '') ?> on <?= date('M d, Y H:i', strtotime($delivery['verified_at'])) ?></b>
            </div>
        <?php endif; ?>

        <div class="btns" style="margin-top:var(--s4);">
            <a href="index.php?page=deliveries" class="btn alt sm">Back to List</a>
            <?php if ($isAdmin && !in_array($status, ['Verified', 'Failed', 'Cancelled'], true)): ?>
                <a href="index.php?page=deliveries&action=edit&id=<?= (int) $delivery['delivery_id'] ?>" class="btn alt sm">Edit Details / Rider</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card s2 anim-in">
        <h2>Status &amp; Item Verification</h2>

        <?php if (empty($allowedNext)): ?>
            <p class="muted">This delivery is in a final state (<?= htmlspecialchars($status) ?>). No further status changes are allowed.</p>
        <?php else: ?>
            <?php if ($isRider): ?>
                <?php if (in_array('Out for Delivery', $allowedNext, true)): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="margin-bottom:var(--s3);"
                          data-confirm="Mark this delivery as Out for Delivery?" data-confirm-title="Start Delivery">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Out for Delivery">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Dispatch Note (Optional)</label>
                            <input type="text" name="note" placeholder="Rider departed for delivery">
                        </div>
                        <button type="submit" class="btn">Mark Out for Delivery</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array('Verified', $allowedNext, true)): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="margin-bottom:var(--s3);"
                          data-validate="true"
                          data-confirm="Verify this item as delivered and credit P<?= number_format($currentRate, 2) ?> toward your earnings and daily quota?"
                          data-confirm-title="Verify Item & Credit Earnings">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Verified">

                        <div class="verify-checklist">
                            <div class="verify-checklist-title">Item Verification Checklist</div>
                            <label class="checkline">
                                <input type="checkbox" name="checks[]" value="Parcel seal & condition intact" required>
                                Parcel seal &amp; item condition verified intact
                            </label>
                            <label class="checkline">
                                <input type="checkbox" name="checks[]" value="Tracking code matched" required>
                                Tracking code (<?= htmlspecialchars($delivery['tracking_code']) ?>) matched
                            </label>
                            <label class="checkline">
                                <input type="checkbox" name="checks[]" value="Recipient confirmed" required>
                                Handed directly to recipient or authorized person
                            </label>
                        </div>

                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Proof Note / Recipient Confirmation Code *</label>
                            <input type="text" name="proof_note"
                                   value="<?= htmlspecialchars($delivery['proof_note'] ?? '') ?>"
                                   placeholder="e.g. Signed by Juan, Ref #4421" data-label="Proof note" required>
                        </div>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label>Additional Remarks (Optional)</label>
                            <input type="text" name="note" placeholder="Delivered and verified on site">
                        </div>
                        <button type="submit" class="btn ok">Verify Item &amp; Credit P<?= number_format($currentRate, 2) ?></button>
                    </form>
                <?php endif; ?>

                <?php if (in_array('Failed', $allowedNext, true)): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="padding-top:var(--s3);border-top:1px solid var(--line);"
                          data-validate="true" data-confirm="Mark this delivery as Failed?" data-confirm-title="Mark Failed">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Failed">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Failure Reason *</label>
                            <input type="text" name="note" placeholder="e.g. Recipient unavailable" data-label="Failure reason" required>
                        </div>
                        <button type="submit" class="btn red">Mark Failed</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <?php if (in_array('Verified', $allowedNext, true) && $status === 'Delivered (Pending Verification)'): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="margin-bottom:var(--s3);"
                          data-confirm="Approve proof and apply rate P<?= number_format($currentRate, 2) ?> to rider earnings?" data-confirm-title="Verify Delivery">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Verified">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Verification Note (Optional)</label>
                            <input type="text" name="note" placeholder="Proof approved">
                        </div>
                        <button type="submit" class="btn ok">Approve &amp; Verify (Rate: P<?= number_format($currentRate, 2) ?>)</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array('Verification Rejected', $allowedNext, true)): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="margin-bottom:var(--s3);padding-top:var(--s3);border-top:1px solid var(--line);"
                          data-validate="true" data-confirm="Reject proof and return delivery to Out for Delivery flow?" data-confirm-title="Reject Proof">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Verification Rejected">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Rejection Reason *</label>
                            <input type="text" name="note" placeholder="e.g. Missing recipient name or code" data-label="Rejection reason" required>
                        </div>
                        <button type="submit" class="btn red">Reject Proof</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array('Cancelled', $allowedNext, true)): ?>
                    <form method="post" action="index.php?page=deliveries&action=transition" style="padding-top:var(--s3);border-top:1px solid var(--line);"
                          data-validate="true" data-confirm="Cancel this delivery? History will be preserved." data-confirm-title="Cancel Delivery">
                        <input type="hidden" name="delivery_id" value="<?= (int) $delivery['delivery_id'] ?>">
                        <input type="hidden" name="target_status" value="Cancelled">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Cancellation Reason *</label>
                            <input type="text" name="note" placeholder="e.g. Cancelled by sender" data-label="Cancellation reason" required>
                        </div>
                        <button type="submit" class="btn red">Cancel Delivery</button>
                    </form>
                <?php endif; ?>

                <?php if ($status === 'Registered'): ?>
                    <p class="muted" style="margin-top:8px;">Assign a rider using "Edit Details / Rider" to move this delivery to Assigned.</p>
                <?php elseif ($status === 'Out for Delivery'): ?>
                    <p class="muted">Currently out for delivery with the assigned rider. The rider can verify the item upon handoff.</p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card anim-in" style="margin-top:var(--s4);">
    <h2 data-i18n-text="Delivery History">Delivery History</h2>
    <?php if (empty($events)): ?>
        <p class="muted" data-i18n-text="No updates yet.">No updates yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th data-i18n-text="Date & Time">Date &amp; Time</th>
                    <th data-i18n-text="Status">Status</th>
                    <th data-i18n-text="Updated By">Updated By</th>
                    <th data-i18n-text="Note">Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?= date('M d, Y H:i', strtotime($ev['created_at'])) ?></td>
                        <td><span class="cpill"><?= htmlspecialchars($ev['status_name']) ?></span></td>
                        <td><?= htmlspecialchars($ev['actor_name']) ?></td>
                        <td><?= htmlspecialchars($ev['note'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
