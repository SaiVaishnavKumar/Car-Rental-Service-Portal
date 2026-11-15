<?php
/**
 * DriveEasy – Admin Sidebar Component
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['SCRIPT_NAME']);
$admin = current_user();
?>
<!-- Mobile Overlay -->
<div class="admin-overlay"></div>

<!-- Admin Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-brand d-flex align-items-center justify-content-between">
        <a href="<?= base_url('admin/dashboard.php') ?>" class="d-flex align-items-center text-decoration-none">
            <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="38" class="bg-white p-1 rounded-2">
        </a>
    </div>

    <!-- Logged in Admin Profile Summary -->
    <div class="p-3 mx-2 my-2 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 d-flex align-items-center gap-2">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
            <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="overflow-hidden">
            <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></div>
            <div class="text-secondary" style="font-size: 0.72rem;"><i class="bi bi-shield-check text-success me-1"></i>Administrator</div>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="nav flex-column py-2 flex-grow-1">
        <div class="px-3 py-1 text-uppercase text-secondary fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Core Management</div>

        <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard.php') ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a class="nav-link <?= ($current_page === 'cars.php' || $current_page === 'edit-car.php') ? 'active' : '' ?>" href="<?= base_url('admin/cars.php') ?>">
            <i class="bi bi-car-front-fill"></i>
            <span>Fleet / Cars</span>
        </a>

        <a class="nav-link <?= ($current_page === 'add-car.php') ? 'active' : '' ?>" href="<?= base_url('admin/add-car.php') ?>">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add New Car</span>
        </a>

        <a class="nav-link <?= ($current_page === 'bookings.php') ? 'active' : '' ?>" href="<?= base_url('admin/bookings.php') ?>">
            <i class="bi bi-calendar2-range-fill"></i>
            <span>Bookings</span>
        </a>

        <a class="nav-link <?= ($current_page === 'users.php') ? 'active' : '' ?>" href="<?= base_url('admin/users.php') ?>">
            <i class="bi bi-people-fill"></i>
            <span>Customers</span>
        </a>

        <a class="nav-link <?= ($current_page === 'categories.php') ? 'active' : '' ?>" href="<?= base_url('admin/categories.php') ?>">
            <i class="bi bi-tags-fill"></i>
            <span>Categories</span>
        </a>

        <a class="nav-link <?= ($current_page === 'messages.php') ? 'active' : '' ?>" href="<?= base_url('admin/messages.php') ?>">
            <i class="bi bi-chat-left-text-fill"></i>
            <span>Messages</span>
        </a>

        <div class="px-3 pt-3 pb-1 text-uppercase text-secondary fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Links</div>

        <a class="nav-link" href="<?= base_url('index.php') ?>" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>View Public Site</span>
        </a>

        <a class="nav-link text-danger" href="<?= base_url('admin/logout.php') ?>">
            <i class="bi bi-power"></i>
            <span>Sign Out</span>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-top border-secondary border-opacity-25 text-center text-secondary small">
        DriveEasy v2.5 Admin
    </div>
</aside>
