<?php
/**
 * DriveEasy – Logout Handler
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();

// Start a fresh session to hold the flash message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_flash('info', 'You have been successfully logged out of DriveEasy. See you again!');
redirect('login.php');
