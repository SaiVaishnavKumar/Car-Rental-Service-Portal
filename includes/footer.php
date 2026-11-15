<?php
/**
 * DriveEasy – Common HTML Footer Include
 */
require_once __DIR__ . '/functions.php';
?>
<footer class="footer-custom">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="40" class="bg-white p-1 rounded-2">
                </div>
                <p class="text-secondary mb-4 pe-lg-3">
                    DriveEasy is your premier self-drive and chauffeur car rental portal. Enjoy transparent pricing, clean & verified vehicles, and 24/7 dedicated customer assistance.
                </p>
                <div class="d-flex gap-3 fs-5">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="text-secondary hover-primary" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="text-secondary hover-primary" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="text-secondary hover-primary" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener" class="text-secondary hover-primary" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled mb-0 d-flex flex-direction-column flex-column gap-2">
                    <li><a href="<?= base_url('index.php') ?>" class="text-secondary text-decoration-none">Home</a></li>
                    <li><a href="<?= base_url('cars.php') ?>" class="text-secondary text-decoration-none">Explore Fleet</a></li>
                    <li><a href="<?= base_url('about.php') ?>" class="text-secondary text-decoration-none">About DriveEasy</a></li>
                    <li><a href="<?= base_url('contact.php') ?>" class="text-secondary text-decoration-none">Support Desk</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?= base_url('my-bookings.php') ?>" class="text-secondary text-decoration-none">My Bookings</a></li>
                    <?php else: ?>
                        <li><a href="<?= base_url('login.php') ?>" class="text-secondary text-decoration-none">Customer Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Vehicle Categories -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5>Vehicle Classes</h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= base_url('cars.php?category=1') ?>" class="text-secondary text-decoration-none"><i class="bi bi-chevron-right small me-1"></i> Rugged SUVs</a></li>
                    <li><a href="<?= base_url('cars.php?category=2') ?>" class="text-secondary text-decoration-none"><i class="bi bi-chevron-right small me-1"></i> Premium Sedans</a></li>
                    <li><a href="<?= base_url('cars.php?category=3') ?>" class="text-secondary text-decoration-none"><i class="bi bi-chevron-right small me-1"></i> City Hatchbacks</a></li>
                    <li><a href="<?= base_url('cars.php?category=4') ?>" class="text-secondary text-decoration-none"><i class="bi bi-chevron-right small me-1"></i> Executive Luxury</a></li>
                    <li><a href="<?= base_url('cars.php?category=5') ?>" class="text-secondary text-decoration-none"><i class="bi bi-chevron-right small me-1"></i> Green Electric Vehicles</a></li>
                </ul>
            </div>

            <!-- Contact & Helpdesk -->
            <div class="col-lg-3 col-md-6">
                <h5>Headquarters</h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-secondary">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
                        <span>Tech Park Blvd, Airport Road, Sector 18, Gurugram / New Delhi</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span>+91 98765 43210</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-primary"></i>
                        <span>support@driveeasy.com</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-fill text-primary"></i>
                        <span>24/7 Booking & Roadside Support</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>
                &copy; <?= date('Y') ?> <strong>DriveEasy</strong>. All rights reserved. Designed for College Viva & Project Showcase.
            </div>
            <div class="d-flex gap-3">
                <a href="<?= base_url('admin/login.php') ?>" class="text-secondary text-decoration-none small">
                    <i class="bi bi-shield-lock me-1"></i> Admin Portal
                </a>
                <span class="text-secondary small">&bull;</span>
                <span class="text-secondary small">XAMPP Apache + MySQL Compatible</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom App Scripts -->
<script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
