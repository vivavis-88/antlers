<?php if (empty($hideShell)): ?>
    </main>
</div>
<?php endif; ?>
</div><!-- /.viewport-stage -->

<!-- Bootstrap Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="confirmModalTitle">Confirm Action</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn alt" data-bs-dismiss="modal" data-i18n-text="Cancel">Cancel</button>
                <button type="button" class="btn" id="confirmModalOk" data-i18n-text="Confirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
