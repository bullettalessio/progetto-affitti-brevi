<?php
session_start();
include_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$immobile_id = $_GET['immobile_id'] ?? null;
$messaggio = "";
$errore = "";

if (!$immobile_id) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT i.*, l.nome AS localita_nome FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE i.id = ?");
$stmt->execute([$immobile_id]);
$immobile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$immobile) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_inizio = $_POST['data_inizio'] ?? '';
    $data_fine = $_POST['data_fine'] ?? '';
    $ospiti = (int)($_POST['ospiti'] ?? 1);
    $user_id = $_SESSION['user_id'];
    $posti_letto_max = $immobile['posti_letto'] ?? 10;

    // Regola 1 & 2: Controllo date nel passato e fine successiva a inizio
    if (empty($data_inizio) || empty($data_fine)) {
        $errore = "Seleziona entrambe le date.";
    } elseif ($data_inizio < date('Y-m-d')) {
        $errore = "Non è possibile effettuare prenotazioni nel passato.";
    } elseif ($data_inizio >= $data_fine) {
        $errore = "La data di fine (partenza) deve essere successiva a quella di inizio (arrivo).";
    } elseif ($ospiti > $posti_letto_max) {
        // Regola 4: Il numero di ospiti non può superare i posti letto
        $errore = "Il numero di ospiti inserito supera i posti letto disponibili ($posti_letto_max).";
    } else {
        // Regola 3: Controllo sovrapposizione prenotazioni (partenza può coincidere con arrivo successivo)
        $stmtOv = $conn->prepare("
            SELECT COUNT(*) FROM prenotazioni 
            WHERE immobile_id = ? 
            AND stato != 'annullata' 
            AND NOT (data_fine <= ? OR data_inizio >= ?)
        ");
        $stmtOv->execute([$immobile_id, $data_inizio, $data_fine]);
        $sovrapposte = $stmtOv->fetchColumn();

        if ($sovrapposte > 0) {
            $errore = "L'alloggio risulta già prenotato per il periodo selezionato.";
        } else {
            // Regola 5: Calcolo server-side del prezzo totale
            $giorni = (strtotime($data_fine) - strtotime($data_inizio)) / (60 * 60 * 24);
            $giorni = max(1, (int)$giorni);
            $prezzo_totale = $giorni * $immobile['prezzo_notte'];

            try {
                $stmtIns = $conn->prepare("INSERT INTO prenotazioni (immobile_id, utente_id, data_inizio, data_fine, prezzo_totale, stato) VALUES (?, ?, ?, ?, ?, 'confermata')");
                if ($stmtIns->execute([$immobile_id, $user_id, $data_inizio, $data_fine, $prezzo_totale])) {
                    $messaggio = "Prenotazione effettuata con successo!";
                } else {
                    $errore = "Errore durante il salvataggio della prenotazione.";
                }
            } catch (PDOException $e) {
                $errore = "Errore database: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 p-4 bg-white">
            <h2 class="fw-bold mb-3">Conferma Prenotazione</h2>
            <h4 class="text-primary mb-2"><?= htmlspecialchars($immobile['titolo']) ?></h4>
            <p class="text-muted mb-4"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($immobile['localita_nome']) ?> - <strong>€<?= number_format($immobile['prezzo_notte'], 2) ?></strong> a notte (Max <?= $immobile['posti_letto'] ?? 2 ?> ospiti)</p>

            <?php if (!empty($messaggio)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($messaggio) ?><br>
                    <a href="index.php" class="btn btn-sm btn-success mt-2">Torna alla Home</a>
                </div>
            <?php else: ?>

                <?php if (!empty($errore)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data Inizio (Arrivo)</label>
                        <input type="date" name="data_inizio" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data Fine (Partenza)</label>
                        <input type="date" name="data_fine" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Numero Ospiti</label>
                        <input type="number" name="ospiti" class="form-control" value="1" min="1" max="<?= $immobile['posti_letto'] ?? 10 ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Conferma e Prenota</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
