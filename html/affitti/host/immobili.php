<?php
session_start();
include_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['ruolo'] ?? '') !== 'host') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$messaggio = "";
$errore = "";

// Gestione disattivazione/eliminazione immobile
if (isset($_GET['cancella'])) {
    $immobile_id = $_GET['cancella'];
    try {
        // Verifica che l'immobile appartenga all'host loggato (Regola di business 6)
        $stmtCheck = $conn->prepare("SELECT * FROM immobili WHERE id = ? AND (host_id = ? OR utente_id = ?)");
        $stmtCheck->execute([$immobile_id, $user_id, $user_id]);
        if ($stmtCheck->rowCount() > 0) {
            $stmtDel = $conn->prepare("DELETE FROM immobili WHERE id = ?");
            if ($stmtDel->execute([$immobile_id])) {
                $messaggio = "Immobile disattivato con successo.";
            } else {
                $errore = "Errore durante la disattivazione.";
            }
        } else {
            $errore = "Non sei autorizzato a disattivare questo immobile.";
        }
    } catch (PDOException $e) {
        $errore = "Errore database: " . $e->getMessage();
    }
}

// Recupera i locali dell'host
try {
    $stmt = $conn->prepare("SELECT i.*, l.nome AS localita_nome FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE i.host_id = ? OR i.utente_id = ? ORDER BY i.id DESC");
    $stmt->execute([$user_id, $user_id]);
    $miei_immobili = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $miei_immobili = [];
}
?>

<?php include_once '../includes/header.php'; ?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-houses"></i> Gestione I Miei Locali</h2>
            <a href="../aggiungi_immobile.php" class="btn btn-primary fw-bold">
                <i class="bi bi-plus-lg"></i> Aggiungi Nuovo Immobile
            </a>
        </div>

        <?php if (!empty($messaggio)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($messaggio) ?></div>
        <?php endif; ?>
        <?php if (!empty($errore)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if (empty($miei_immobili)): ?>
            <div class="alert alert-info">Non hai ancora pubblicato alcun immobile.</div>
        <?php else: ?>
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Titolo</th>
                                <th>Località</th>
                                <th>Prezzo Notte</th>
                                <th>Posti Letto</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($miei_immobili as $imm): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($imm['titolo']) ?></td>
                                    <td><?= htmlspecialchars($imm['localita_nome']) ?></td>
                                    <td>€<?= number_format($imm['prezzo_notte'], 2) ?></td>
                                    <td><?= $imm['posti_letto'] ?? 2 ?></td>
                                    <td class="text-end">
                                        <a href="../dettaglio.php?id=<?= $imm['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Visualizza"><i class="bi bi-eye"></i></a>
                                        <a href="immobili.php?cancella=<?= $imm['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Sei sicuro di voler disattivare questo immobile?');" title="Disattiva"><i class="bi bi-trash"></i> Disattiva</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="../index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Torna alla Home</a>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
