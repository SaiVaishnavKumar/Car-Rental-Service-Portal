<?php
/**
 * DriveEasy – Admin Booking Management
 */
$page_title = "Manage Bookings";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking_status') {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $new_status = trim($_POST['new_status'] ?? '');
    $valid_statuses = ['Pending', 'Confirmed', 'Rejected', 'Active', 'Completed', 'Cancelled'];

    if ($booking_id && in_array($new_status, $valid_statuses)) {
        $update = $pdo->prepare("UPDATE bookings SET booking_status = ? WHERE id = ?");
        $update->execute([$new_status, $booking_id]);
        set_flash('success', "Booking #{$booking_id} status updated to: {$new_status}");
    }
    redirect('admin/bookings.php');
}

// Handle Payment Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_payment_status') {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if ($booking_id) {
        $curPayStmt = $pdo->prepare("SELECT payment_status, total_amount, user_id FROM bookings WHERE id = ?");
        $curPayStmt->execute([$booking_id]);
        $bookingData = $curPayStmt->fetch();

        if ($bookingData) {
            $new_pay = ($bookingData['payment_status'] === 'Paid') ? 'Pending' : 'Paid';
            $updatePay = $pdo->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
            $updatePay->execute([$new_pay, $booking_id]);

            // If marked Paid, ensure a payment record exists in payments table
            if ($new_pay === 'Paid') {
                $checkPay = $pdo->prepare("SELECT id FROM payments WHERE booking_id = ? LIMIT 1");
                $checkPay->execute([$booking_id]);
                if (!$checkPay->fetch()) {
                    $txn = 'TXN-ADM-' . strtoupper(bin2hex(random_bytes(4)));
                    $insertP = $pdo->prepare("
                        INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, payment_status, created_at)
                        VALUES (?, ?, ?, 'Cash on Pickup', ?, 'Paid', NOW())
                    ");
                    $insertP->execute([$booking_id, $bookingData['user_id'], $bookingData['total_amount'], $txn]);
                }
            }
            set_flash('success', "Payment status for Booking #{$booking_id} set to: {$new_pay}");
        }
    }
    redirect('admin/bookings.php');
}

// Search & Filter
$search_query   = trim($_GET['search'] ?? '');
$status_filter  = trim($_GET['status'] ?? '');
$payment_filter = trim($_GET['payment'] ?? '');

$sql = "
    SELECT b.*, c.brand, c.model, c.registration_number, c.image, cat.name AS category_name,
           u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON b.user_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (b.id = :search_exact OR u.name LIKE :search_like OR u.email LIKE :search_like OR c.brand LIKE :search_like OR c.model LIKE :search_like)";
    $params[':search_exact'] = (int)$search_query;
    $params[':search_like']  = "%$search_query%";
}

if (!empty($status_filter)) {
    $sql .= " AND b.booking_status = :status_filter";
    $params[':status_filter'] = $status_filter;
}

if (!empty($payment_filter)) {
    $sql .= " AND b.payment_status = :payment_filter";
    $params[':payment_filter'] = $payment_filter;
}

