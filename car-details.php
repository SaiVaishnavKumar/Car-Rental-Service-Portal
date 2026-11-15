<?php
/**
 * DriveEasy – Car Details Page
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$car_id) {
    redirect('cars.php');
}

// Fetch Car Details with Category
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.description AS category_description
    FROM cars c
    JOIN categories cat ON c.category_id = cat.id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    set_flash('danger', 'The requested vehicle could not be found.');
    redirect('cars.php');
}

$page_title = $car['brand'] . ' ' . $car['model'] . ' (' . $car['year'] . ')';

// Parse features
$features_list = [];
if (!empty($car['features'])) {
    $features_list = array_map('trim', explode(',', $car['features']));
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('cars.php') ?>" class="text-muted">Cars</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <div class="row g-4">
            <!-- Left Column: Car Image & Overview -->
            <div class="col-lg-8">
                <!-- Large Image Preview Box -->
                <div class="card card-custom overflow-hidden mb-4 border-0 shadow-sm">
                    <div class="car-detail-img-box position-relative">
                        <span class="car-badge-category" style="top: 18px; left: 18px;">
                            <?= htmlspecialchars($car['category_name']) ?>
                        </span>
                        <div class="car-badge-available" style="top: 18px; right: 18px;">
                            <?= get_car_availability_badge($car['availability']) ?>
                        </div>
                        <img src="<?= get_car_image_url($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                    </div>
                </div>

                <!-- Specifications Grid -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-speedometer2 text-primary me-2"></i> Vehicle Specifications</h4>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-calendar-event fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Model Year</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['year']) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-fuel-pump fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Fuel Type</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['fuel_type']) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-gear-wide-connected fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Transmission</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['transmission']) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-people fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Seating Capacity</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['seats']) ?> Passengers</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-speedometer fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Mileage / Range</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['mileage'] ?: 'Standard') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-shield-check fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Registration</span>
                                <div class="fw-bold fs-6"><?= htmlspecialchars($car['registration_number']) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-shield-lock-fill fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Insurance</span>
                                <div class="fw-bold fs-6 text-success">Comprehensive</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-body-tertiary rounded-3 text-center h-100">
                                <i class="bi bi-check2-circle fs-3 text-primary mb-1 d-block"></i>
                                <span class="text-muted small">Service Status</span>
                                <div class="fw-bold fs-6 text-success">Certified</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-card-text text-primary me-2"></i> Vehicle Overview</h4>
                    <p class="text-muted leading-relaxed mb-0">
                        <?= nl2br(htmlspecialchars($car['description'] ?? 'This vehicle is part of the certified DriveEasy fleet, regularly inspected and deeply sanitized prior to each handover.')) ?>
                    </p>
                </div>

                <!-- Features & Amenities -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-stars text-primary me-2"></i> Features & Highlights</h4>
                    <?php if (!empty($features_list)): ?>
                        <div class="row g-2">
                            <?php foreach ($features_list as $feature): ?>
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center gap-2 p-2 bg-body-tertiary rounded-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span><?= htmlspecialchars($feature) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Standard safety kit, air conditioning, and factory infotainment included.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Sticky Pricing & Action Card -->
            <div class="col-lg-4">
                <div class="card card-custom p-4 sticky-top" style="top: 90px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-uppercase text-muted small fw-bold tracking-wider">Rental Price</span>
                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-fill-check"></i> Best Rate Guarantee</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <h2 class="display-6 fw-extrabold text-primary mb-0"><?= format_currency($car['price_per_day']) ?></h2>
                        <span class="text-muted fw-semibold">/ 24 Hours</span>
                    </div>

                    <div class="border-top border-bottom py-3 my-3">
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Refundable Deposit:</span>
                            <span class="text-success fw-bold">₹0.00 (Zero Deposit)</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Daily Kilometers:</span>
                            <span class="fw-semibold text-dark">Unlimited</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Cancellation:</span>
                            <span class="text-success fw-semibold">Free before pickup</span>
                        </div>
                    </div>

                    <?php if (strtolower($car['availability']) === 'available'): ?>
                        <a href="<?= base_url('booking.php?car_id=' . $car['id']) ?>" class="btn btn-primary btn-lg w-100 rounded-pill py-3 mb-2 shadow">
                            <i class="bi bi-calendar-check-fill me-2"></i> Book This Car
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 rounded-pill py-3 mb-2" disabled>
                            <i class="bi bi-x-circle me-2"></i> Currently Unavailable
                        </button>
                        <p class="text-center text-muted small mb-2">This car is currently leased out or undergoing routine service inspection.</p>
                    <?php endif; ?>

                    <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-secondary w-100 rounded-pill">
                        <i class="bi bi-arrow-left me-1"></i> Browse Other Cars
                    </a>

                    <!-- Assistance box -->
                    <div class="mt-4 pt-3 border-top text-center">
                        <p class="small text-muted mb-1">Need assistance with this reservation?</p>
                        <a href="<?= base_url('contact.php') ?>" class="small text-primary fw-bold text-decoration-none">
                            <i class="bi bi-telephone-outbound me-1"></i> Contact DriveEasy Concierge
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
