<?php
/**
 * DriveEasy – Booking Confirmation & Simulated Payment
 */
$page_title = "Booking Confirmation & Payment";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = current_user();
$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$booking_id) {
    redirect('my-bookings.php');
}

// Fetch Booking with Car and User verification
$stmt = $pdo->prepare("
    SELECT b.*, c.brand, c.model, c.year, c.image, c.price_per_day, c.fuel_type, c.transmission,
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
    set_flash('danger', 'Booking record not found.');
    redirect('my-bookings.php');
}

// Security Ownership Check: Customer can only view their own booking unless Admin
if ($booking['user_id'] != $user['id'] && !is_admin()) {
    http_response_code(403);
    require_once __DIR__ . '/403.php';
    exit;
}

// Handle Payment Submission
$payment_processed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $payment_method = $_POST['payment_method'] ?? 'UPI';
    $valid_methods = ['UPI', 'Credit Card', 'Debit Card', 'Cash on Pickup'];

    if (!in_array($payment_method, $valid_methods)) {
        $payment_method = 'UPI';
    }

    $amount = (float)$booking['total_amount'];
    $transaction_id = 'TXN-' . strtoupper(substr($payment_method, 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(4)));
    $payment_status = ($payment_method === 'Cash on Pickup') ? 'Pending' : 'Paid';
    $new_booking_status = ($payment_method === 'Cash on Pickup') ? 'Pending' : 'Confirmed';

    // Insert into payments table
    $insertPayment = $pdo->prepare("
        INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, payment_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $insertPayment->execute([
        $booking['id'],
        $user['id'],
        $amount,
        $payment_method,
        $transaction_id,
        $payment_status
    ]);

    // Update bookings table
    $updateBooking = $pdo->prepare("
        UPDATE bookings 
        SET payment_status = ?, booking_status = ? 
        WHERE id = ?
    ");
    $updateBooking->execute([$payment_status, $new_booking_status, $booking['id']]);

    // Refresh booking record
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    $payment_processed = true;

    set_flash('success', 'Payment simulation successful! Your booking #' . $booking['id'] . ' is now confirmed.');
}

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
                <!-- If Payment is Still Pending and user hasn't paid yet, show Simulated Payment Flow -->
                <?php if ($booking['payment_status'] === 'Pending' && !$payment_processed): ?>
                    <div class="card card-custom p-4 p-md-5 mb-4 no-print">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="bg-warning bg-opacity-25 text-warning p-3 rounded-circle me-3 fs-3">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div>
                                <h2 class="h3 fw-bold mb-1">Simulated Payment Gateway</h2>
                                <p class="text-muted small mb-0">Select your simulated payment method to finalize Booking #<?= $booking['id'] ?></p>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Vehicle Summary Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                                    <span class="badge bg-primary-subtle text-primary mb-2"><?= htmlspecialchars($booking['category_name']) ?></span>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></h5>
                                    <div class="small text-muted mb-3">
                                        <i class="bi bi-calendar-event me-1"></i> <?= format_date($booking['pickup_date']) ?> &rarr; <?= format_date($booking['return_date']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Duration:</span>
                                        <strong><?= $booking['number_of_days'] ?> Days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Daily Rate:</span>
                                        <strong><?= format_currency($booking['price_per_day']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between fs-6 fw-bold text-dark pt-2 border-top mt-2">
                                        <span>Total Amount:</span>
                                        <span class="text-primary fs-5"><?= format_currency($booking['total_amount']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="col-md-6">
                                <form action="<?= base_url('booking-confirmation.php') ?>" method="POST" class="prevent-double-submit">
                                    <input type="hidden" name="action" value="process_payment">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                                    <h6 class="fw-bold mb-3">Choose Demo Payment Method:</h6>

                                    <div class="form-check p-3 rounded-3 border mb-2 cursor-pointer bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payMethodUPI" value="UPI" checked>
                                        <label class="form-check-label fw-bold d-flex justify-content-between align-items-center w-100" for="payMethodUPI">
                                            <span><i class="bi bi-qr-code text-primary me-2"></i> UPI (GPay / PhonePe / Paytm)</span>
                                            <span class="badge bg-success">Instant</span>
                                        </label>
                                    </div>

                                    <div class="form-check p-3 rounded-3 border mb-2 cursor-pointer bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payMethodCard" value="Credit Card">
                                        <label class="form-check-label fw-bold d-flex justify-content-between align-items-center w-100" for="payMethodCard">
                                            <span><i class="bi bi-credit-card-2-front text-primary me-2"></i> Credit / Debit Card</span>
                                            <span class="badge bg-secondary">Simulated</span>
                                        </label>
                                    </div>

                                    <div class="form-check p-3 rounded-3 border mb-3 cursor-pointer bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payMethodCash" value="Cash on Pickup">
                                        <label class="form-check-label fw-bold d-flex justify-content-between align-items-center w-100" for="payMethodCash">
                                            <span><i class="bi bi-cash-stack text-success me-2"></i> Pay on Vehicle Pickup</span>
                                            <span class="badge bg-warning text-dark">Flexible</span>
                                        </label>
                                    </div>

                                    <!-- Simulated Card Input Preview -->
                                    <div id="payment-details-card" class="d-none p-3 bg-light rounded-3 mb-3 border">
                                        <label class="form-label small text-muted mb-1">Demo Card (No real data stored)</label>
                                        <input type="text" class="form-control form-control-sm mb-2" placeholder="4111 2222 3333 4444" value="4111 •••• •••• 4242">
                                        <div class="row g-2">
                                            <div class="col-6"><input type="text" class="form-control form-control-sm" placeholder="MM/YY" value="12/28"></div>
                                            <div class="col-6"><input type="password" class="form-control form-control-sm" placeholder="CVV" value="123"></div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill py-2.5 shadow">
                                        <i class="bi bi-lock-fill me-1"></i> Pay <?= format_currency($booking['total_amount']) ?> (Simulated)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Printable Formal Booking Receipt / Confirmation Slip -->
                <div class="card card-custom p-4 p-md-5 print-invoice-box">
                    <!-- Slip Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-4 mb-4 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="42" class="bg-white p-1 rounded-2">
                            <div>
                                <h3 class="fw-extrabold mb-0">Booking Confirmation</h3>
                                <p class="text-muted small mb-0">Reservation Voucher & Receipt</p>
                            </div>
                        </div>
                        <div class="text-md-end mt-3 mt-md-0">
                            <span class="badge bg-dark fs-6 px-3 py-1.5 rounded-pill mb-1">Booking #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <div class="text-muted small">Date: <?= format_date($booking['created_at'], 'd M Y, h:i A') ?></div>
                        </div>
                    </div>

                    <!-- Status Badges -->
                    <div class="row g-3 mb-4 p-3 bg-body-tertiary rounded-3 border">
                        <div class="col-md-6 col-12 d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small">Booking Status:</span>
                            <?= get_booking_status_badge($booking['booking_status']) ?>
                        </div>
                        <div class="col-md-6 col-12 d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small">Payment Status:</span>
                            <?= get_payment_status_badge($booking['payment_status']) ?>
                        </div>
                    </div>

                    <!-- Details Columns -->
                    <div class="row g-4 mb-4">
                        <!-- Customer Details -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-person-fill text-primary me-1"></i> Customer Information</h6>
                            <div class="p-3 bg-light rounded-3">
                                <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($booking['customer_name']) ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($booking['customer_email']) ?></div>
                                <div class="text-muted small"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($booking['customer_phone'] ?: 'N/A') ?></div>
                            </div>
                        </div>

                        <!-- Vehicle Details -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-car-front-fill text-primary me-1"></i> Vehicle Reserved</h6>
                            <div class="p-3 bg-light rounded-3">
                                <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?> (<?= htmlspecialchars($booking['year']) ?>)</div>
                                <div class="text-muted small mb-1"><i class="bi bi-tags me-1"></i> Category: <?= htmlspecialchars($booking['category_name']) ?></div>
                                <div class="text-muted small"><i class="bi bi-fuel-pump me-1"></i> <?= htmlspecialchars($booking['fuel_type']) ?> &bull; <?= htmlspecialchars($booking['transmission']) ?></div>
                            </div>
                        </div>

                        <!-- Itinerary Details -->
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-geo-alt-fill text-primary me-1"></i> Schedule & Locations</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pickup Schedule</th>
                                            <th>Return Schedule</th>
                                            <th>Total Days</th>
                                            <th>Daily Price</th>
                                            <th>Total Bill</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= format_date($booking['pickup_date']) ?></div>
                                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($booking['pickup_location']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= format_date($booking['return_date']) ?></div>
                                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($booking['return_location']) ?></div>
                                            </td>
                                            <td class="fw-bold text-center"><?= $booking['number_of_days'] ?> Days</td>
                                            <td><?= format_currency($booking['price_per_day']) ?></td>
                                            <td class="fw-extrabold text-primary fs-5"><?= format_currency($booking['total_amount']) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Instructions -->
                    <div class="small text-muted mb-4 border-top pt-3">
                        <h6 class="fw-bold small text-dark mb-1">Handover Instructions:</h6>
                        <ul class="ps-3 mb-0">
                            <li>Please present a valid Driving License and Government ID proof at the time of car pickup.</li>
                            <li>Fuel level upon return should match the fuel level recorded during vehicle handover.</li>
                            <li>24/7 Roadside Assistance is active throughout your rental period: +91 98765 43210.</li>
                        </ul>
                    </div>

                    <!-- Action Buttons (Hidden on Print) -->
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center pt-3 border-top no-print">
                        <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> My Bookings
                        </a>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-eye me-1"></i> View Booking Details
                            </a>
                            <button onclick="window.print();" class="btn btn-primary rounded-pill shadow-sm">
                                <i class="bi bi-printer-fill me-1"></i> Print Booking Slip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
