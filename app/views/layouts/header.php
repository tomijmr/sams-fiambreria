<?php $isAuth = Auth::check(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">SAMS - Kiosko</a>
        <?php if ($isAuth): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard">Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/sales/pos">Ventas Rapidas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/sales/history">Historial Ventas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/purchases/manual">Compras</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/reports/daily-cash">Caja Diaria</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products">Stock</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/suppliers">Proveedores</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expenses">Gastos</a></li>
                </ul>
                <div class="d-flex align-items-center text-white gap-2">
                    <span class="small">Hola, <?= htmlspecialchars(Auth::userName()) ?></span>
                    <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/logout">Salir</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>
<main class="container py-4">
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
