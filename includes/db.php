<?php
/**
 * DriveEasy – Database Connection (PDO)
 * Target: XAMPP Localhost (Apache + MySQL)
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'car_rental');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Graceful error display without exposing sensitive stack traces
    $db_error_message = $e->getMessage();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Notice – DriveEasy</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
            .card-error { background: #1e293b; border: 1px solid #334155; border-radius: 16px; max-width: 650px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        </style>
    </head>
    <body>
        <div class="card card-error p-4 p-md-5">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-danger bg-opacity-25 text-danger p-3 rounded-circle me-3 fs-3">
                    <i class="bi bi-database-exclamation"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-white fw-bold">Database Connection Notice</h3>
                    <p class="text-secondary mb-0">DriveEasy Car Rental Service Portal</p>
                </div>
            </div>
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                <i class="bi bi-info-circle-fill me-2"></i>
                Could not connect to the MySQL database <strong><?= htmlspecialchars(DB_NAME) ?></strong>.
            </div>
            <h5 class="text-white mt-3">Quick Setup in XAMPP:</h5>
            <ol class="text-light-emphasis small ps-3">
                <li class="mb-2">Open the <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to <strong>Apache</strong> and <strong>MySQL</strong>.</li>
                <li class="mb-2">Open your browser and navigate to <a href="http://localhost/phpmyadmin/" target="_blank" class="text-info">http://localhost/phpmyadmin/</a>.</li>
                <li class="mb-2">Click <strong>New</strong> and create a database named <code>car_rental</code>.</li>
                <li class="mb-2">Click the <strong>Import</strong> tab, select <code>database/car_rental.sql</code> from this project folder, and click <strong>Go</strong>.</li>
                <li>Refresh this page!</li>
            </ol>
            <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                <span class="text-secondary small">Error: <?= htmlspecialchars($db_error_message) ?></span>
                <button onclick="window.location.reload();" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
