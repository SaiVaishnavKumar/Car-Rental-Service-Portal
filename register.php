<?php
/**
 * DriveEasy – Customer Registration
 */
$page_title = "Create an Account";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect away
require_guest();

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Name must be at least 3 characters.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        // Duplicate check
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email address is already registered. Please sign in instead.';
        }
    }

    if (empty($phone)) {
        $errors['phone'] = 'Contact phone number is required.';
    } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) < 10) {
        $errors['phone'] = 'Please enter a valid 10-digit phone number.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // If valid, create user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (name, email, phone, password, role, status, created_at)
            VALUES (?, ?, ?, ?, 'customer', 'active', NOW())
        ");
        
        try {
            $insertStmt->execute([$name, $email, $phone, $hashed_password]);
            set_flash('success', 'Your DriveEasy account has been created successfully! Please sign in below.');
            redirect('login.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Registration failed due to a database issue. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card card-custom p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3 fs-3">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted small">Join DriveEasy to reserve top cars across India</p>
                    </div>

                    <?php render_flash(); ?>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('register.php') ?>" method="POST" class="prevent-double-submit" novalidate>
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($name) ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="+91 98765 43210" value="<?= htmlspecialchars($phone) ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="regPassword" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Min. 6 characters" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="regPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Re-enter password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="regConfirmPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill shadow-sm">
                            <i class="bi bi-person-check-fill me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            Already have an account? 
                            <a href="<?= base_url('login.php') ?>" class="text-primary fw-bold">Sign In here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