$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggleBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h4 class="mb-0 fw-bold">Customer Bookings Management</h4>
            </div>

            <div class="text-muted small">
                Total Reservations: <strong><?= count($bookings) ?></strong>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <!-- Filter Card -->
            <div class="card card-custom p-3 mb-4">
                <form action="<?= base_url('admin/bookings.php') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by ID, customer name, email, car..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Booking Statuses</option>
                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="Active" <?= $status_filter === 'Active' ? 'selected' : '' ?>>Active (On-Road)</option>
                            <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="payment" class="form-select form-select-sm">
                            <option value="">All Payments</option>
                            <option value="Paid" <?= $payment_filter === 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Pending" <?= $payment_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">Filter</button>
                        <?php if ($search_query || $status_filter || $payment_filter): ?>
                            <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Bookings List Table -->
            <div class="card card-custom overflow-hidden border-0 shadow-sm">
                <?php if (empty($bookings)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-calendar-x display-4 mb-2 d-block text-secondary"></i>
                        <h5>No bookings found</h5>
                        <p class="small mb-0">No customer reservations match the specified filters.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Car Reserved</th>
                                    <th>Rental Schedule</th>
                                    <th>Total Fare</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th class="text-end">Manage Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#<?= $b['id'] ?></span>
                                            <div class="small text-muted"><?= format_date($b['created_at'], 'd M') ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 140px;"><?= htmlspecialchars($b['customer_name']) ?></div>
                                            <div class="small text-muted text-truncate" style="max-width: 140px;"><?= htmlspecialchars($b['customer_email']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($b['customer_phone'] ?: 'N/A') ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= get_car_image_url($b['image']) ?>" alt="<?= htmlspecialchars($b['brand']) ?>" class="rounded border" style="width: 50px; height: 32px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold text-dark text-truncate" style="max-width: 130px;"><?= htmlspecialchars($b['brand'] . ' ' . $b['model']) ?></div>
                                                    <code><?= htmlspecialchars($b['registration_number']) ?></code>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <span class="fw-semibold text-primary"><?= format_date($b['pickup_date'], 'd M Y') ?></span> &rarr;
                                                <span class="fw-semibold text-primary"><?= format_date($b['return_date'], 'd M Y') ?></span>
                                            </div>
                                            <div class="small text-muted mt-0.5">
                                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($b['pickup_location']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= format_currency($b['total_amount']) ?></span>
                                            <div class="small text-muted"><?= $b['number_of_days'] ?> Days</div>
                                        </td>
                                        <td>
                                            <?= get_booking_status_badge($b['booking_status']) ?>
                                        </td>
                                        <td>
                                            <!-- Toggle Payment Button -->
                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_payment_status">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle payment status">
                                                    <?= get_payment_status_badge($b['payment_status']) ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <!-- Status Change Dropdown -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle rounded-pill px-3" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                    <li><h6 class="dropdown-header small">Change Booking Status</h6></li>
                                                    
                                                    <!-- Confirm Action -->
                                                    <?php if ($b['booking_status'] !== 'Confirmed'): ?>
                                                        <li>
                                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST">
                                                                <input type="hidden" name="action" value="update_booking_status">
                                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                                <input type="hidden" name="new_status" value="Confirmed">
                                                                <button type="submit" class="dropdown-item py-1.5 text-success">
                                                                    <i class="bi bi-check-circle me-1"></i> Approve / Confirm
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <!-- Mark Active Action -->
                                                    <?php if ($b['booking_status'] !== 'Active'): ?>
                                                        <li>
                                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST">
                                                                <input type="hidden" name="action" value="update_booking_status">
                                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                                <input type="hidden" name="new_status" value="Active">
                                                                <button type="submit" class="dropdown-item py-1.5 text-info">
                                                                    <i class="bi bi-signpost-split me-1"></i> Mark Active (On-Road)
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <!-- Mark Completed Action -->
                                                    <?php if ($b['booking_status'] !== 'Completed'): ?>
                                                        <li>
                                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST">
                                                                <input type="hidden" name="action" value="update_booking_status">
                                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                                <input type="hidden" name="new_status" value="Completed">
                                                                <button type="submit" class="dropdown-item py-1.5 text-primary">
                                                                    <i class="bi bi-check2-all me-1"></i> Mark Completed
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <!-- Reject Action -->
                                                    <?php if ($b['booking_status'] !== 'Rejected'): ?>
                                                        <li>
                                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST">
                                                                <input type="hidden" name="action" value="update_booking_status">
                                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                                <input type="hidden" name="new_status" value="Rejected">
                                                                <button type="submit" class="dropdown-item py-1.5 text-warning">
                                                                    <i class="bi bi-x-octagon me-1"></i> Reject Booking
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <!-- Cancel Action -->
                                                    <?php if ($b['booking_status'] !== 'Cancelled'): ?>
                                                        <li>
                                                            <form action="<?= base_url('admin/bookings.php') ?>" method="POST">
                                                                <input type="hidden" name="action" value="update_booking_status">
                                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                                <input type="hidden" name="new_status" value="Cancelled">
                                                                <button type="submit" class="dropdown-item py-1.5 text-danger confirm-cancel" data-confirm-msg="Are you sure you want to cancel booking #<?= $b['id'] ?>?">
                                                                    <i class="bi bi-x-circle me-1"></i> Cancel Booking
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <a href="<?= base_url('booking-details.php?id=' . $b['id']) ?>" target="_blank" class="dropdown-item py-1.5">
                                                            <i class="bi bi-printer me-1"></i> View / Print Slip
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
