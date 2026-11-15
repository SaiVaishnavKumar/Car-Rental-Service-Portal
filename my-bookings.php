<?php
/**
 * DriveEasy – Customer My Bookings
 */
$page_title = "My Bookings";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = current_user();

// Handle Booking Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    $cancel_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if ($cancel_id) {
        // Ownership check
        $stmt = $pdo->prepare("SELECT booking_status FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$cancel_id, $user['id']]);
        $status = $stmt->fetchColumn();

        if ($status && !in_array($status, ['Completed', 'Cancelled', 'Rejected'])) {
            $update = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE id = ? AND user_id = ?");
            $update->execute([$cancel_id, $user['id']]);
            set_flash('success', 'Booking #' . $cancel_id . ' has been cancelled successfully.');
        } else {
            set_flash('danger', 'This booking cannot be cancelled because it is already ' . strtolower($status) . '.');
        }
    }
    redirect('my-bookings.php');
}

// Search Query by Booking ID or Car Name
$search_id = trim($_GET['search_id'] ?? '');

$sql = "
    SELECT b.*, c.brand, c.model, c.year, c.image, cat.name AS category_name
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    WHERE b.user_id = :user_id
";
$params = [':user_id' => $user['id']];

if (!empty($search_id)) {
    $sql .= " AND (b.id = :search_exact OR c.brand LIKE :search_like OR c.model LIKE :search_like)";
    $params[':search_exact'] = (int)$search_id;
    $params[':search_like'] = "%$search_id%";
}

$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb / Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h2 fw-bold mb-1"><i class="bi bi-calendar2-check-fill text-primary me-2"></i> My Rental Bookings</h1>
                <p class="text-muted small mb-0">Track and manage your upcoming and past car reservations</p>
            </div>
            
            <!-- Booking Search Bar -->
            <form action="<?= base_url('my-bookings.php') ?>" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="max-width: 280px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search_id" class="form-control" placeholder="Search by ID or car..." value="<?= htmlspecialchars($search_id) ?>">
                    <?php if ($search_id): ?>
                        <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">Search</button>
            </form>
        </div>

        <?php render_flash(); ?>

        <?php if (empty($bookings)): ?>
            <div class="card card-custom p-5 text-center my-4">
                <div class="display-3 text-muted mb-3"><i class="bi bi-calendar-x"></i></div>
                <h4 class="fw-bold">No Bookings Found</h4>
                <p class="text-muted max-w-500 mx-auto">
                    <?= $search_id ? 'No bookings matched your search query.' : 'You haven\'t made any car reservations yet. Explore our wide fleet and book your perfect drive today!' ?>
                </p>
                <div class="mt-3">
                    <a href="<?= base_url('cars.php') ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-car-front me-2"></i> Explore Rental Cars
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-custom overflow-hidden border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Car Details</th>
                                <th>Rental Period</th>
                                <th>Total Bill</th>
                                <th>Booking Status</th>
                                <th>Payment</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): 
                                $can_cancel = !in_array($b['booking_status'], ['Completed', 'Cancelled', 'Rejected']);
                            ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">#<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                        <div class="small text-muted"><?= format_date($b['created_at'], 'd M Y') ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= get_car_image_url($b['image']) ?>" alt="<?= htmlspecialchars($b['brand']) ?>" class="rounded-2" style="width: 60px; height: 40px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($b['brand'] . ' ' . $b['model']) ?></div>
                                                <span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($b['category_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="fw-semibold text-primary"><?= format_date($b['pickup_date']) ?></span> &rarr; <span class="fw-semibold text-primary"><?= format_date($b['return_date']) ?></span>
                                        </div>
                                        <span class="badge bg-light text-muted border small"><?= $b['number_of_days'] ?> Days</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= format_currency($b['total_amount']) ?></span>
                                        <div class="small text-muted"><?= format_currency($b['price_per_day']) ?> / day</div>
                                    </td>
                                    <td>
                                        <?= get_booking_status_badge($b['booking_status']) ?>
                                    </td>
                                    <td>
                                        <?= get_payment_status_badge($b['payment_status']) ?>
                                        <?php if ($b['payment_status'] === 'Pending' && $b['booking_status'] !== 'Cancelled'): ?>
                                            <a href="<?= base_url('booking-confirmation.php?booking_id=' . $b['id']) ?>" class="badge bg-warning text-dark text-decoration-none d-inline-block mt-1">
                                                <i class="bi bi-credit-card"></i> Pay Now
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= base_url('booking-details.php?id=' . $b['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5" title="View Full Details">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                            
                                            <?php if ($can_cancel): ?>
                                                <form action="<?= base_url('my-bookings.php') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="cancel_booking">
                                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill confirm-cancel" data-confirm-msg="Are you sure you want to cancel Booking #<?= $b['id'] ?>?">
                                                        <i class="bi bi-x-circle"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light text-muted border-0 rounded-pill" disabled title="Cannot cancel a <?= strtolower($b['booking_status']) ?> booking">
                                                    <i class="bi bi-dash-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
