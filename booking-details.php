<?php
/**
 * DriveEasy – Single Booking Details & Invoice View
 */
$page_title = "Booking Details";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = current_user();
$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$booking_id) {
    redirect('my-bookings.php');
}

// Fetch Booking Record
$stmt = $pdo->prepare("
    SELECT b.*, c.brand, c.model, c.year, c.registration_number, c.fuel_type, c.transmission, c.seats, c.image,
           cat.name AS category_name, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
    LIMIT 1
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('danger', 'Booking not found.');
    redirect('my-bookings.php');
}

// Security Ownership Check
if ($booking['user_id'] != $user['id'] && !is_admin()) {
    http_response_code(403);
    require_once __DIR__ . '/403.php';
    exit;
}

// Fetch Payment Record if any
$payStmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1");
$payStmt->execute([$booking_id]);
$payment = $payStmt->fetch();

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    if (!in_array($booking['booking_status'], ['Completed', 'Cancelled', 'Rejected'])) {
        $update = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE id = ?");
        $update->execute([$booking['id']]);
        set_flash('success', 'Your booking #' . $booking['id'] . ' has been cancelled.');
        redirect('booking-details.php?id=' . $booking['id']);
    } else {
        set_flash('danger', 'This booking cannot be cancelled.');
    }
}

$page_title = "Booking #" . $booking['id'] . " – " . $booking['brand'] . ' ' . $booking['model'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb (hidden on print) -->
        <nav aria-label="breadcrumb" class="mb-4 no-print">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('my-bookings.php') ?>" class="text-muted">My Bookings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking #<?= $booking['id'] ?></li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-custom p-4 p-md-5 print-invoice-box">
                    <!-- Invoice Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-4 mb-4 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="44" class="bg-white p-1 rounded-2">
                            <div>
                                <h3 class="fw-bold mb-0">Booking Details & Invoice</h3>
                                <p class="text-muted small mb-0">Tax Invoice & Rental Agreement Summary</p>
                            </div>
                        </div>
                        <div class="text-md-end mt-3 mt-md-0">
                            <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill mb-1">ID #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <div class="text-muted small">Booked on: <?= format_date($booking['created_at'], 'd M Y, h:i A') ?></div>
                        </div>
                    </div>

                    <!-- Status Overview -->
                    <div class="row g-3 p-3 bg-body-tertiary rounded-3 border mb-4">
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small">Reservation Status:</span>
                            <?= get_booking_status_badge($booking['booking_status']) ?>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small">Payment Status:</span>
                            <?= get_payment_status_badge($booking['payment_status']) ?>
                            <?php if ($payment): ?>
                                <span class="badge bg-secondary-subtle text-secondary small">(<?= htmlspecialchars($payment['payment_method']) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Customer Info -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-person-fill text-primary me-1"></i> Customer Information</h6>
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($booking['customer_name']) ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($booking['customer_email']) ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($booking['customer_phone'] ?: 'N/A') ?></div>
                                <div class="text-muted small"><i class="bi bi-person-badge me-1"></i> User ID: #<?= $booking['user_id'] ?></div>
                            </div>
                        </div>

                        <!-- Vehicle Info -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-car-front-fill text-primary me-1"></i> Vehicle Information</h6>
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?> (<?= htmlspecialchars($booking['year']) ?>)</div>
                                <div class="text-muted small mb-1"><i class="bi bi-tags me-1"></i> <?= htmlspecialchars($booking['category_name']) ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-upc-scan me-1"></i> Reg. No: <?= htmlspecialchars($booking['registration_number']) ?></div>
                                <div class="text-muted small"><i class="bi bi-fuel-pump me-1"></i> <?= htmlspecialchars($booking['fuel_type']) ?> &bull; <?= htmlspecialchars($booking['transmission']) ?></div>
                            </div>
                        </div>

                        <!-- Schedule & Pricing Table -->
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-calendar-range text-primary me-1"></i> Rental Duration & Locations</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pickup</th>
                                            <th>Return</th>
                                            <th class="text-center">Days</th>
                                            <th>Rate / Day</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?= format_date($booking['pickup_date']) ?></div>
                                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($booking['pickup_location']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= format_date($booking['return_date']) ?></div>
                                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($booking['return_location']) ?></div>
                                            </td>
                                            <td class="text-center fw-bold"><?= $booking['number_of_days'] ?> Days</td>
                                            <td><?= format_currency($booking['price_per_day']) ?></td>
                                            <td class="text-end fw-extrabold text-primary fs-5"><?= format_currency($booking['total_amount']) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Transaction Details -->
                        <?php if ($payment): ?>
                            <div class="col-12">
                                <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-credit-card-2-front-fill text-primary me-1"></i> Transaction Record</h6>
                                <div class="p-3 bg-light rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-bold text-dark">Transaction ID: <code><?= htmlspecialchars($payment['transaction_id']) ?></code></div>
                                        <div class="text-muted small">Method: <?= htmlspecialchars($payment['payment_method']) ?> &bull; Time: <?= format_date($payment['created_at'], 'd M Y, h:i A') ?></div>
                                    </div>
                                    <div>
                                        <span class="badge bg-success px-3 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($payment['payment_status']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Notes -->
                    <div class="small text-muted border-top pt-3 mb-4">
                        <div class="fw-bold text-dark mb-1">DriveEasy Rental Agreement Summary:</div>
                        <p class="mb-1">This booking is covered by standard comprehensive vehicle insurance. Unlimited kilometers are included for the duration of the trip.</p>
                        <p class="mb-0">Support & Emergencies: +91 98765 43210 | Email: support@driveeasy.com</p>
                    </div>

                    <!-- Buttons (hidden on print) -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2 no-print">
                        <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> Back to My Bookings
                        </a>

                        <div class="d-flex gap-2">
                            <?php if (!in_array($booking['booking_status'], ['Completed', 'Cancelled', 'Rejected'])): ?>
                                <form action="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="cancel_booking">
                                    <button type="submit" class="btn btn-outline-danger rounded-pill confirm-cancel" data-confirm-msg="Are you sure you want to cancel this booking reservation?">
                                        <i class="bi bi-x-circle me-1"></i> Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($booking['payment_status'] === 'Pending' && $booking['booking_status'] !== 'Cancelled'): ?>
                                <a href="<?= base_url('booking-confirmation.php?booking_id=' . $booking['id']) ?>" class="btn btn-warning rounded-pill">
                                    <i class="bi bi-wallet2 me-1"></i> Complete Payment
                                </a>
                            <?php endif; ?>

                            <button onclick="window.print();" class="btn btn-primary rounded-pill shadow-sm">
                                <i class="bi bi-printer-fill me-1"></i> Print Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
