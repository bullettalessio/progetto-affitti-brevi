<?php
session_start();
include_once 'api_client.php';

$localita_list = callAPI('GET', 'http://localhost/affitti/api/localita.php');
$immobili = callAPI('GET', 'http://localhost/affitti/api/immobili.php');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>AffittiBrevi - Umbria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-blue: #0f172a; --accent-orange: #f97316; }
        body { background-color: #f8fafc; color: #334155; }
        .navbar-custom { background-color: var(--primary-blue) !important; border-bottom: 3px solid var(--accent-orange); }
        .btn-primary { background-color: var(--accent-orange) !important; border-color: var(--accent-orange) !important; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.08); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="index.php">🔥 AffittiBrevi <small class="fs-6 text-muted">Umbria</small></a>
        <div class="ms-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-light me-3"><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Esci</a>
            <?php else: ?>
                <a href="../login.php" class="btn btn-primary btn-sm fw-bold">Accedi</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container py-5">
    <h2 class="fw-bold mb-4">Alloggi Disponibili (Ordinati per Prezzo)</h2>
    <div class="row">
        <?php if (!empty($immobili) && is_array($immobili)): ?>
            <?php foreach ($immobili as $imm): ?>
                <div class="col-md-4 mb-4">
                    <div class="card p-3 bg-white">
                        <h4 class="fw-bold text-primary"><?= htmlspecialchars($imm['titolo']) ?></h4>
                        <p class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($imm['localita_nome']) ?></p>
                        <h5 class="text-success fw-bold">€<?= number_format($imm['prezzo_notte'], 2) ?> <small class="text-muted fw-normal">a notte</small></h5>
                        <a href="immobile.php?id=<?= $imm['id'] ?>" class="btn btn-outline-primary btn-sm mt-2">Visualizza Dettaglio</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info">Nessun immobile trovato.</div></div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
