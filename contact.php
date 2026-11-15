<?php
/**
 * DriveEasy – Contact Us & Helpdesk Inquiry
 */
$page_title = "Contact Support";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$user = current_user();

$errors = [];
$name    = $user['name'] ?? '';
$email   = $user['email'] ?? '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Your name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Please select or provide a subject.';
    }

    if (empty($message)) {
        $errors['message'] = 'Message body cannot be empty.';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    }

    if (empty($errors)) {
        $insertMsg = $pdo->prepare("
            INSERT INTO contact_messages (user_id, name, email, subject, message, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'unread', NOW())
        ");
        
        try {
            $user_id = $user ? $user['id'] : null;
            $insertMsg->execute([$user_id, $name, $email, $subject, $message]);

            set_flash('success', 'Thank you! Your message has been sent to our customer care team. Ticket reference recorded.');
            // Clear message fields on success
            $subject = '';
            $message = '';
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to submit inquiry due to a database problem. Please try again.';
        }
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
                <li class="breadcrumb-item active" aria-current="page">Contact Support</li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <div class="row g-5">
            <!-- Contact Form Column -->
            <div class="col-lg-7">
                <div class="card card-custom p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3 fs-4">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <div>
                            <h2 class="h3 fw-bold mb-0">Get In Touch</h2>
                            <p class="text-muted small mb-0">Have a question regarding bookings or fleet options? Send us a message.</p>
                        </div>
                    </div>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger mb-3"><?= htmlspecialchars($errors['general']) ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('contact.php') ?>" method="POST" class="prevent-double-submit">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Your Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Subject / Topic <span class="text-danger">*</span></label>
                                <select name="subject" class="form-select <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" required>
                                    <option value="">Select an inquiry category...</option>
                                    <option value="Booking Assistance" <?= $subject === 'Booking Assistance' ? 'selected' : '' ?>>Booking Assistance & Dates</option>
                                    <option value="Payment Inquiry" <?= $subject === 'Payment Inquiry' ? 'selected' : '' ?>>Payment & Refund Inquiry</option>
                                    <option value="Fleet & Commercial Rental" <?= $subject === 'Fleet & Commercial Rental' ? 'selected' : '' ?>>Corporate / Long-term Rentals</option>
                                    <option value="Roadside Support" <?= $subject === 'Roadside Support' ? 'selected' : '' ?>>On-Road Support / Breakdown</option>
                                    <option value="Feedback & Suggestions" <?= $subject === 'Feedback & Suggestions' ? 'selected' : '' ?>>General Feedback / College Viva Inquiry</option>
                                </select>
                                <?php if (isset($errors['subject'])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['subject']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Message -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Your Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="5" class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" placeholder="How can our car rental team help you today?" required><?= htmlspecialchars($message) ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['message']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-pill shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Submit Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information & Hubs Column -->
            <div class="col-lg-5">
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-headset text-primary me-2"></i> Quick Support Channels</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Telephone Support</div>
                                <div class="text-muted small">+91 98765 43210 (Toll-Free 24/7)</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Support Email</div>
                                <div class="text-muted small">support@driveeasy.com / info@driveeasy.com</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Central Dispatch Hub</div>
                                <div class="text-muted small">DriveEasy Tower, Sector 18, Cyber City, Airport Expressway, Delhi-NCR, India</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Working Hours</div>
                                <div class="text-muted small">Hub Operations: 24 Hours, 7 Days a week</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- FAQ Accordion -->
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-question-circle-fill text-primary me-2"></i> Frequently Asked Questions</h5>
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What documents are required to rent?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-muted small">
                                    You only need an original Indian/International Driving License and a valid Government photo ID (Aadhaar or Passport) during vehicle pickup.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I cancel my booking later?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-muted small">
                                    Yes! You can cancel any pending or confirmed booking at zero charge before your scheduled pickup date directly via your "My Bookings" dashboard.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Is there a security deposit?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 text-muted small">
                                    DriveEasy offers zero refundable security deposit on all verified customer accounts! Pay only your daily rental fee.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
