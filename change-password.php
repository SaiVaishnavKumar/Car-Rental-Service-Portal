<?php
/**
 * DriveEasy – Change Account Password
 */
$page_title = "Change Password";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user_session = current_user();

// Fetch password hash from DB
$stmt = $pdo->prepare("SELECT id, password FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_session['id']]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password)) {
        $errors['current_password'] = 'Current password is required.';
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors['current_password'] = 'The current password you entered is incorrect.';
    }

    if (empty($new_password)) {
        $errors['new_password'] = 'New password is required.';
    } elseif (strlen($new_password) < 6) {
        $errors['new_password'] = 'New password must be at least 6 characters.';
    }

    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'New password confirmation does not match.';
    }

    if (empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['id']]);

        set_flash('success', 'Your password has been changed successfully!');
        redirect('profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('profile.php') ?>" class="text-muted">Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <div class="row g-4 justify-content-center">
            <!-- Navigation Sidebar Column -->
            <div class="col-lg-4">
                <div class="list-group shadow-sm rounded-4 overflow-hidden border">
                    <a href="<?= base_url('profile.php') ?>" class="list-group-item list-group-item-action fw-semibold py-3">
                        <i class="bi bi-person-circle me-2"></i> Profile Details
                    </a>
                    <a href="<?= base_url('change-password.php') ?>" class="list-group-item list-group-item-action active fw-semibold py-3">
                        <i class="bi bi-key-fill me-2"></i> Change Password
                    </a>
                    <a href="<?= base_url('my-bookings.php') ?>" class="list-group-item list-group-item-action fw-semibold py-3">
                        <i class="bi bi-calendar2-check-fill me-2"></i> My Bookings
                    </a>
                </div>

                <div class="card card-custom p-4 mt-4 bg-light">
                    <h6 class="fw-bold"><i class="bi bi-shield-check text-success me-1"></i> Security Advice</h6>
                    <p class="small text-muted mb-0">Use at least 6 characters, combining letters and numbers for optimal security. Passwords are encrypted with standard Bcrypt algorithms.</p>
                </div>
            </div>

            <!-- Change Password Form -->
            <div class="col-lg-8">
                <div class="card card-custom p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">Update Password</h3>
                            <p class="text-muted small mb-0">Ensure your account remains safe with an up-to-date password</p>
                        </div>
                    </div>

                    <form action="<?= base_url('change-password.php') ?>" method="POST" class="prevent-double-submit">
                        <!-- Current Password -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="curPassword" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" placeholder="Enter current password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="curPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" name="new_password" id="newPassword" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" placeholder="Min. 6 characters" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="newPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" name="confirm_password" id="confirmNewPassword" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Re-enter new password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="confirmNewPassword" aria-label="Toggle password view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                <i class="bi bi-shield-check me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
