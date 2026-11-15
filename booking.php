<?php
/**
 * DriveEasy – Car Booking Page with Date & Overlap Validation
 */
$page_title = "Book Your Car";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Must be logged in to book
$car_id_param = filter_input(INPUT_GET, 'car_id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
if (!$car_id_param) {
    redirect('cars.php');
}

require_login('booking.php?car_id=' . $car_id_param);

$user = current_user();

// Fetch Car Details
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name 
    FROM cars c
    JOIN categories cat ON c.category_id = cat.id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$car_id_param]);
$car = $stmt->fetch();

if (!$car) {
    set_flash('danger', 'The selected car could not be found.');
    redirect('cars.php');
}

if (strtolower($car['availability']) !== 'available') {
    set_flash('danger', 'This vehicle is currently unavailable for booking. Please select another car.');
    redirect('cars.php');
}

$errors = [];
$pickup_date     = $_POST['pickup_date'] ?? date('Y-m-d', strtotime('+1 day'));
$return_date     = $_POST['return_date'] ?? date('Y-m-d', strtotime('+3 days'));
$pickup_location = $_POST['pickup_location'] ?? 'Airport Terminal 3';
$return_location = $_POST['return_location'] ?? 'Airport Terminal 3';

$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date     = trim($_POST['pickup_date'] ?? '');
    $return_date     = trim($_POST['return_date'] ?? '');
    $pickup_location = trim($_POST['pickup_location'] ?? '');
    $return_location = trim($_POST['return_location'] ?? '');

    // 1. Validate Required Fields
    if (empty($pickup_date)) {
        $errors['pickup_date'] = 'Pickup date is required.';
    } elseif ($pickup_date < $today) {
        $errors['pickup_date'] = 'Pickup date cannot be earlier than today.';
    }

    if (empty($return_date)) {
        $errors['return_date'] = 'Return date is required.';
    } elseif ($return_date < $pickup_date) {
        $errors['return_date'] = 'Return date cannot be earlier than pickup date.';
    }

    if (empty($pickup_location)) {
        $errors['pickup_location'] = 'Pickup location is required.';
    }

    if (empty($return_location)) {
        $errors['return_location'] = 'Return location is required.';
    }

    // 2. Check Overlapping Bookings on Server
    if (empty($errors)) {
        $overlapQuery = "
            SELECT COUNT(*) FROM bookings 
            WHERE car_id = ? 
              AND booking_status NOT IN ('Cancelled', 'Rejected')
              AND (pickup_date <= ? AND return_date >= ?)
        ";
        $checkStmt = $pdo->prepare($overlapQuery);
        $checkStmt->execute([$car['id'], $return_date, $pickup_date]);
        $has_overlap = (int)$checkStmt->fetchColumn();

        if ($has_overlap > 0) {
            $errors['overlap'] = "This car is already booked between " . format_date($pickup_date) . " and " . format_date($return_date) . ". Please choose different rental dates.";
        }
    }

    // 3. Create Booking Record
    if (empty($errors)) {
        $days = calculate_days($pickup_date, $return_date);
        $price_per_day = (float)$car['price_per_day'];
        $total_amount = $days * $price_per_day;

        $insertBooking = $pdo->prepare("
            INSERT INTO bookings (
                user_id, car_id, pickup_date, return_date, 
                pickup_location, return_location, number_of_days, 
                price_per_day, total_amount, booking_status, payment_status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', NOW())
        ");

        try {
            $insertBooking->execute([
                $user['id'],
                $car['id'],
                $pickup_date,
                $return_date,
                $pickup_location,
                $return_location,
                $days,
                $price_per_day,
                $total_amount
            ]);

            $booking_id = $pdo->lastInsertId();
            set_flash('success', 'Your booking reservation has been created! Please complete payment simulation to confirm.');
            redirect('booking-confirmation.php?booking_id=' . $booking_id);
        } catch (PDOException $e) {
            $errors['general'] = 'An unexpected database error occurred while reserving the vehicle. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('cars.php') ?>" class="text-muted">Cars</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="text-muted"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left Column: Booking Form -->
            <div class="col-lg-7">
                <div class="card card-custom p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div>
                            <h2 class="h3 fw-bold mb-0">Rental Reservation</h2>
                            <p class="text-muted small mb-0">Complete your rental dates and pickup details</p>
                        </div>
                    </div>

                    <?php render_flash(); ?>

                    <?php if (isset($errors['overlap'])): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                            <div><strong>Booking Conflict:</strong> <?= htmlspecialchars($errors['overlap']) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger mb-4">
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <div id="bookingDateAlert" class="alert alert-danger d-none mb-4"></div>

                    <form action="<?= base_url('booking.php?car_id=' . $car['id']) ?>" method="POST" class="prevent-double-submit" id="carBookingForm">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

                        <!-- Customer Info (Read-Only) -->
                        <h5 class="fw-bold mb-3"><i class="bi bi-person-fill text-primary me-2"></i> Renter Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Primary Driver Name</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Contact Email</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Contact Phone</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['phone'] ?: '+91 (Not Provided)') ?>" readonly>
                            </div>
                        </div>

                        <!-- Date Selection -->
                        <h5 class="fw-bold mb-3"><i class="bi bi-calendar3 text-primary me-2"></i> Rental Schedule</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Pickup Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" name="pickup_date" id="pickup_date" class="form-control <?= isset($errors['pickup_date']) ? 'is-invalid' : '' ?>" min="<?= $today ?>" value="<?= htmlspecialchars($pickup_date) ?>" required>
                                    <?php if (isset($errors['pickup_date'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['pickup_date']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Return Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" name="return_date" id="return_date" class="form-control <?= isset($errors['return_date']) ? 'is-invalid' : '' ?>" min="<?= $pickup_date ?: $today ?>" value="<?= htmlspecialchars($return_date) ?>" required>
                                    <?php if (isset($errors['return_date'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['return_date']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Pickup & Return Locations -->
                        <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Handover Hubs</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Pickup Location <span class="text-danger">*</span></label>
                                <select name="pickup_location" class="form-select <?= isset($errors['pickup_location']) ? 'is-invalid' : '' ?>" required>
                                    <option value="Airport Terminal 3" <?= $pickup_location === 'Airport Terminal 3' ? 'selected' : '' ?>>Airport Terminal 3 (Arrivals Gate)</option>
                                    <option value="Downtown Central Station" <?= $pickup_location === 'Downtown Central Station' ? 'selected' : '' ?>>Downtown Central Station Hub</option>
                                    <option value="City Center Mall Hub" <?= $pickup_location === 'City Center Mall Hub' ? 'selected' : '' ?>>City Center Mall Parking P2</option>
                                    <option value="Tech Park Cyber City" <?= $pickup_location === 'Tech Park Cyber City' ? 'selected' : '' ?>>Tech Park Cyber City Tower A</option>
                                    <option value="Doorstep Delivery" <?= $pickup_location === 'Doorstep Delivery' ? 'selected' : '' ?>>Doorstep Home Delivery (Complimentary)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Return Location <span class="text-danger">*</span></label>
                                <select name="return_location" class="form-select <?= isset($errors['return_location']) ? 'is-invalid' : '' ?>" required>
                                    <option value="Airport Terminal 3" <?= $return_location === 'Airport Terminal 3' ? 'selected' : '' ?>>Airport Terminal 3 (Departures Gate)</option>
                                    <option value="Downtown Central Station" <?= $return_location === 'Downtown Central Station' ? 'selected' : '' ?>>Downtown Central Station Hub</option>
                                    <option value="City Center Mall Hub" <?= $return_location === 'City Center Mall Hub' ? 'selected' : '' ?>>City Center Mall Parking P2</option>
                                    <option value="Tech Park Cyber City" <?= $return_location === 'Tech Park Cyber City' ? 'selected' : '' ?>>Tech Park Cyber City Tower A</option>
                                    <option value="Doorstep Pickup" <?= $return_location === 'Doorstep Pickup' ? 'selected' : '' ?>>Doorstep Home Pickup</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" id="bookingSubmitBtn" class="btn btn-primary btn-lg rounded-pill py-3 shadow">
                                <i class="bi bi-shield-check me-2"></i> Proceed to Payment & Confirmation
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Vehicle & Live Fare Calculation Summary -->
            <div class="col-lg-5">
                <div class="card card-custom p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3"><i class="bi bi-receipt text-primary me-2"></i> Booking Summary</h5>

                    <!-- Selected Car Card Preview -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-body-tertiary rounded-3 mb-3 border">
                        <img src="<?= get_car_image_url($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" class="rounded-2" style="width: 100px; height: 65px; object-fit: cover;">
                        <div class="overflow-hidden">
                            <span class="badge bg-primary-subtle text-primary small mb-1"><?= htmlspecialchars($car['category_name']) ?></span>
                            <h6 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h6>
                            <div class="text-muted small"><?= htmlspecialchars($car['fuel_type']) ?> &bull; <?= htmlspecialchars($car['transmission']) ?></div>
                        </div>
                    </div>

                    <!-- Price Calculations -->
                    <div class="py-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Rate Per Day:</span>
                            <span class="fw-bold" id="price_per_day_val" data-price="<?= $car['price_per_day'] ?>">
                                <?= format_currency($car['price_per_day']) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Rental Duration:</span>
                            <span class="fw-bold text-primary" id="calc_days_val">--</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Insurance & Taxes:</span>
                            <span class="text-success fw-bold">Included (₹0.00)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Security Deposit:</span>
                            <span class="text-success fw-bold">₹0.00 (Zero Deposit)</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="h5 fw-bold mb-0">Estimated Total:</span>
                            <span class="h3 fw-extrabold text-primary mb-0" id="calc_total_val">₹0.00</span>
                        </div>
                    </div>

                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 small text-muted">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i>
                        Server-side real-time conflict checking ensures zero double-booking overlaps. Simulated payment simulation takes place on the next screen.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
