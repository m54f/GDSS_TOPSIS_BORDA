/**
 * GDSS Relawan BPBD - Custom JavaScript
 * Metode Borda - Group Decision Support System
 */

// Initialize when DOM is ready
$(document).ready(function () {
    // Initialize DataTables
    initDataTables();

    // Auto hide alerts
    autoHideAlerts();

    // Initialize tooltips
    initTooltips();
});

/**
 * Initialize DataTables with Indonesian language
 */
function initDataTables() {
    if ($.fn.DataTable) {
        $('.table-datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 10,
            ordering: true,
            searching: true,
            responsive: true
        });
    }
}

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Auto hide alerts after 3 seconds
 */
function autoHideAlerts() {
    setTimeout(function () {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 3000);
}

/**
 * Confirm delete with SweetAlert2
 */
function confirmDelete(url, title = 'Data ini') {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: title + ' akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

/**
 * Show success alert
 */
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

/**
 * Show error alert
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message
    });
}

/**
 * Show loading overlay
 */
function showLoading() {
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    Swal.close();
}

/**
 * Confirm action with custom message
 */
function confirmAction(message, callback) {
    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
}

/**
 * Format number with thousand separator
 */
function formatNumber(number, decimals = 0) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

/**
 * Highlight criteria item on select change (for penilaian)
 */
$(document).on('change', '.criteria-item select', function () {
    var item = $(this).closest('.criteria-item');
    if ($(this).val() !== '') {
        item.css('background-color', '#e8f5e9');
        item.css('border-color', '#a5d6a7');
    } else {
        item.css('background-color', '#f8f9fa');
        item.css('border-color', '#e9ecef');
    }
});
