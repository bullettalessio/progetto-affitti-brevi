<?php
session_start();
include_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ruolo = $_SESSION['ruolo'] ?? 'ospite';

try {
    // Se l'utente è host, mostra le prenotazioni dei suoi immobili, altrimenti le sue prenotazioni personali
    if ($ruolo === 'host') {
        $stmt = $conn->prepare("
            SELECT p.*, i.titolo, l.nome AS localita_nome, u.nome AS utente_nome 
            FROM prenotazioni p 
            JOIN immobili i ON p.immobile_id = i.id 
            JOIN localita l ON i.localita_id = l.id 
            JOIN utenti u ON p.utente_id = u.id 
            WHERE i.utente_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $conn->prepare("
            SELECT p.*, i.titolo, l.nome AS localita_nome 
            FROM prenotazioni p 
            JOIN immobili i ON p.immobile_id = i.id 
            JOIN localita l ON i.localita_id = l.id 
            WHERE p.utente_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$user_id]);
    }
    $prenotazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $prenotazioni = [];
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">Le tue Prenotazioni</h2>

    <?php if (empty($prenotazioni)): ?>
        <div class="alert alert-info">Non ci sono prenotazioni registrate al momento.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle bg-white shadow-sm rounded">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Immobile</th>
                        <th>Località</th>
                        <?php if ($ruolo === 'host'): ?><th>Ospite</th><?php endif; ?>
                        <th>Data Inizio</th>
                        <th>Data Fine</th>
                        <th>Totale</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prenotazioni as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($p['titolo']) ?></td>
                            <td><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($p['localita_nome']) ?></td>
                            <?php if ($ruolo === 'host'): ?><td><?= htmlspecialchars($p['utente_nome']) ?></td><?php endif; ?>
                            <td><?= htmlspecialchars($p['data_inizio']) ?></td>
                            <td><?= htmlspecialchars($p['data_fine']) ?></td>
                            <td class="text-success fw-bold">€<?= number_format($p['prezzo_totale'], 2) ?></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($p['stato'] ?? 'confermata') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Torna alla Home</a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
