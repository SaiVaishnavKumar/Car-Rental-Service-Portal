<?php
/**
 * DriveEasy – Admin Dashboard
 */
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Enforce admin permission
require_admin();

// Fetch Real-time Statistics from MySQL
$stat_total_cars       = (int)$pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$stat_available_cars   = (int)$pdo->query("SELECT COUNT(*) FROM cars WHERE availability = 'available'")->fetchColumn();
$stat_total_customers  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$stat_total_bookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$stat_pending_bookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'")->fetchColumn();
$stat_active_rentals   = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Active'")->fetchColumn();
$stat_completed_rentals= (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Completed'")->fetchColumn();
$stat_cancelled        = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Cancelled'")->fetchColumn();
$stat_total_revenue    = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Paid'")->fetchColumn();
$stat_unread_messages  = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();

// Fetch Recent Bookings
$recent_stmt = $pdo->query("
    SELECT b.*, c.brand, c.model, c.image, u.name AS customer_name, u.email AS customer_email
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.id DESC
    LIMIT 6
");
$recent_bookings = $recent_stmt->fetchAll();

// Fetch Category Distribution for Quick Analytics
$cat_distribution = $pdo->query("
    SELECT cat.name, COUNT(c.id) as car_count 
    FROM categories cat 
    LEFT JOIN cars c ON cat.id = c.category_id 
    GROUP BY cat.id 
    ORDER BY car_count DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Admin Main Body -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggleBtn" aria-label="Toggle navigation sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h4 class="mb-0 fw-bold d-none d-sm-block">System Overview</h4>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2" id="themeToggleBtn" title="Toggle theme">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>

                <a href="<?= base_url('admin/messages.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill position-relative px-3">
                    <i class="bi bi-chat-left-text me-1"></i> Messages
                    <?php if ($stat_unread_messages > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?= $stat_unread_messages ?></span>
                    <?php endif; ?>
                </a>

                <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Car
                </a>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="admin-content">
            <?php render_flash(); ?>

            <!-- Welcome Header -->
            <div class="mb-4">
                <h1 class="h3 fw-bold mb-1">Administrative Dashboard</h1>
                <p class="text-muted small mb-0">Live portal telemetry, fleet status, and rental operations</p>
            </div>

            <!-- Statistics Grid (Section 32 Requirements) -->
            <div class="row g-3 mb-4">
                <!-- Stat 1: Total Revenue -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="stat-card border-primary border-opacity-25">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Total Revenue</div>
                            <div class="stat-value text-primary"><?= format_currency($stat_total_revenue) ?></div>
                            <div class="text-success small"><i class="bi bi-graph-up-arrow me-1"></i> From Paid Rentals</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Total Bookings -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="stat-card border-success border-opacity-25">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Total Bookings</div>
                            <div class="stat-value"><?= $stat_total_bookings ?></div>
                            <div class="text-muted small">
                                <span class="text-warning fw-bold"><?= $stat_pending_bookings ?></span> Pending Approval
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Total Cars & Availability -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="stat-card border-info border-opacity-25">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Fleet Size</div>
                            <div class="stat-value"><?= $stat_total_cars ?> Cars</div>
                            <div class="text-success small">
                                <strong><?= $stat_available_cars ?></strong> Ready for Handover
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Customers -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="stat-card border-warning border-opacity-25">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Registered Users</div>
                            <div class="stat-value"><?= $stat_total_customers ?></div>
                            <div class="text-muted small">Active Customer Accounts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Status Badges Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-body rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <div class="small text-muted">Pending Bookings</div>
                            <div class="fs-4 fw-bold text-warning"><?= $stat_pending_bookings ?></div>
                        </div>
                        <i class="bi bi-hourglass-split fs-2 text-warning opacity-75"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-body rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <div class="small text-muted">Active Rentals (On Road)</div>
                            <div class="fs-4 fw-bold text-info"><?= $stat_active_rentals ?></div>
                        </div>
                        <i class="bi bi-signpost-split-fill fs-2 text-info opacity-75"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-body rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <div class="small text-muted">Completed Rentals</div>
                            <div class="fs-4 fw-bold text-success"><?= $stat_completed_rentals ?></div>
                        </div>
                        <i class="bi bi-check2-all fs-2 text-success opacity-75"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-body rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <div class="small text-muted">Cancelled Reservations</div>
                            <div class="fs-4 fw-bold text-danger"><?= $stat_cancelled ?></div>
                        </div>
                        <i class="bi bi-x-circle-fill fs-2 text-danger opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table & Fleet Category Breakdown -->
            <div class="row g-4">
                <!-- Recent Bookings (8 Cols) -->
                <div class="col-lg-8">
                    <div class="card card-custom p-4 border-0 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i> Recent Bookings</h5>
                            <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                View All (<?= $stat_total_bookings ?>) <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <?php if (empty($recent_bookings)): ?>
                            <div class="p-4 text-center text-muted">No bookings recorded yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Vehicle</th>
                                            <th>Dates</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $rb): ?>
                                            <tr>
                                                <td><span class="fw-bold">#<?= $rb['id'] ?></span></td>
                                                <td>
                                                    <div class="fw-semibold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($rb['customer_name']) ?></div>
                                                    <div class="text-muted small text-truncate" style="max-width: 140px;"><?= htmlspecialchars($rb['customer_email']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($rb['brand'] . ' ' . $rb['model']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="small"><?= format_date($rb['pickup_date'], 'd M') ?> &rarr; <?= format_date($rb['return_date'], 'd M') ?></div>
                                                    <span class="badge bg-light text-muted border"><?= $rb['number_of_days'] ?>d</span>
                                                </td>
                                                <td class="fw-bold"><?= format_currency($rb['total_amount']) ?></td>
                                                <td><?= get_booking_status_badge($rb['booking_status']) ?></td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('admin/bookings.php?search=' . $rb['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5">
                                                        Manage
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Category Breakdown & Quick Actions (4 Cols) -->
                <div class="col-lg-4">
                    <!-- Category Breakdown -->
                    <div class="card card-custom p-4 border-0 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i> Fleet Categories</h5>
                            <a href="<?= base_url('admin/categories.php') ?>" class="text-primary small text-decoration-none">Manage</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cat_distribution as $cat): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill"><?= $cat['car_count'] ?> Cars</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Quick Admin Controls -->
                    <div class="card card-custom p-4 border-0 shadow-sm bg-primary text-white">
                        <h5 class="fw-bold text-white mb-2"><i class="bi bi-lightning-charge-fill me-1"></i> Quick Admin Shortcuts</h5>
                        <p class="small text-white-50 mb-3">Jump directly into everyday rental operations.</p>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-light rounded-pill text-primary fw-bold">
                                <i class="bi bi-plus-circle me-1"></i> Add New Vehicle
                            </a>
                            <a href="<?= base_url('admin/bookings.php?status=Pending') ?>" class="btn btn-outline-light rounded-pill">
                                <i class="bi bi-clock-history me-1"></i> Review Pending Bookings (<?= $stat_pending_bookings ?>)
                            </a>
                            <a href="<?= base_url('admin/users.php') ?>" class="btn btn-outline-light rounded-pill">
                                <i class="bi bi-people me-1"></i> Customer Accounts
                            </a>
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
