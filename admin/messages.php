<?php
/**
 * DriveEasy – Admin Contact Inquiries & Support Messages
 */
$page_title = "Support Messages";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

require_admin();

// Handle Status Toggle (Mark Read / Unread)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_read') {
        $msg_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
        if ($msg_id) {
            $cur = $pdo->prepare("SELECT status FROM contact_messages WHERE id = ?");
            $cur->execute([$msg_id]);
            $st = $cur->fetchColumn();
            if ($st) {
                $new_st = ($st === 'unread') ? 'read' : 'unread';
                $up = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
                $up->execute([$new_st, $msg_id]);
                set_flash('success', "Message #{$msg_id} marked as {$new_st}.");
            }
        }
    } elseif ($_POST['action'] === 'delete_message') {
        $msg_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
        if ($msg_id) {
            $del = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $del->execute([$msg_id]);
            set_flash('success', "Message #{$msg_id} has been deleted.");
        }
    }
    redirect('admin/messages.php');
}

// Filter
$status_filter = trim($_GET['status'] ?? '');

$sql = "SELECT m.*, u.role AS user_role 
        FROM contact_messages m 
        LEFT JOIN users u ON m.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND m.status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

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
                <h4 class="mb-0 fw-bold">Customer Inquiries & Messages</h4>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= base_url('admin/messages.php') ?>" class="btn btn-sm <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
                    All
                </a>
                <a href="<?= base_url('admin/messages.php?status=unread') ?>" class="btn btn-sm <?= $status_filter === 'unread' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">
                    Unread
                </a>
                <a href="<?= base_url('admin/messages.php?status=read') ?>" class="btn btn-sm <?= $status_filter === 'read' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">
                    Read
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php render_flash(); ?>

            <div class="card card-custom overflow-hidden border-0 shadow-sm">
                <?php if (empty($messages)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-chat-left-dots display-4 mb-2 d-block text-secondary"></i>
                        <h5>No messages found</h5>
                        <p class="small mb-0">Customer helpdesk inquiries will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Sender Info</th>
                                    <th>Subject & Message</th>
                                    <th>Received Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $m): ?>
                                    <tr class="<?= $m['status'] === 'unread' ? 'table-warning bg-opacity-25' : '' ?>">
                                        <td style="width: 100px;">
                                            <?php if ($m['status'] === 'unread'): ?>
                                                <span class="badge bg-danger rounded-pill px-2.5 py-1">Unread</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">Read</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($m['name']) ?></div>
                                            <div class="small text-muted"><a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($m['email']) ?></a></div>
                                            <?php if ($m['user_id']): ?>
                                                <span class="badge bg-info-subtle text-info small">Registered User #<?= $m['user_id'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border small">Guest Visitor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($m['subject']) ?></div>
                                            <div class="small text-secondary" style="max-width: 480px; white-space: pre-wrap;"><?= htmlspecialchars($m['message']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><?= format_date($m['created_at'], 'd M Y') ?></div>
                                            <div class="small text-muted"><?= format_date($m['created_at'], 'h:i A') ?></div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <!-- Toggle Read/Unread -->
                                                <form action="<?= base_url('admin/messages.php') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_read">
                                                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" title="Mark as <?= $m['status'] === 'unread' ? 'Read' : 'Unread' ?>">
                                                        <i class="bi <?= $m['status'] === 'unread' ? 'bi-envelope-open' : 'bi-envelope' ?>"></i>
                                                    </button>
                                                </form>

                                                <!-- Delete -->
                                                <form action="<?= base_url('admin/messages.php') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_message">
                                                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 confirm-delete" data-confirm-msg="Are you sure you want to permanently delete this message?">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/script.js') ?>"></script>
</body>
</html>
