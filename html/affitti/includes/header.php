<?php
// Determina se siamo nella root o in una sottocartella (es. host/)
$is_subfolder = file_exists('../config/database.php') || strpos($_SERVER['PHP_SELF'], '/host/') !== false;
$base_path = $is_subfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AffittiBrevi - Umbria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="<?= $base_path ?>index.php">
            <span style="color: #f97316;">🔥</span> AffittiBrevi <small class="fs-6 text-muted fw-normal">Umbria</small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>index.php"><i class="bi bi-house"></i> Home</a></li>
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['ruolo'] ?? '') === 'host'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>host/immobili.php"><i class="bi bi-houses"></i> I Miei Locali</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['ruolo'] ?? '') === 'host'): ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-primary btn-sm fw-bold px-3" href="<?= $base_path ?>aggiungi_immobile.php">
                                <i class="bi bi-plus-lg"></i> Aggiungi Immobile
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-light fw-semibold">
                            <?= htmlspecialchars($_SESSION['nome'] ?? 'Utente') ?> 
                            <span class="badge bg-warning text-dark ms-1"><?= htmlspecialchars($_SESSION['ruolo'] ?? 'ospite') ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3" href="<?= $base_path ?>logout.php">Esci</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>login.php">Accedi</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-primary btn-sm px-3 fw-bold" href="<?= $base_path ?>register.php">Registrati</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
