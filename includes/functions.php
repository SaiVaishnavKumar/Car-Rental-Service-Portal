<?php
/**
 * DriveEasy – Global Helper Functions
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

/**
 * Dynamically determine the base URL for the project
 * Works on any server, subfolder, XAMPP htdocs, or PHP built-in server.
 */
function base_url($path = '') {
    if (defined('CUSTOM_BASE_URL')) {
        return rtrim(CUSTOM_BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    if (empty($scriptName) || $scriptName === 'Command line code' || php_sapi_name() === 'cli') {
        return '/' . ltrim($path, '/');
    }

    $scriptDir = dirname($scriptName);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = trim($scriptDir, '/.');
    
    $parts = !empty($scriptDir) ? explode('/', $scriptDir) : [];
    
    // If running inside admin subfolder, strip 'admin' from base path
    if (!empty($parts) && end($parts) === 'admin') {
        array_pop($parts);
    }
    
    $base = empty($parts) ? '' : '/' . implode('/', $parts);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Sanitize string input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Safe redirect helper
 */
function redirect($url, $status = 302) {
    // If relative path without slash/protocol, resolve with base_url
    if (!preg_match('#^https?://#i', $url) && !str_starts_with($url, '/')) {
        $url = base_url($url);
    }
    header("Location: " . $url, true, $status);
    exit;
}

/**
 * Set flash alert message
 * Types: success, danger, warning, info
 */
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as Bootstrap 5 alert with dismiss button
 */
function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $msg = $flash['message']; // Can contain safe HTML or text
        $icon = match($type) {
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            default => 'bi-info-circle-fill'
        };
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show shadow-sm d-flex align-items-center mb-4" role="alert">
                <i class="bi ' . $icon . ' me-2 fs-5"></i>
                <div>' . $msg . '</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Format currency with Indian Rupee symbol
 */
function format_currency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

/**
 * Format date in friendly human-readable format
 */
function format_date($date, $format = 'd M Y') {
    if (empty($date) || $date === '0000-00-00') return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Calculate difference in days between two dates (minimum 1 day)
 */
function calculate_days($pickup_date, $return_date) {
    $d1 = new DateTime($pickup_date);
    $d2 = new DateTime($return_date);
    $diff = $d1->diff($d2);
    $days = (int)$diff->format('%r%a');
    return max(1, $days);
}

/**
 * Resolve car image URL with safe fallback
 */
function get_car_image_url($image_filename) {
    if (empty($image_filename)) {
        return asset_url('images/cars/default-car.svg');
    }

    // The Hector image asset is PNG data; serve it with the matching extension.
    if ($image_filename === 'hector.jpg') {
        $image_filename = 'hector.png';
    }
    
    // If it's a full URL
    if (preg_match('#^https?://#i', $image_filename)) {
        return $image_filename;
    }
    
    $local_path = __DIR__ . '/../assets/images/cars/' . $image_filename;
    if (file_exists($local_path)) {
        return asset_url('images/cars/' . $image_filename);
    }
    
    // Try svg extension if jpg was given or vice versa
    $alt = preg_replace('/\.(jpg|png|jpeg)$/i', '.svg', $image_filename);
    if (file_exists(__DIR__ . '/../assets/images/cars/' . $alt)) {
        return asset_url('images/cars/' . $alt);
    }
    
    return asset_url('images/cars/default-car.svg');
}

/**
 * Check if a car is available for a given date range
 * Overlap logic:
 * A collision happens if: existing_pickup <= new_return AND existing_return >= new_pickup
 */
function is_car_available_for_dates($pdo, $car_id, $pickup_date, $return_date, $exclude_booking_id = null) {
    // 1. Check general car availability status
    $stmt = $pdo->prepare("SELECT availability FROM cars WHERE id = ?");
    $stmt->execute([(int)$car_id]);
    $car = $stmt->fetch();
    
    if (!$car || strtolower($car['availability']) !== 'available') {
        return false;
    }
    
    // 2. Check for date collision with active/confirmed/pending bookings
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE car_id = :car_id 
              AND booking_status NOT IN ('Cancelled', 'Rejected')
              AND (pickup_date <= :return_date AND return_date >= :pickup_date)";
              
    $params = [
        ':car_id' => $car_id,
        ':pickup_date' => $pickup_date,
        ':return_date' => $return_date
    ];
    
    if ($exclude_booking_id) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $exclude_booking_id;
    }
    
    $checkStmt = $pdo->prepare($sql);
    $checkStmt->execute($params);
    $overlapCount = $checkStmt->fetchColumn();
    
    return ($overlapCount == 0);
}

/**
 * Return styled badge for Booking Status
 */
function get_booking_status_badge($status) {
    $class = match(strtolower($status)) {
        'confirmed' => 'badge-soft-success',
        'pending'   => 'badge-soft-warning',
        'active'    => 'badge-soft-info',
        'completed' => 'badge-soft-primary',
        'cancelled', 'rejected' => 'badge-soft-danger',
        default     => 'badge-soft-secondary'
    };
    return '<span class="badge ' . $class . ' px-2.5 py-1.5 rounded-pill">' . htmlspecialchars(ucfirst($status)) . '</span>';
}

/**
 * Return styled badge for Payment Status
 */
function get_payment_status_badge($status) {
    $class = match(strtolower($status)) {
        'paid'    => 'badge-soft-success',
        'pending' => 'badge-soft-warning',
        'failed'  => 'badge-soft-danger',
        default   => 'badge-soft-secondary'
    };
    return '<span class="badge ' . $class . ' px-2.5 py-1.5 rounded-pill">' . htmlspecialchars(ucfirst($status)) . '</span>';
}

/**
 * Return styled badge for Car Availability
 */
function get_car_availability_badge($availability) {
    if (strtolower($availability) === 'available') {
        return '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Available</span>';
    }
    return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Unavailable</span>';
}
