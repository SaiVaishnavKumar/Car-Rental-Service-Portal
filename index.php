<?php
/**
 * DriveEasy – Home Page
 */
$page_title = "Rent Your Perfect Car. Drive Your Journey";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch Popular / Featured Cars from MySQL
$stmt = $pdo->query("
    SELECT c.*, cat.name AS category_name 
    FROM cars c
    JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.price_per_day DESC
    LIMIT 6
");
$featured_cars = $stmt->fetchAll();

// Fetch Categories for Quick Selector
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php render_flash(); ?>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
                        <i class="bi bi-stars me-1"></i> Premium Car Rental Service
                    </span>
                    <h1 class="display-4 fw-extrabold text-white mb-3">
                        Rent Your Perfect Car.<br>
                        <span class="text-primary-subtle" style="color: #60a5fa !important;">Drive Your Journey.</span>
                    </h1>
                    <p class="lead text-light-emphasis mb-4 pe-lg-4" style="color: #cbd5e1 !important;">
                        Find reliable and affordable cars for your next trip. Experience seamless bookings, spotless verified vehicles, transparent pricing, and 24/7 roadside assistance.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= base_url('cars.php') ?>" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
                            <i class="bi bi-car-front me-2"></i> Browse Cars
                        </a>
                        <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            <i class="bi bi-calendar2-check me-2"></i> Book Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-car-showcase position-relative border border-secondary border-opacity-25">
                        <img src="<?= get_car_image_url('fortuner.jpg') ?>" alt="Toyota Fortuner 4x4" class="w-100 img-fluid" style="height: 330px; object-fit: cover; object-position: center;">
                        <div class="position-absolute bottom-0 start-0 end-0 p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(to top, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.7) 60%, transparent);">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-1"><i class="bi bi-star-fill me-1"></i> TOP RATED</span>
                                <h5 class="fw-bold mb-0 text-white">Toyota Fortuner 4x4</h5>
                                <div class="small text-light-emphasis">Rugged 7-Seater Luxury SUV</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5 text-warning">₹4,500<span class="fs-6 text-white-50">/d</span></div>
                                <a href="<?= base_url('booking.php?car_id=1') ?>" class="btn btn-primary btn-sm rounded-pill px-3 mt-1 shadow-sm">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Search Overlay Card -->
            <div class="hero-search-card">
                <form action="<?= base_url('cars.php') ?>" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-search text-primary me-1"></i> Search Car or Brand
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="e.g. Fortuner, Creta, City...">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-tags text-primary me-1"></i> Vehicle Category
                        </label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-fuel-pump text-primary me-1"></i> Fuel Preference
                        </label>
                        <select name="fuel" class="form-select">
                            <option value="">Any Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel-fill me-1"></i> Find Cars
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 my-3">
        <div class="container">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1.5 rounded-pill mb-2">Why Choose DriveEasy</span>
                <h2 class="fw-bold">Designed For Seamless & Safe Journeys</h2>
                <p class="text-muted">We provide an unmatched car rental experience with first-class convenience and complete peace of mind.</p>
            </div>

            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Wide Range of Cars</h4>
                        <p class="text-muted mb-0">Choose from luxury sedans, rugged 4x4 SUVs, economical hatchbacks, and green EVs to match any journey.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Easy Booking</h4>
                        <p class="text-muted mb-0">Book your dream car in three quick steps. Instant online availability check and rapid confirmation.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Affordable Prices</h4>
                        <p class="text-muted mb-0">Transparent daily rental pricing with zero surprise charges. High mileage limits and competitive packages.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Secure Booking</h4>
                        <p class="text-muted mb-0">Protected customer accounts, industry-standard encrypted credentials, and authentic simulated payments.</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">Flexible Pickup</h4>
                        <p class="text-muted mb-0">Choose convenient pickup and drop-off hubs across international airport terminals, rail stations, and city hubs.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">24/7 Support</h4>
                        <p class="text-muted mb-0">Round-the-clock dedicated customer assistance and 24-hour on-road breakdown support whenever needed.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Cars Section (Fetched dynamically from MySQL) -->
    <section class="py-5 bg-body-tertiary">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
                <div>
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1.5 rounded-pill mb-2">Our Premium Fleet</span>
                    <h2 class="fw-bold mb-1">Most Popular Rental Cars</h2>
                    <p class="text-muted mb-0">Meticulously serviced, sanitized, and ready for your next adventure.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-primary rounded-pill">
                        View Entire Fleet <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($featured_cars as $car): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="car-card">
                            <div class="car-img-wrapper">
                                <span class="car-badge-category"><?= htmlspecialchars($car['category_name']) ?></span>
                                <span class="car-badge-available">
                                    <?= get_car_availability_badge($car['availability']) ?>
                                </span>
                                <img src="<?= get_car_image_url($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                            </div>
                            <div class="car-card-body">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h3 class="h5 fw-bold mb-0 text-truncate">
                                        <?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?>
                                    </h3>
                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($car['year']) ?></span>
                                </div>
                                <p class="text-muted small text-truncate-2 mb-2" style="min-height: 38px;">
                                    <?= htmlspecialchars($car['description'] ?? 'Verified rental car with high safety ratings.') ?>
                                </p>

                                <div class="car-spec-grid">
                                    <div class="car-spec-item" title="Fuel Type">
                                        <i class="bi bi-fuel-pump"></i>
                                        <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                                    </div>
                                    <div class="car-spec-item" title="Transmission">
                                        <i class="bi bi-gear-wide-connected"></i>
                                        <span><?= htmlspecialchars($car['transmission']) ?></span>
                                    </div>
                                    <div class="car-spec-item" title="Seats">
                                        <i class="bi bi-people"></i>
                                        <span><?= htmlspecialchars($car['seats']) ?> Seats</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2">
                                    <div>
                                        <span class="car-price-tag"><?= format_currency($car['price_per_day']) ?></span>
                                        <span class="car-price-unit">/ day</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-outline-secondary btn-sm px-2.5 rounded-pill" title="View Details">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                        <?php if (strtolower($car['availability']) === 'available'): ?>
                                            <a href="<?= base_url('booking.php?car_id=' . $car['id']) ?>" class="btn btn-primary btn-sm px-3 rounded-pill">
                                                Book Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm px-3 rounded-pill" disabled>
                                                Booked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="py-5">
        <div class="container">
            <div class="p-5 rounded-4 text-white text-center position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);">
                <div class="position-relative" style="z-index: 2;">
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-3">Drive Your Dream</span>
                    <h2 class="display-5 fw-bold text-white mb-3">Ready to Embark on Your Next Road Trip?</h2>
                    <p class="lead text-light-emphasis max-w-600 mx-auto mb-4" style="color: #cbd5e1 !important;">
                        Sign up in under 60 seconds and gain instant access to our entire premium fleet with zero paperwork hassle.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="<?= base_url('register.php') ?>" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold">
                            <i class="bi bi-person-plus-fill me-1"></i> Register Free Account
                        </a>
                        <a href="<?= base_url('contact.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            <i class="bi bi-telephone-fill me-1"></i> Speak to Concierge
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
