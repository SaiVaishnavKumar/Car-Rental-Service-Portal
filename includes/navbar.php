<?php
/**
 * DriveEasy – Main Responsive Navigation Bar
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$current_script = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center me-4" href="<?= base_url('index.php') ?>">
            <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="42">
        </a>

        <!-- Mobile Controls (Theme Toggle + Hamburger) -->
        <div class="d-flex align-items-center gap-2 d-lg-none ms-auto me-2">
            <button class="btn btn-outline-secondary btn-sm rounded-circle p-2" id="mobileThemeToggleBtn" onclick="document.getElementById('themeToggleBtn').click();" aria-label="Toggle theme">
                <i class="bi bi-circle-half"></i>
            </button>
            <button class="navbar-toggler border-0 p-2 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarCollapse" aria-controls="mainNavbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-2 text-primary"></i>
            </button>
        </div>

        <!-- Collapsible Links -->
        <div class="collapse navbar-collapse" id="mainNavbarCollapse">
            <!-- Left Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_script === 'index.php') ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_script === 'cars.php' || $current_script === 'car-details.php') ? 'active' : '' ?>" href="<?= base_url('cars.php') ?>">
                        <i class="bi bi-car-front me-1"></i> Browse Cars
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_script === 'about.php') ? 'active' : '' ?>" href="<?= base_url('about.php') ?>">
                        <i class="bi bi-info-circle me-1"></i> About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_script === 'contact.php') ? 'active' : '' ?>" href="<?= base_url('contact.php') ?>">
                        <i class="bi bi-envelope me-1"></i> Contact
                    </a>
                </li>
                
                <?php if (is_logged_in() && !is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_script === 'my-bookings.php' || $current_script === 'booking-details.php') ? 'active' : '' ?>" href="<?= base_url('my-bookings.php') ?>">
                        <i class="bi bi-calendar2-check me-1"></i> My Bookings
                    </a>
                </li>
                <?php endif; ?>

                <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="<?= base_url('admin/dashboard.php') ?>">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Dashboard
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right Side Controls (Theme Toggle + Auth State) -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <!-- Theme Toggle Button (Desktop) -->
                <button type="button" class="btn btn-outline-secondary rounded-circle d-none d-lg-inline-flex p-2" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle Light/Dark Theme">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>

                <?php if (is_logged_in()): 
                    $user = current_user();
                ?>
                    <!-- User Dropdown Menu -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1.5" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </span>
                            <span class="d-inline-block text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 py-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($user['email']) ?></div>
                                <span class="badge bg-primary-subtle text-primary mt-1 text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($user['role']) ?></span>
                            </li>
                            <?php if (is_admin()): ?>
                            <li>
                                <a class="dropdown-item py-2" href="<?= base_url('admin/dashboard.php') ?>">
                                    <i class="bi bi-speedometer2 text-primary me-2"></i> Admin Panel
                                </a>
                            </li>
                            <?php else: ?>
                            <li>
                                <a class="dropdown-item py-2 <?= ($current_script === 'my-bookings.php') ? 'active' : '' ?>" href="<?= base_url('my-bookings.php') ?>">
                                    <i class="bi bi-journal-bookmark text-primary me-2"></i> My Bookings
                                </a>
                            </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item py-2 <?= ($current_script === 'profile.php') ? 'active' : '' ?>" href="<?= base_url('profile.php') ?>">
                                    <i class="bi bi-person-circle text-primary me-2"></i> Manage Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 <?= ($current_script === 'change-password.php') ? 'active' : '' ?>" href="<?= base_url('change-password.php') ?>">
                                    <i class="bi bi-key text-primary me-2"></i> Change Password
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?= base_url('logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Auth Buttons -->
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-primary px-3 rounded-pill">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-primary px-3.5 rounded-pill shadow-sm">
                        <i class="bi bi-person-plus me-1"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
