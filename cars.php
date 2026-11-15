<?php
/**
 * DriveEasy – Cars Fleet Listing with Search, Multi-Filter & Sorting
 */
$page_title = "Explore Our Fleet";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch Categories for Filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Read Filter / Search Parameters
$search       = trim($_GET['search'] ?? '');
$category_id  = trim($_GET['category'] ?? '');
$fuel_type    = trim($_GET['fuel'] ?? '');
$transmission = trim($_GET['transmission'] ?? '');
$seats        = trim($_GET['seats'] ?? '');
$min_price    = trim($_GET['min_price'] ?? '');
$max_price    = trim($_GET['max_price'] ?? '');
$availability = trim($_GET['availability'] ?? '');
$sort         = trim($_GET['sort'] ?? 'newest');

// Build SQL Query
$sql = "SELECT c.*, cat.name AS category_name 
        FROM cars c
        JOIN categories cat ON c.category_id = cat.id
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.brand LIKE :search OR c.model LIKE :search OR cat.name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category_id)) {
    $sql .= " AND c.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if (!empty($fuel_type)) {
    $sql .= " AND c.fuel_type = :fuel_type";
    $params[':fuel_type'] = $fuel_type;
}

if (!empty($transmission)) {
    $sql .= " AND c.transmission = :transmission";
    $params[':transmission'] = $transmission;
}

if (!empty($seats)) {
    $sql .= " AND c.seats = :seats";
    $params[':seats'] = (int)$seats;
}

if (!empty($min_price) && is_numeric($min_price)) {
    $sql .= " AND c.price_per_day >= :min_price";
    $params[':min_price'] = (float)$min_price;
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND c.price_per_day <= :max_price";
    $params[':max_price'] = (float)$max_price;
}

if (!empty($availability)) {
    $sql .= " AND c.availability = :availability";
    $params[':availability'] = $availability;
}

