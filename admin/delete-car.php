<?php
/**
 * DriveEasy – Admin Safe Delete Car
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    redirect('admin/cars.php');
}

// Fetch car details
$stmt = $pdo->prepare("SELECT brand, model, image FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    set_flash('danger', 'Vehicle not found.');
    redirect('admin/cars.php');
}

// Check for active or future bookings
$checkStmt = $pdo->prepare("
    SELECT COUNT(*) FROM bookings 
    WHERE car_id = ? AND booking_status IN ('Pending', 'Confirmed', 'Active')
");
$checkStmt->execute([$car_id]);
$active_bookings = (int)$checkStmt->fetchColumn();

if ($active_bookings > 0) {
    set_flash('danger', "Cannot delete \"{$car['brand']} {$car['model']}\" because it has {$active_bookings} active or pending customer reservation(s). You can set its availability to 'Unavailable' instead.");
    redirect('admin/cars.php');
}

// Safe to delete
try {
    $delStmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $delStmt->execute([$car_id]);

    // If it's a custom uploaded image, safely remove file
    if (!empty($car['image']) && str_starts_with($car['image'], 'car_')) {
        $imgPath = __DIR__ . '/../assets/images/cars/' . $car['image'];
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }

    set_flash('success', "Vehicle \"{$car['brand']} {$car['model']}\" was deleted successfully from the fleet.");
} catch (PDOException $e) {
    set_flash('danger', 'Unable to delete vehicle due to foreign key constraints with past completed bookings.');
}

redirect('admin/cars.php');
