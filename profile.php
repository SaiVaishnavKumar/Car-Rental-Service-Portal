<?php
/**
 * DriveEasy – Customer Profile Management
 */
$page_title = "My Profile";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user_session = current_user();

// Fetch latest user info from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_session['id']]);
$user = $stmt->fetch();

if (!$user) {
    logout_user();
    redirect('login.php');
}

$errors = [];
$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

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
        // If email was changed, check if new email belongs to someone else
        if (strtolower($email) !== strtolower($user['email'])) {
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkEmail->execute([$email, $user['id']]);
            if ($checkEmail->fetch()) {
                $errors['email'] = 'This email is already in use by another account.';
            }
        }
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $updateStmt->execute([$name, $email, $phone, $user['id']]);

        // Update active session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;

        set_flash('success', 'Your profile information has been updated successfully!');
        redirect('profile.php');
    }
}

// Fetch booking statistics for profile display
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
        SUM(CASE WHEN booking_status IN ('Pending', 'Confirmed', 'Active') THEN 1 ELSE 0 END) as active_bookings
    FROM bookings 
    WHERE user_id = ?
");
$statsStmt->execute([$user['id']]);
$userStats = $statsStmt->fetch();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-muted">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profile</li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <div class="row g-4 justify-content-center">
            <!-- Left Column: User Summary Card -->
            <div class="col-lg-4">
                <div class="card card-custom p-4 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 800;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                    <div>
                        <span class="badge bg-primary-subtle text-primary text-uppercase px-3 py-1 rounded-pill fw-bold">
                            <?= htmlspecialchars($user['role']) ?>
                        </span>
                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill ms-1">
                            <?= ucfirst($user['status']) ?>
                        </span>
                    </div>

                    <hr class="my-4">

                    <!-- User Stats -->
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 bg-body-tertiary rounded-3">
                                <div class="fw-bold fs-5 text-dark"><?= $userStats['total_bookings'] ?: 0 ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Total Trips</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-body-tertiary rounded-3">
                                <div class="fw-bold fs-5 text-primary"><?= $userStats['active_bookings'] ?: 0 ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Active</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-body-tertiary rounded-3">
                                <div class="fw-bold fs-5 text-success"><?= $userStats['completed_bookings'] ?: 0 ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Completed</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small mt-4 pt-2 border-top">
                        Member since: <strong><?= format_date($user['created_at'], 'd M Y') ?></strong>
                    </div>
                </div>

                <!-- Navigation List -->
                <div class="list-group shadow-sm rounded-4 overflow-hidden border">
                    <a href="<?= base_url('profile.php') ?>" class="list-group-item list-group-item-action active fw-semibold py-3">
                        <i class="bi bi-person-circle me-2"></i> Profile Details
                    </a>
                    <a href="<?= base_url('change-password.php') ?>" class="list-group-item list-group-item-action fw-semibold py-3">
                        <i class="bi bi-key-fill me-2"></i> Change Password
                    </a>
                    <a href="<?= base_url('my-bookings.php') ?>" class="list-group-item list-group-item-action fw-semibold py-3">
                        <i class="bi bi-calendar2-check-fill me-2"></i> My Bookings
                    </a>
                </div>
            </div>

            <!-- Right Column: Edit Profile Form -->
            <div class="col-lg-8">
                <div class="card card-custom p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">Personal Profile Details</h3>
                            <p class="text-muted small mb-0">Update your contact information for booking confirmations</p>
                        </div>
                    </div>

                    <form action="<?= base_url('profile.php') ?>" method="POST" class="prevent-double-submit">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($name) ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($phone) ?>" required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text small">Reservation receipts and verification codes will be sent to this email.</div>
                            </div>

                            <!-- Readonly System Fields (Demonstrating Security) -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Customer Account ID (Immutable)</label>
                                <input type="text" class="form-control bg-light" value="#<?= $user['id'] ?>" readonly disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Account Role (Immutable)</label>
                                <input type="text" class="form-control bg-light text-capitalize" value="<?= htmlspecialchars($user['role']) ?>" readonly disabled>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
