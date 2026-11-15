<?php
/**
 * DriveEasy – Admin Authorization Verification
 */

require_once __DIR__ . '/auth.php';

/**
 * Enforce admin authorization
 * If not logged in -> redirect to login.php or admin/login.php
 * If logged in as customer -> abort with 403 Access Denied
 */
function require_admin() {
    if (!is_logged_in()) {
        set_flash('warning', 'Administrative credentials required. Please log in.');
        redirect('admin/login.php');
    }
    
    if (!is_admin()) {
        http_response_code(403);
        // Include clean 403 Access Denied page
        $page_title = "403 – Access Denied";
        if (file_exists(__DIR__ . '/../403.php')) {
            require_once __DIR__ . '/../403.php';
        } else {
            echo '<!DOCTYPE html><html><head><title>403 Access Denied</title></head><body><h1>403 – Access Denied</h1><p>You do not have permission to view administrative resources.</p><a href="' . base_url('index.php') . '">Return Home</a></body></html>';
        }
        exit;
    }
}
