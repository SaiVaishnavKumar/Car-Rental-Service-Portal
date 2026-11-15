<?php
/**
 * DriveEasy – Admin Customer Account Management
 */
$page_title = "Manage Customers";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

// Handle Status Toggle (Activate / Deactivate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_user_status') {
    $target_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($target_id) {
        // Prevent deactivating own admin account or another admin through this screen
        $chk = $pdo->prepare("SELECT role, status, name FROM users WHERE id = ?");
        $chk->execute([$target_id]);
        $targetUser = $chk->fetch();

        if ($targetUser && $targetUser['role'] === 'customer') {
            $new_status = ($targetUser['status'] === 'active') ? 'inactive' : 'active';
            $up = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $up->execute([$new_status, $target_id]);
            set_flash('success', "Customer \"{$targetUser['name']}\" account is now " . ucfirst($new_status) . ".");
        } else {
            set_flash('danger', 'Administrative accounts cannot be modified from the customer panel.');
        }
    }
    redirect('admin/users.php');
}

// Search
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
           COUNT(b.id) AS total_bookings,
           COALESCE(SUM(b.total_amount), 0) AS total_spend
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    WHERE u.role = 'customer'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " GROUP BY u.id ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

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
                <h4 class="mb-0 fw-bold">Customer Accounts Management</h4>
            </div>

            <div class="text-muted small">
                Registered Customers: <strong><?= count($customers) ?></strong>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <!-- Search Card -->
            <div class="card card-custom p-3 mb-4">
                <form action="<?= base_url('admin/users.php') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6 col-sm-8">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search customer name, email address, phone..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Search</button>
                        <?php if ($search): ?>
                            <a href="<?= base_url('admin/users.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="card card-custom overflow-hidden border-0 shadow-sm">
                <?php if (empty($customers)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-people display-4 mb-2 d-block text-secondary"></i>
                        <h5>No customers found</h5>
                        <p class="small mb-0">No registered customer accounts matched your search.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer Name</th>
                                    <th>Email & Phone</th>
                                    <th>Total Rentals</th>
                                    <th>Total Spent</th>
                                    <th>Member Since</th>
                                    <th>Account Status</th>
                                    <th class="text-end">Status Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td><span class="fw-bold">#<?= $c['id'] ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                                </div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><i class="bi bi-envelope me-1 text-muted"></i> <?= htmlspecialchars($c['email']) ?></div>
                                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($c['phone'] ?: 'N/A') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                                <?= $c['total_bookings'] ?> Bookings
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary"><?= format_currency($c['total_spend']) ?></span>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?= format_date($c['created_at'], 'd M Y') ?></span>
                                        </td>
                                        <td>
                                            <?php if ($c['status'] === 'active'): ?>
                                                <span class="badge badge-soft-success px-2.5 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-danger px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Suspended</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form action="<?= base_url('admin/users.php') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_user_status">
                                                <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                                <?php if ($c['status'] === 'active'): ?>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 confirm-cancel" data-confirm-msg="Are you sure you want to suspend <?= htmlspecialchars($c['name']) ?>'s account?">
                                                        <i class="bi bi-person-x"></i> Suspend
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                                        <i class="bi bi-person-check"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
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
