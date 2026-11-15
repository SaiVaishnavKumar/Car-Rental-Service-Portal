<?php
/**
 * DriveEasy – Admin Logout
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_user();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_flash('info', 'Admin session terminated safely.');
redirect('admin/login.php');
