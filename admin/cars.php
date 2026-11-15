<?php
/**
 * DriveEasy – Admin Cars Management
 */
$page_title = "Manage Fleet";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

// Quick Availability Toggle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_availability') {
    $toggle_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
    if ($toggle_id) {
        $curStmt = $pdo->prepare("SELECT availability FROM cars WHERE id = ?");
        $curStmt->execute([$toggle_id]);
        $cur = $curStmt->fetchColumn();

        if ($cur !== false) {
            $new_avail = (strtolower($cur) === 'available') ? 'unavailable' : 'available';
            $updateStmt = $pdo->prepare("UPDATE cars SET availability = ? WHERE id = ?");
            $updateStmt->execute([$new_avail, $toggle_id]);
            set_flash('success', "Vehicle status changed to: " . ucfirst($new_avail));
        }
    }
    redirect('admin/cars.php');
}

// Search & Filter Parameters
$search       = trim($_GET['search'] ?? '');
$category_id  = trim($_GET['category'] ?? '');
$availability = trim($_GET['availability'] ?? '');

$sql = "
    SELECT c.*, cat.name AS category_name,
           (SELECT COUNT(*) FROM bookings WHERE car_id = c.id) AS total_bookings_count
    FROM cars c
    JOIN categories cat ON c.category_id = cat.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.brand LIKE :search OR c.model LIKE :search OR c.registration_number LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category_id)) {
    $sql .= " AND c.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if (!empty($availability)) {
    $sql .= " AND c.availability = :availability";
    $params[':availability'] = $availability;
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

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
                <h4 class="mb-0 fw-bold">Fleet Management</h4>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add New Vehicle
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <!-- Header and Filter Bar -->
            <div class="card card-custom p-3 mb-4">
                <form action="<?= base_url('admin/cars.php') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search brand, model, plate..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="availability" class="form-select form-select-sm">
                            <option value="">All Availability</option>
                            <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                            Filter
                        </button>
                        <?php if ($search || $category_id || $availability): ?>
                            <a href="<?= base_url('admin/cars.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Clear">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Cars Table -->
            <div class="card card-custom overflow-hidden border-0 shadow-sm">
                <div class="card-header bg-body d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="bi bi-car-front-fill text-primary me-2"></i> All Vehicles (<?= count($cars) ?>)</span>
                    <span class="text-muted small">Real-time database records</span>
                </div>

                <?php if (empty($cars)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-car-front display-4 mb-2 d-block text-secondary"></i>
                        <h5>No vehicles found</h5>
                        <p class="small mb-3">No cars match your search criteria.</p>
                        <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-primary btn-sm rounded-pill">Add First Car</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Brand & Model</th>
                                    <th>Category</th>
                                    <th>Plate / Reg</th>
                                    <th>Daily Rate</th>
                                    <th>Specs</th>
                                    <th>Availability</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cars as $car): ?>
                                    <tr>
                                        <td style="width: 80px;">
                                            <img src="<?= get_car_image_url($car['image']) ?>" alt="<?= htmlspecialchars($car['brand']) ?>" class="rounded-2 border" style="width: 70px; height: 46px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                                            <div class="text-muted small">Year: <?= htmlspecialchars($car['year']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($car['category_name']) ?></span>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($car['registration_number']) ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary"><?= format_currency($car['price_per_day']) ?></span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($car['fuel_type']) ?> &bull; <?= htmlspecialchars($car['transmission']) ?> &bull; <?= $car['seats'] ?> Seats
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Toggle Form -->
                                            <form action="<?= base_url('admin/cars.php') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle availability">
                                                    <?= get_car_availability_badge($car['availability']) ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Preview Public Page">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/edit-car.php?id=' . $car['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="Edit Car">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="<?= base_url('admin/delete-car.php?id=' . $car['id']) ?>" class="btn btn-outline-danger btn-sm rounded-pill px-2 confirm-delete" data-confirm-msg="Are you sure you want to delete <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>? Deletion cannot be undone." title="Delete Car">
                                                    <i class="bi bi-trash"></i>
                                                </a>
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
