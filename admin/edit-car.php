<?php
/**
 * DriveEasy – Admin Edit Car
 */
$page_title = "Edit Vehicle";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    redirect('admin/cars.php');
}

// Fetch car
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? LIMIT 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    set_flash('danger', 'Car record not found.');
    redirect('admin/cars.php');
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$brand               = $car['brand'];
$model               = $car['model'];
$category_id         = $car['category_id'];
$year                = $car['year'];
$registration_number = $car['registration_number'];
$price_per_day       = $car['price_per_day'];
$fuel_type           = $car['fuel_type'];
$transmission        = $car['transmission'];
$seats               = $car['seats'];
$mileage             = $car['mileage'];
$description         = $car['description'];
$features            = $car['features'];
$availability        = $car['availability'];
$image_filename      = $car['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand               = trim($_POST['brand'] ?? '');
    $model               = trim($_POST['model'] ?? '');
    $category_id         = trim($_POST['category_id'] ?? '');
    $year                = (int)($_POST['year'] ?? date('Y'));
    $registration_number = strtoupper(trim($_POST['registration_number'] ?? ''));
    $price_per_day       = (float)($_POST['price_per_day'] ?? 0);
    $fuel_type           = trim($_POST['fuel_type'] ?? 'Petrol');
    $transmission        = trim($_POST['transmission'] ?? 'Automatic');
    $seats               = (int)($_POST['seats'] ?? 5);
    $mileage             = trim($_POST['mileage'] ?? '');
    $description         = trim($_POST['description'] ?? '');
    $features            = trim($_POST['features'] ?? '');
    $availability        = trim($_POST['availability'] ?? 'available');

    // Validation
    if (empty($brand)) $errors['brand'] = 'Brand name is required.';
    if (empty($model)) $errors['model'] = 'Model name is required.';
    if (empty($category_id)) $errors['category_id'] = 'Please select a vehicle category.';
    if ($year < 2000 || $year > (int)date('Y') + 1) $errors['year'] = 'Please enter a valid model year.';
    if ($price_per_day <= 0) $errors['price_per_day'] = 'Price per day must be greater than zero.';
    if ($seats < 1) $errors['seats'] = 'Seating capacity must be at least 1.';

    if (empty($registration_number)) {
        $errors['registration_number'] = 'Registration / License plate is required.';
    } else {
        // Unique check excluding current car
        $checkPlate = $pdo->prepare("SELECT id FROM cars WHERE registration_number = ? AND id != ? LIMIT 1");
        $checkPlate->execute([$registration_number, $car['id']]);
        if ($checkPlate->fetch()) {
            $errors['registration_number'] = 'This registration number is already registered to another car.';
        }
    }

    // Handle Image Replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'File upload failed with error code: ' . $file['error'];
        } else {
            $max_size = 4 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                $errors['image'] = 'Image size must be less than 4MB.';
            } else {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions)) {
                    $errors['image'] = 'Only JPG, JPEG, PNG, WEBP, and SVG image files are allowed.';
                } else {
                    $new_filename = 'car_' . uniqid() . '_' . time() . '.' . $ext;
                    $target_dir = __DIR__ . '/../assets/images/cars/';
                    
                    if (move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
                        $image_filename = $new_filename;
                    } else {
                        $errors['image'] = 'Failed to save uploaded image file.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare("
            UPDATE cars SET 
                category_id = ?, brand = ?, model = ?, year = ?, registration_number = ?,
                price_per_day = ?, fuel_type = ?, transmission = ?, seats = ?, mileage = ?,
                description = ?, features = ?, image = ?, availability = ?
            WHERE id = ?
        ");

        try {
            $updateStmt->execute([
                $category_id, $brand, $model, $year, $registration_number,
                $price_per_day, $fuel_type, $transmission, $seats, $mileage,
                $description, $features, $image_filename, $availability,
                $car['id']
            ]);

            set_flash('success', "Vehicle \"{$brand} {$model}\" has been updated successfully!");
            redirect('admin/cars.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Database update failed: ' . $e->getMessage();
        }
    }
}

$page_title = "Edit " . $car['brand'] . ' ' . $car['model'];
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
                <h4 class="mb-0 fw-bold">Edit Vehicle #<?= $car['id'] ?></h4>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Preview
                </a>
                <a href="<?= base_url('admin/cars.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm max-w-900 mx-auto">
                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Update <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                        <p class="text-muted small mb-0">Modify specifications, pricing, availability and photo</p>
                    </div>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger mb-4"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('admin/edit-car.php?id=' . $car['id']) ?>" method="POST" enctype="multipart/form-data" class="prevent-double-submit">
                    <input type="hidden" name="id" value="<?= $car['id'] ?>">

                    <!-- Core Information -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Core Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Brand <span class="text-danger">*</span></label>
                            <input type="text" name="brand" class="form-control <?= isset($errors['brand']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($brand) ?>" required>
                            <?php if (isset($errors['brand'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['brand']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control <?= isset($errors['model']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($model) ?>" required>
                            <?php if (isset($errors['model'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['model']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Model Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($year) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Registration Number <span class="text-danger">*</span></label>
                            <input type="text" name="registration_number" class="form-control text-uppercase <?= isset($errors['registration_number']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($registration_number) ?>" required>
                            <?php if (isset($errors['registration_number'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['registration_number']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Price Per Day (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_day" class="form-control <?= isset($errors['price_per_day']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($price_per_day) ?>" step="50" min="100" required>
                        </div>
                    </div>

                    <!-- Technical Specs -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-gear-fill text-primary me-2"></i> Technical Specifications</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold">Fuel Type</label>
                            <select name="fuel_type" class="form-select">
                                <option value="Petrol" <?= $fuel_type === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="Diesel" <?= $fuel_type === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Electric" <?= $fuel_type === 'Electric' ? 'selected' : '' ?>>Electric (EV)</option>
                                <option value="Hybrid" <?= $fuel_type === 'Hybrid' ? 'selected' : '' ?>>Strong Hybrid</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold">Transmission</label>
                            <select name="transmission" class="form-select">
                                <option value="Automatic" <?= $transmission === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $transmission === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold">Seats</label>
                            <input type="number" name="seats" class="form-control" value="<?= htmlspecialchars($seats) ?>" min="1" max="15" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold">Mileage / Range</label>
                            <input type="text" name="mileage" class="form-control" value="<?= htmlspecialchars($mileage) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Availability Status</label>
                            <select name="availability" class="form-select">
                                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available for Booking</option>
                                <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable / In Maintenance</option>
                            </select>
                        </div>

                        <!-- Current Image & Replacement -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Replace Vehicle Image</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img id="car_image_preview" src="<?= get_car_image_url($car['image']) ?>" alt="Current Car Image" class="rounded border" style="width: 80px; height: 50px; object-fit: cover;">
                                <div class="small text-muted">Current: <?= htmlspecialchars($car['image']) ?></div>
                            </div>
                            <input type="file" name="image" id="car_image_input" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept=".jpg,.jpeg,.png,.webp,.svg">
                            <div class="form-text small">Leave empty to keep existing image file.</div>
                            <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <!-- Features & Description -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-card-text text-primary me-2"></i> Features & Description</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Features (Comma-separated)</label>
                            <input type="text" name="features" class="form-control" value="<?= htmlspecialchars($features) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Vehicle Description</label>
                            <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($description) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= base_url('admin/cars.php') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
