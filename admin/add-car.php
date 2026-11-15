<?php
/**
 * DriveEasy – Admin Add New Car
 */
$page_title = "Add New Car";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$brand               = '';
$model               = '';
$category_id         = '';
$year                = date('Y');
$registration_number = '';
$price_per_day       = '';
$fuel_type           = 'Petrol';
$transmission        = 'Automatic';
$seats               = '5';
$mileage             = '';
$description         = '';
$features            = 'Air Conditioning, Bluetooth, GPS Navigation, USB Charger, Reverse Camera';
$availability        = 'available';

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
        // Unique check
        $checkPlate = $pdo->prepare("SELECT id FROM cars WHERE registration_number = ? LIMIT 1");
        $checkPlate->execute([$registration_number]);
        if ($checkPlate->fetch()) {
            $errors['registration_number'] = 'This registration number is already registered to another car.';
        }
    }

    // Secure Image Upload Handling
    $image_filename = 'default-car.svg';

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'File upload failed with error code: ' . $file['error'];
        } else {
            $max_size = 4 * 1024 * 1024; // 4MB
            if ($file['size'] > $max_size) {
                $errors['image'] = 'Image size must be less than 4MB.';
            } else {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions)) {
                    $errors['image'] = 'Only JPG, JPEG, PNG, WEBP, and SVG image files are allowed.';
                } else {
                    // Generate safe unique filename
                    $image_filename = 'car_' . uniqid() . '_' . time() . '.' . $ext;
                    $target_dir = __DIR__ . '/../assets/images/cars/';
                    
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    $target_path = $target_dir . $image_filename;
                    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                        $errors['image'] = 'Failed to save uploaded image file.';
                    }
                }
            }
        }
    }

    // Insert into Database
    if (empty($errors)) {
        $insertStmt = $pdo->prepare("
            INSERT INTO cars (
                category_id, brand, model, year, registration_number, 
                price_per_day, fuel_type, transmission, seats, mileage, 
                description, features, image, availability, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        try {
            $insertStmt->execute([
                $category_id, $brand, $model, $year, $registration_number,
                $price_per_day, $fuel_type, $transmission, $seats, $mileage,
                $description, $features, $image_filename, $availability
            ]);

            set_flash('success', "New vehicle \"{$brand} {$model}\" has been added to the fleet successfully!");
            redirect('admin/cars.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

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
                <h4 class="mb-0 fw-bold">Add New Fleet Vehicle</h4>
            </div>

            <div>
                <a href="<?= base_url('admin/cars.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Fleet
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm max-w-900 mx-auto">
                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Vehicle Specifications & Details</h3>
                        <p class="text-muted small mb-0">Enter full specifications, upload vehicle photos, and set daily rates</p>
                    </div>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger mb-4"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('admin/add-car.php') ?>" method="POST" enctype="multipart/form-data" class="prevent-double-submit">
                    <!-- General Vehicle Information -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Core Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Brand / Manufacturer <span class="text-danger">*</span></label>
                            <input type="text" name="brand" class="form-control <?= isset($errors['brand']) ? 'is-invalid' : '' ?>" placeholder="e.g. Toyota" value="<?= htmlspecialchars($brand) ?>" required>
                            <?php if (isset($errors['brand'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['brand']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Model Name & Trim <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control <?= isset($errors['model']) ? 'is-invalid' : '' ?>" placeholder="e.g. Fortuner 4x4" value="<?= htmlspecialchars($model) ?>" required>
                            <?php if (isset($errors['model'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['model']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Body Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Model Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($year) ?>" min="2000" max="<?= date('Y') + 1 ?>" required>
                            <?php if (isset($errors['year'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['year']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Registration Number <span class="text-danger">*</span></label>
                            <input type="text" name="registration_number" class="form-control text-uppercase <?= isset($errors['registration_number']) ? 'is-invalid' : '' ?>" placeholder="DL-01-AB-1234" value="<?= htmlspecialchars($registration_number) ?>" required>
                            <?php if (isset($errors['registration_number'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['registration_number']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Price Per Day (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_day" class="form-control <?= isset($errors['price_per_day']) ? 'is-invalid' : '' ?>" placeholder="e.g. 3500.00" value="<?= htmlspecialchars($price_per_day) ?>" step="50" min="100" required>
                            <?php if (isset($errors['price_per_day'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['price_per_day']) ?></div><?php endif; ?>
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
                            <input type="text" name="mileage" class="form-control" placeholder="e.g. 16 km/l" value="<?= htmlspecialchars($mileage) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Availability Status</label>
                            <select name="availability" class="form-select">
                                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available for Booking</option>
                                <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable / In Service</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Vehicle Image Upload</label>
                            <input type="file" name="image" id="car_image_input" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept=".jpg,.jpeg,.png,.webp,.svg">
                            <div class="form-text small">Accepted formats: JPG, PNG, WEBP, SVG (Max 4MB)</div>
                            <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
                            
                            <!-- Live Image Preview -->
                            <div class="mt-2 text-center">
                                <img id="car_image_preview" src="" alt="Preview" class="d-none img-thumbnail" style="max-height: 120px;">
                            </div>
                        </div>
                    </div>

                    <!-- Textual Descriptions & Amenities -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-card-text text-primary me-2"></i> Features & Description</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Features (Comma-separated)</label>
                            <input type="text" name="features" class="form-control" placeholder="Air Conditioning, Bluetooth, GPS Navigation, Sunroof, Reverse Camera" value="<?= htmlspecialchars($features) ?>">
                            <div class="form-text small">List key highlights separated by commas for customer display.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Vehicle Description</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Describe the vehicle condition, driving experience, and key recommendations..."><?= htmlspecialchars($description) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= base_url('admin/cars.php') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Add Vehicle to Fleet
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
