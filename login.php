<?php
/**
 * DriveEasy – User Login
 */
$page_title = "Sign In to Your Account";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect
require_guest();

$error = '';
$email = '';
$redirect_url = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect_url = $_POST['redirect'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Your account has been suspended or deactivated. Please contact customer support.';
            } else {
                // Successful login
                login_user($user);
                set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

                // Redirect logic
                if (!empty($redirect_url) && !str_contains($redirect_url, 'logout.php') && !str_contains($redirect_url, 'login.php')) {
                    header("Location: " . $redirect_url);
                    exit;
                }

                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            }
        } else {
            $error = 'Invalid email address or password. Please verify and try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card card-custom p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3 fs-3">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted small">Sign in to manage your car bookings</p>
                    </div>

                    <?php render_flash(); ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login.php') ?>" method="POST" class="prevent-double-submit">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect_url) ?>">

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-semibold small mb-1">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="loginPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill shadow-sm mt-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </button>
                    </form>

                    <!-- Quick Demo Credentials Box for College Viva/Examiner -->
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 mt-4 mb-2 small">
                        <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Demo Credentials (Viva Testing):</div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><strong>Admin:</strong> admin@driveeasy.com</span>
                            <span class="badge bg-secondary">admin123</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Customer:</strong> customer@driveeasy.com</span>
                            <span class="badge bg-secondary">customer123</span>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            Don't have an account yet? 
                            <a href="<?= base_url('register.php') ?>" class="text-primary fw-bold">Sign Up now</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
