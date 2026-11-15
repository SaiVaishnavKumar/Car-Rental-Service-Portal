<?php
/**
 * DriveEasy – About Us Page
 */
$page_title = "About Us";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Quick stats from DB
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- About Hero -->
    <section class="py-5 bg-dark text-white position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
        <div class="container py-4 text-center">
            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-3">About DriveEasy</span>
            <h1 class="display-4 fw-bold text-white mb-3">Redefining Self-Drive Car Rentals</h1>
            <p class="lead text-light-emphasis max-w-700 mx-auto" style="color: #cbd5e1 !important;">
                Born from a passion for open-road adventures, DriveEasy delivers premium car rental solutions with zero deposit barriers, transparent daily rates, and spotless certified vehicles.
            </p>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img src="<?= get_car_image_url('creta.jpg') ?>" alt="Hyundai Creta - DriveEasy Fleet" class="img-fluid rounded-4 shadow-lg border w-100" style="height: 360px; object-fit: cover; object-position: center;">
                </div>
                <div class="col-lg-6">
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1.5 rounded-pill mb-2">Our Mission</span>
                    <h2 class="fw-bold mb-3">Empowering Every Journey With Comfort & Freedom</h2>
                    <p class="text-muted leading-relaxed mb-4">
                        DriveEasy was created to eliminate the friction, confusing deposits, and hidden charges of traditional car rentals. Whether you need a fuel-efficient hatchback for urban errands, an executive sedan for business engagements, or a 4x4 SUV for a Himalayan expedition, our fleet is prepped and waiting.
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 bg-body-tertiary rounded-3 border">
                                <h5 class="fw-bold mb-1"><i class="bi bi-shield-check text-success me-1"></i> Safety First</h5>
                                <p class="small text-muted mb-0">Every vehicle undergoes a 45-point mechanical inspection before each key handover.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-body-tertiary rounded-3 border">
                                <h5 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-1"></i> 24/7 Concierge</h5>
                                <p class="small text-muted mb-0">Round-the-clock roadside breakdown support and instant emergency towing across India.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Real-Time Statistics -->
    <section class="py-5 bg-body-tertiary">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-body rounded-4 shadow-sm border">
                        <div class="display-5 fw-extrabold text-primary mb-1"><?= $total_cars ?>+</div>
                        <div class="text-muted fw-semibold">Modern Vehicles</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-body rounded-4 shadow-sm border">
                        <div class="display-5 fw-extrabold text-primary mb-1"><?= $total_users ?>+</div>
                        <div class="text-muted fw-semibold">Satisfied Renters</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-body rounded-4 shadow-sm border">
                        <div class="display-5 fw-extrabold text-primary mb-1"><?= $total_bookings ?>+</div>
                        <div class="text-muted fw-semibold">Trips Completed</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-body rounded-4 shadow-sm border">
                        <div class="display-5 fw-extrabold text-warning mb-1">4.9/5</div>
                        <div class="text-muted fw-semibold">Customer Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="py-5">
        <div class="container">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1.5 rounded-pill mb-2">Our Standards</span>
                <h2 class="fw-bold">What Sets DriveEasy Apart</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Zero Deposit Hassle</h4>
                        <p class="text-muted mb-0">Unlike other agencies that lock up thousands in deposits for weeks, DriveEasy offers trusted transparent bookings.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-sparkles"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Deep Sanitization</h4>
                        <p class="text-muted mb-0">Cars are interior vacuumed, ozone-sterilized, and touchpoints wiped down before your key collection.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-speedometer"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Unlimited Kilometers</h4>
                        <p class="text-muted mb-0">Never worry about calculating mileage limits or paying penalties for exploring scenic detours.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
