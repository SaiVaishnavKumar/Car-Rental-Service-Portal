<?php
/**
 * DriveEasy – Dedicated Admin Login
 */
$page_title = "Admin Sign In";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already admin, redirect to admin dashboard
if (is_admin()) {
    redirect('admin/dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter administrative email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $error = 'Access restricted: This account does not possess administrative privileges.';
            } elseif ($user['status'] !== 'active') {
                $error = 'This administrative account is deactivated.';
            } else {
                login_user($user);
                set_flash('success', 'Welcome to the DriveEasy Administration Console.');
                redirect('admin/dashboard.php');
            }
        } else {
            $error = 'Invalid administrative credentials. Please verify and try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> – DriveEasy</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset_url('images/logo.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 480px;">
        <div class="text-center mb-4">
            <a href="<?= base_url('index.php') ?>">
                <img src="<?= asset_url('images/logo.svg') ?>" alt="DriveEasy" height="48" class="bg-white p-2 rounded-3 shadow">
            </a>
            <div class="mt-3 text-white-50 small">Administrative Control Center</div>
        </div>

        <div class="card card-custom p-4 p-md-5 border-0 shadow-lg">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-2 fs-3">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="fw-bold">Admin Portal</h3>
                <p class="text-muted small">Sign in to manage fleet, bookings & customers</p>
            </div>

            <?php render_flash(); ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/login.php') ?>" method="POST" class="prevent-double-submit">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Admin Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@driveeasy.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="adminLoginPass" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="adminLoginPass" aria-label="Toggle password view">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Access Admin Console
                </button>
            </form>

            <!-- Quick Demo Credentials Box for College Viva/Examiner -->
            <div class="alert alert-secondary border-0 mt-4 mb-2 small text-dark">
                <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill text-primary me-1"></i> Default Admin Credentials:</div>
                <div class="d-flex justify-content-between">
                    <span><strong>Email:</strong> admin@driveeasy.com</span>
                    <span class="badge bg-dark">admin123</span>
                </div>
            </div>

            <div class="text-center mt-3 pt-2">
                <a href="<?= base_url('index.php') ?>" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Return to Public Site
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
