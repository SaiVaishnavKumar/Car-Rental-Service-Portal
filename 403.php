<?php
$page_title = "403 – Access Denied";
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> – DriveEasy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                    <div class="mb-3">
                        <div class="display-1 fw-black text-danger">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h1 class="h2 fw-bold text-dark mt-3">403 – Access Denied</h1>
                        <p class="text-muted mt-2">
                            You do not have administrative privileges to access this page. Please sign in with an administrator account or return to the main portal.
                        </p>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= base_url('index.php') ?>" class="btn btn-primary btn-lg rounded-pill">
                            <i class="bi bi-house-door me-2"></i> Return to Homepage
                        </a>
                        <a href="<?= base_url('admin/login.php') ?>" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi bi-person-badge me-2"></i> Admin Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
