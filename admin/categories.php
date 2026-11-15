<?php
/**
 * DriveEasy – Admin Vehicle Categories Management (CRUD)
 */
$page_title = "Manage Categories";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

$edit_category = null;
$errors = [];

// Handle Edit Mode Fetch
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $st = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $st->execute([$edit_id]);
        $edit_category = $st->fetch();
    }
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_category') {
        $cat_id = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Category name is required.';
        } else {
            // Check duplicate name
            $dupSql = "SELECT id FROM categories WHERE name = ?";
            $dupParams = [$name];
            if ($cat_id) {
                $dupSql .= " AND id != ?";
                $dupParams[] = $cat_id;
            }
            $dupStmt = $pdo->prepare($dupSql);
            $dupStmt->execute($dupParams);
            if ($dupStmt->fetch()) {
                $errors['name'] = 'A category with this name already exists.';
            }
        }

        if (empty($errors)) {
            if ($cat_id) {
                // Update
                $up = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $up->execute([$name, $description, $cat_id]);
                set_flash('success', "Category \"{$name}\" updated successfully.");
            } else {
                // Insert
                $ins = $pdo->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
                $ins->execute([$name, $description]);
                set_flash('success', "New category \"{$name}\" created successfully.");
            }
            redirect('admin/categories.php');
        }
    } elseif ($_POST['action'] === 'delete_category') {
        $del_id = filter_input(INPUT_POST, 'del_id', FILTER_VALIDATE_INT);
        if ($del_id) {
            // Safe Deletion Check: Prevent deletion if cars belong to this category!
            $carCountStmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE category_id = ?");
            $carCountStmt->execute([$del_id]);
            $assigned_cars = (int)$carCountStmt->fetchColumn();

            if ($assigned_cars > 0) {
                set_flash('danger', "Cannot delete this category because {$assigned_cars} vehicle(s) in your fleet are currently assigned to it. Reassign or delete those cars first.");
            } else {
                $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $del->execute([$del_id]);
                set_flash('success', 'Category has been deleted successfully.');
            }
        }
        redirect('admin/categories.php');
    }
}

// Fetch all categories with car count
$categories = $pdo->query("
    SELECT c.*, COUNT(cars.id) AS car_count 
    FROM categories c
    LEFT JOIN cars ON c.id = cars.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

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
                <h4 class="mb-0 fw-bold">Vehicle Categories Management</h4>
            </div>

            <?php if ($edit_category): ?>
                <a href="<?= base_url('admin/categories.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Add Mode
                </a>
            <?php endif; ?>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <div class="row g-4">
                <!-- Add / Edit Category Form (4 Cols) -->
                <div class="col-lg-4">
                    <div class="card card-custom p-4 border-0 shadow-sm sticky-top" style="top: 85px;">
                        <h5 class="fw-bold mb-3">
                            <i class="bi <?= $edit_category ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> text-primary me-2"></i>
                            <?= $edit_category ? 'Edit Category' : 'Create New Category' ?>
                        </h5>

                        <form action="<?= base_url('admin/categories.php') ?>" method="POST" class="prevent-double-submit">
                            <input type="hidden" name="action" value="save_category">
                            <?php if ($edit_category): ?>
                                <input type="hidden" name="cat_id" value="<?= $edit_category['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. Electric, Convertible" value="<?= htmlspecialchars($edit_category['name'] ?? $_POST['name'] ?? '') ?>" required>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Describe the category features, target use case, and styling..."><?= htmlspecialchars($edit_category['description'] ?? $_POST['description'] ?? '') ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> <?= $edit_category ? 'Update Category' : 'Save Category' ?>
                                </button>
                                <?php if ($edit_category): ?>
                                    <a href="<?= base_url('admin/categories.php') ?>" class="btn btn-outline-secondary rounded-pill">Cancel Edit</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories List Table (8 Cols) -->
                <div class="col-lg-8">
                    <div class="card card-custom overflow-hidden border-0 shadow-sm">
                        <div class="card-header bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold"><i class="bi bi-tags-fill text-primary me-2"></i> Existing Categories (<?= count($categories) ?>)</span>
                            <span class="text-muted small">Safe relational constraints enabled</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Assigned Fleet</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($cat['name']) ?></span>
                                            </td>
                                            <td>
                                                <span class="small text-muted text-truncate-2" style="max-width: 320px;">
                                                    <?= htmlspecialchars($cat['description'] ?: 'No description provided.') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">
                                                    <?= $cat['car_count'] ?> Cars
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?= base_url('admin/categories.php?edit=' . $cat['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="<?= base_url('admin/categories.php') ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_category">
                                                        <input type="hidden" name="del_id" value="<?= $cat['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 confirm-delete" data-confirm-msg="Are you sure you want to delete category '<?= htmlspecialchars($cat['name']) ?>'? Deletion is blocked if cars are assigned." title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
