<?php
session_start();
include_once 'config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT i.*, l.nome AS localita_nome 
        FROM immobili i 
        JOIN localita l ON i.localita_id = l.id 
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $immobile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $immobile = false;
}

if (!$immobile) {
    include_once 'includes/header.php';
    echo '<div class="container mt-5"><div class="alert alert-danger">Immobile non trovato.</div><a href="index.php" class="btn btn-secondary">Torna alla Home</a></div>';
    include_once 'includes/footer.php';
    exit();
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-4 bg-white rounded">
            <h2 class="fw-bold text-primary mb-2"><?= htmlspecialchars($immobile['titolo']) ?></h2>
            <p class="text-muted mb-3">
                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($immobile['localita_nome']) ?> 
                <?php if (!empty($immobile['indirizzo'])): ?>
                    - <small><?= htmlspecialchars($immobile['indirizzo']) ?></small>
                <?php endif; ?>
            </p>

            <div class="mb-4">
                <span class="fs-3 fw-bold text-success">€<?= number_format($immobile['prezzo_notte'], 2) ?></span> 
                <span class="text-muted">a notte</span>
                <?php if (!empty($immobile['posti_letto'])): ?>
                    <span class="badge bg-secondary ms-3"><i class="bi bi-people"></i> <?= $immobile['posti_letto'] ?> Posti Letto</span>
                <?php endif; ?>
            </div>

            <hr>

            <h5 class="fw-bold mb-2">Descrizione e Servizi</h5>
            <p class="text-secondary mb-4" style="white-space: pre-line;"><?= htmlspecialchars($immobile['descrizione']) ?></p>

            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Torna alla Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="prenota.php?immobile_id=<?= $immobile['id'] ?>" class="btn btn-primary px-4 fw-bold">Prenota Subito</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary px-4">Accedi per Prenotare</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