// Sorting logic
$sql .= match($sort) {
    'price_asc'  => " ORDER BY c.price_per_day ASC",
    'price_desc' => " ORDER BY c.price_per_day DESC",
    'name_asc'   => " ORDER BY c.brand ASC, c.model ASC",
    default      => " ORDER BY c.created_at DESC, c.id DESC"
};

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
    <div class="container">
        <!-- Breadcrumb / Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cars</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold mb-0">Our Rental Fleet</h1>
                <p class="text-muted small mb-0">Showing <strong><?= count($cars) ?></strong> vehicles available across categories</p>
            </div>
            
            <!-- Sorting Dropdown Form -->
            <form action="<?= base_url('cars.php') ?>" method="GET" class="d-flex align-items-center gap-2">
                <!-- Keep existing filters on sort change -->
                <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                <?php if ($category_id): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category_id) ?>"><?php endif; ?>
                <?php if ($fuel_type): ?><input type="hidden" name="fuel" value="<?= htmlspecialchars($fuel_type) ?>"><?php endif; ?>
                <?php if ($transmission): ?><input type="hidden" name="transmission" value="<?= htmlspecialchars($transmission) ?>"><?php endif; ?>
                <?php if ($seats): ?><input type="hidden" name="seats" value="<?= htmlspecialchars($seats) ?>"><?php endif; ?>
                <?php if ($min_price): ?><input type="hidden" name="min_price" value="<?= htmlspecialchars($min_price) ?>"><?php endif; ?>
                <?php if ($max_price): ?><input type="hidden" name="max_price" value="<?= htmlspecialchars($max_price) ?>"><?php endif; ?>
                <?php if ($availability): ?><input type="hidden" name="availability" value="<?= htmlspecialchars($availability) ?>"><?php endif; ?>

                <label class="form-label mb-0 small text-nowrap fw-semibold text-muted">Sort By:</label>
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit();" style="width: 170px;">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Brand: A to Z</option>
                </select>
            </form>
        </div>

        <?php render_flash(); ?>

        <div class="row g-4">
            <!-- Filter Sidebar -->
            <div class="col-lg-3">
                <div class="filter-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-1 text-primary"></i> Filter Fleet</h5>
                        <a href="<?= base_url('cars.php') ?>" class="text-danger small text-decoration-none">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>

                    <form action="<?= base_url('cars.php') ?>" method="GET">
                        <!-- Search Keyword -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Search Keyword</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Brand or model..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Body Type / Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fuel Type -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Fuel Type</label>
                            <select name="fuel" class="form-select form-select-sm">
                                <option value="">All Fuel Types</option>
                                <option value="Petrol" <?= $fuel_type === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="Diesel" <?= $fuel_type === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Electric" <?= $fuel_type === 'Electric' ? 'selected' : '' ?>>Electric (EV)</option>
                                <option value="Hybrid" <?= $fuel_type === 'Hybrid' ? 'selected' : '' ?>>Strong Hybrid</option>
                            </select>
                        </div>

                        <!-- Transmission -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Transmission</label>
                            <select name="transmission" class="form-select form-select-sm">
                                <option value="">All Transmissions</option>
                                <option value="Automatic" <?= $transmission === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $transmission === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>

                        <!-- Seating Capacity -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Seats</label>
                            <select name="seats" class="form-select form-select-sm">
                                <option value="">Any Seating</option>
                                <option value="4" <?= $seats === '4' ? 'selected' : '' ?>>4 Seater</option>
                                <option value="5" <?= $seats === '5' ? 'selected' : '' ?>>5 Seater</option>
                                <option value="7" <?= $seats === '7' ? 'selected' : '' ?>>7 Seater</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Price Per Day (₹)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ₹" value="<?= htmlspecialchars($min_price) ?>" step="100">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ₹" value="<?= htmlspecialchars($max_price) ?>" step="100">
                                </div>
                            </div>
                        </div>

                        <!-- Availability Status -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Availability</label>
                            <select name="availability" class="form-select form-select-sm">
                                <option value="">All Vehicles</option>
                                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available Only</option>
                                <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable / Booked</option>
                            </select>
                        </div>

                        <!-- Submit Filters -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill">
                                <i class="bi bi-funnel-fill me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cars Grid Column -->
            <div class="col-lg-9">
                <?php if (empty($cars)): ?>
                    <div class="card card-custom p-5 text-center my-4">
                        <div class="display-3 text-muted mb-3"><i class="bi bi-search"></i></div>
                        <h4 class="fw-bold">No Vehicles Found</h4>
                        <p class="text-muted max-w-500 mx-auto">We couldn't find any cars matching your current filter selections. Try broadening your criteria or reset filters.</p>
                        <div class="mt-3">
                            <a href="<?= base_url('cars.php') ?>" class="btn btn-primary rounded-pill">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Clear All Filters
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($cars as $car): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="car-card">
                                    <div class="car-img-wrapper">
                                        <span class="car-badge-category"><?= htmlspecialchars($car['category_name']) ?></span>
                                        <span class="car-badge-available">
                                            <?= get_car_availability_badge($car['availability']) ?>
                                        </span>
                                        <img src="<?= get_car_image_url($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" loading="lazy">
                                    </div>
                                    <div class="car-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h3 class="h6 fw-bold mb-0 text-truncate" title="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                                                <?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?>
                                            </h3>
                                            <span class="badge bg-light text-secondary border small"><?= htmlspecialchars($car['year']) ?></span>
                                        </div>
                                        <p class="text-muted small text-truncate-2 mb-2" style="min-height: 38px;">
                                            <?= htmlspecialchars($car['description'] ?? 'Inspected, sanitized, and ready for your rental journey.') ?>
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
                                            <div class="d-flex gap-1.5">
                                                <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-outline-secondary btn-sm px-2.5 rounded-pill" title="View Specifications">
                                                    <i class="bi bi-eye"></i> Details
                                                </a>
                                                <?php if (strtolower($car['availability']) === 'available'): ?>
                                                    <a href="<?= base_url('booking.php?car_id=' . $car['id']) ?>" class="btn btn-primary btn-sm px-3 rounded-pill">
                                                        Book
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
