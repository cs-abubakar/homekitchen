            </div>
        </main>
    </div>
</div>

<!-- Footer -->
<footer class="bg-light text-center text-lg-start mt-5">
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
        &copy; <?php echo date('Y'); ?> <?php echo UNIVERSITY_NAME; ?>. All rights reserved.
        | <strong><?php echo APP_NAME; ?></strong> v<?php echo APP_VERSION; ?>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

<!-- Auto-hide toast messages -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toasts = document.querySelectorAll('.toast');
    toasts.forEach(function(toast) {
        setTimeout(function() {
            var bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, 5000); // Hide after 5 seconds
    });
});
</script>

</body>
</html>
