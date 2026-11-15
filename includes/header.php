<?php
/**
 * DriveEasy – Common HTML Header Include
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$page_title = isset($page_title) ? $page_title . ' – DriveEasy' : 'DriveEasy – Car Rental Service Portal';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Favicon / Logo -->
    <link rel="icon" type="image/svg+xml" href="<?= asset_url('images/logo.svg') ?>">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">

    <!-- Inline script to set theme before paint to prevent flicker -->
    <script>
        (function() {
            var theme = localStorage.getItem('driveeasy_theme') || 
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body>
