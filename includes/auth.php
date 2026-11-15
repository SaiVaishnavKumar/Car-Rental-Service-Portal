<?php
/**
 * DriveEasy – User Authentication & Authorization Helpers
 */

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current logged in user details array
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Enforce authentication: requires user to be logged in
 */
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        $target = $redirect_to ?: $_SERVER['REQUEST_URI'];
        set_flash('warning', 'Please sign in to your DriveEasy account to continue.');
        redirect('login.php?redirect=' . urlencode($target));
    }
}

/**
 * Enforce guest access: if already logged in, redirect to home or admin dashboard
 */
function require_guest() {
    if (is_logged_in()) {
        if (is_admin()) {
            redirect('admin/dashboard.php');
        } else {
            redirect('index.php');
        }
    }
}

/**
 * Set session credentials on successful login
 */
function login_user($user) {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'] ?? '';
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_at'] = time();
}

/**
 * Completely destroy session on logout
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
