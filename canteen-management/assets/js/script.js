/**
 * Custom JavaScript
 * Canteen Management System - Yangtze University
 */

// DOM Ready
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Confirm delete with better UX
    $('.delete-confirm').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });

    // Auto-hide alerts after 5 seconds
    $('.alert:not(.alert-permanent)').delay(5000).slideUp(300);

    // Smooth scroll to top
    $('.scroll-to-top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 'smooth');
    });

    // Print functionality
    $('.print-btn').on('click', function() {
        window.print();
    });

    // Currency formatting
    $('.currency-input').on('blur', function() {
        var value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });

    // Phone number formatting (simple)
    $('.phone-input').on('input', function() {
        var value = $(this).val().replace(/[^\d+\s()-]/g, '');
        $(this).val(value);
    });

    // Sidebar collapse toggle for mobile
    $('.sidebar-toggle').on('click', function() {
        $('#sidebar').toggleClass('show');
    });

    // DataTables default configuration
    if ($.fn.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }

    // Number input validation
    $('input[type="number"]').on('keypress', function(e) {
        // Allow: backspace, delete, tab, escape, enter, decimal point
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    // Confirmation modals
    $('[data-confirm]').on('click', function(e) {
        var message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });

    // Loading overlay
    function showLoading() {
        if ($('.spinner-overlay').length === 0) {
            $('body').append('<div class="spinner-overlay"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        }
    }

    function hideLoading() {
        $('.spinner-overlay').remove();
    }

    // Show loading on form submit
    $('form:not(.no-loading)').on('submit', function() {
        showLoading();
    });

    // Hide loading on page load
    hideLoading();

    // Export to Excel function (generic)
    window.exportTableToExcel = function(tableId, filename) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename || 'export.xls';
        link.click();
    };

    // Print specific element
    window.printElement = function(elementId) {
        var element = document.getElementById(elementId);
        if (!element) return;

        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(element.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    };

    // Format currency display
    window.formatCurrency = function(amount) {
        return '¥ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    // Date formatting
    window.formatDate = function(dateString) {
        var date = new Date(dateString);
        var options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    };

    // Calculate days between dates
    window.daysBetween = function(date1, date2) {
        var d1 = new Date(date1);
        var d2 = new Date(date2);
        var timeDiff = Math.abs(d2.getTime() - d1.getTime());
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    };

    // Debounce function for search inputs
    window.debounce = function(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    // Live search implementation
    $('.live-search').on('keyup', debounce(function() {
        var query = $(this).val().toLowerCase();
        var target = $(this).data('target');

        $(target + ' tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(query) > -1);
        });
    }, 300));

    // Prevent double submission
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
        setTimeout(function() {
            $('form button[type="submit"]').prop('disabled', false);
        }, 3000);
    });

    // Auto-focus first input in modals
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('input:text:visible:first').focus();
    });

    // Numeric input formatting
    $('.number-format').on('blur', function() {
        var value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toLocaleString());
        }
    });

    // Character counter for textareas
    $('textarea[maxlength]').each(function() {
        var maxLength = $(this).attr('maxlength');
        $(this).after('<div class="form-text text-end"><span class="char-count">0</span>/' + maxLength + ' characters</div>');
    });

    $('textarea[maxlength]').on('input', function() {
        var length = $(this).val().length;
        $(this).next('.form-text').find('.char-count').text(length);
    });

    // Copy to clipboard functionality
    window.copyToClipboard = function(text) {
        var temp = $('<textarea>');
        $('body').append(temp);
        temp.val(text).select();
        document.execCommand('copy');
        temp.remove();

        // Show feedback
        alert('Copied to clipboard!');
    };

    // Initialize all Select2 dropdowns
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
});

// Console welcome message
console.log('%cYangtze University Canteen Management System', 'color: #0d6efd; font-size: 20px; font-weight: bold;');
console.log('%cDeveloped for managing student meal packages', 'color: #6c757d; font-size: 14px;');

// Prevent console tampering in production
if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    console.log = function() {};
    console.warn = function() {};
    console.error = function() {};
}
