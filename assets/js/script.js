/**
 * DriveEasy – Vanilla JavaScript
 * Interactive UI, Date & Price Calculation, Dark Mode, Image Previews, Form Validation
 */

document.addEventListener('DOMContentLoaded', function () {
    // --------------------------------------------------------
    // 1. Dark Mode Toggle with localStorage
    // --------------------------------------------------------
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const htmlElement = document.documentElement;

    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = '<i class="bi bi-sun-fill text-warning"></i>';
                themeToggleBtn.setAttribute('title', 'Switch to Light Mode');
            }
        } else {
            htmlElement.setAttribute('data-bs-theme', 'light');
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = '<i class="bi bi-moon-stars-fill text-secondary"></i>';
                themeToggleBtn.setAttribute('title', 'Switch to Dark Mode');
            }
        }
    }

    const savedTheme = localStorage.getItem('driveeasy_theme') || 
        (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('driveeasy_theme', newTheme);
            applyTheme(newTheme);
        });
    }

    // --------------------------------------------------------
    // 2. Admin Mobile Sidebar Toggle
    // --------------------------------------------------------
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminOverlay = document.querySelector('.admin-overlay');

    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            adminSidebar.classList.toggle('show');
            if (adminOverlay) adminOverlay.classList.toggle('show');
        });
    }

    if (adminOverlay) {
        adminOverlay.addEventListener('click', function () {
            if (adminSidebar) adminSidebar.classList.remove('show');
            adminOverlay.classList.remove('show');
        });
    }

    // --------------------------------------------------------
    // 3. Password Show / Hide Toggle
    // --------------------------------------------------------
    const togglePasswordBtns = document.querySelectorAll('.toggle-password-btn');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye', !isPassword);
                    icon.classList.toggle('bi-eye-slash', isPassword);
                }
            }
        });
    });

    // --------------------------------------------------------
    // 4. Live Booking Date & Price Calculator
    // --------------------------------------------------------
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const pricePerDayEl = document.getElementById('price_per_day_val');
    const calcDaysEl = document.getElementById('calc_days_val');
    const calcTotalEl = document.getElementById('calc_total_val');
    const bookingSubmitBtn = document.getElementById('bookingSubmitBtn');
    const bookingDateAlert = document.getElementById('bookingDateAlert');

    if (pickupDateInput && returnDateInput && pricePerDayEl) {
        const pricePerDay = parseFloat(pricePerDayEl.getAttribute('data-price') || '0');

        function updateBookingCalculation() {
            const pickupVal = pickupDateInput.value;
            const returnVal = returnDateInput.value;

            if (!pickupVal || !returnVal) {
                if (calcDaysEl) calcDaysEl.innerText = '--';
                if (calcTotalEl) calcTotalEl.innerText = '₹0.00';
                return;
            }

            const pDate = new Date(pickupVal + 'T00:00:00');
            const rDate = new Date(returnVal + 'T00:00:00');
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Validation rules
            if (pDate < today) {
                showDateAlert('Pickup date cannot be in the past.');
                return;
            }

            if (rDate < pDate) {
                showDateAlert('Return date cannot be earlier than pickup date.');
                return;
            }

            hideDateAlert();

            const diffTime = rDate.getTime() - pDate.getTime();
            let days = Math.round(diffTime / (1000 * 60 * 60 * 24));
            if (days < 1) days = 1; // Minimum 1 day rental

            const total = days * pricePerDay;

            if (calcDaysEl) calcDaysEl.innerText = days + (days === 1 ? ' Day' : ' Days');
            if (calcTotalEl) calcTotalEl.innerText = '₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            // Also update hidden inputs if present
            const hiddenDays = document.getElementById('hidden_number_of_days');
            const hiddenTotal = document.getElementById('hidden_total_amount');
            if (hiddenDays) hiddenDays.value = days;
            if (hiddenTotal) hiddenTotal.value = total.toFixed(2);
        }

        function showDateAlert(msg) {
            if (bookingDateAlert) {
                bookingDateAlert.innerText = msg;
                bookingDateAlert.classList.remove('d-none');
            }
            if (bookingSubmitBtn) bookingSubmitBtn.disabled = true;
        }

        function hideDateAlert() {
            if (bookingDateAlert) {
                bookingDateAlert.classList.add('d-none');
            }
            if (bookingSubmitBtn) bookingSubmitBtn.disabled = false;
        }

        pickupDateInput.addEventListener('change', function () {
            // Adjust return date min attribute
            if (this.value) {
                returnDateInput.min = this.value;
                if (returnDateInput.value && returnDateInput.value < this.value) {
                    returnDateInput.value = this.value;
                }
            }
            updateBookingCalculation();
        });

        returnDateInput.addEventListener('change', updateBookingCalculation);

        // Run once on load in case inputs have values
        updateBookingCalculation();
    }

    // --------------------------------------------------------
    // 5. Image Preview for Admin Car Form
    // --------------------------------------------------------
    const carImageInput = document.getElementById('car_image_input');
    const carImagePreview = document.getElementById('car_image_preview');

    if (carImageInput && carImagePreview) {
        carImageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    carImagePreview.src = e.target.result;
                    carImagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // --------------------------------------------------------
    // 6. Delete & Cancel Confirmation Handlers
    // --------------------------------------------------------
    const confirmDeleteBtns = document.querySelectorAll('.confirm-delete');
    confirmDeleteBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm-msg') || 'Are you sure you want to permanently delete this item? This action cannot be undone.';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    const confirmCancelBtns = document.querySelectorAll('.confirm-cancel');
    confirmCancelBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm-msg') || 'Are you sure you want to cancel this booking?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // --------------------------------------------------------
    // 7. Form Double-Submission Prevention & Loading Spinner
    // --------------------------------------------------------
    const formsWithLoading = document.querySelectorAll('.prevent-double-submit');
    formsWithLoading.forEach(form => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                // Fallback timeout to re-enable in case form didn't navigate
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }, 8000);
            }
        });
    });

    // --------------------------------------------------------
    // 8. Auto-dismiss flash alerts after 6 seconds
    // --------------------------------------------------------
    const autoAlerts = document.querySelectorAll('.alert-dismissible');
    autoAlerts.forEach(alert => {
        setTimeout(() => {
            if (window.bootstrap && bootstrap.Alert) {
                const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 6000);
    });

    // --------------------------------------------------------
    // 9. Simulated Payment Method Selection Toggle
    // --------------------------------------------------------
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const upiDetails = document.getElementById('payment-details-upi');
    const cardDetails = document.getElementById('payment-details-card');

    if (paymentRadios.length > 0) {
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                const val = this.value;
                if (upiDetails) upiDetails.classList.toggle('d-none', val !== 'UPI');
                if (cardDetails) cardDetails.classList.toggle('d-none', val !== 'Credit Card' && val !== 'Debit Card');
            });
        });
    }
});
