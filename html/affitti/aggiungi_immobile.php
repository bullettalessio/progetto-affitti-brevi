<?php
session_start();
include_once 'config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['ruolo'] ?? '') !== 'host') {
    header("Location: index.php");
    exit();
}

$messaggio = "";
$errore = "";

try {
    $stmtLoc = $conn->query("SELECT * FROM localita ORDER BY nome ASC");
    $localita_list = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $localita_list = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $indirizzo = trim($_POST['indirizzo'] ?? 'Via Principale');
    $posti_letto = $_POST['posti_letto'] ?? 2;
    $descrizione = trim($_POST['descrizione'] ?? '');
    $localita_id = $_POST['localita_id'] ?? '';
    $prezzo_notte = $_POST['prezzo_notte'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!empty($titolo) && !empty($descrizione) && !empty($localita_id) && !empty($prezzo_notte)) {
        try {
            $hasHostId = $conn->query("SHOW COLUMNS FROM immobili LIKE 'host_id'")->rowCount() > 0;
            $hasUtenteId = $conn->query("SHOW COLUMNS FROM immobili LIKE 'utente_id'")->rowCount() > 0;
            $hasIndirizzo = $conn->query("SHOW COLUMNS FROM immobili LIKE 'indirizzo'")->rowCount() > 0;
            $hasPostiLetto = $conn->query("SHOW COLUMNS FROM immobili LIKE 'posti_letto'")->rowCount() > 0;

            $columns = ["titolo", "localita_id", "prezzo_notte", "descrizione"];
            $values = [$titolo, $localita_id, $prezzo_notte, $descrizione];

            if ($hasIndirizzo) {
                $columns[] = "indirizzo";
                $values[] = $indirizzo;
            }
            if ($hasPostiLetto) {
                $columns[] = "posti_letto";
                $values[] = $posti_letto;
            }
            if ($hasHostId) {
                $columns[] = "host_id";
                $values[] = $user_id;
            } elseif ($hasUtenteId) {
                $columns[] = "utente_id";
                $values[] = $user_id;
            }

            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $colsSql = implode(', ', $columns);

            $stmt = $conn->prepare("INSERT INTO immobili ($colsSql) VALUES ($placeholders)");
            if ($stmt->execute($values)) {
                $messaggio = "Immobile aggiunto con successo!";
            } else {
                $errore = "Errore durante l'inserimento dell'immobile.";
            }
        } catch (PDOException $e) {
            $errore = "Errore database: " . $e->getMessage();
        }
    } else {
        $errore = "Tutti i campi sono obbligatori.";
    }
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 p-4 bg-white rounded">
            <h2 class="fw-bold mb-4"><i class="bi bi-house-add"></i> Aggiungi Nuovo Immobile</h2>

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
                        <label class="form-label fw-semibold">Titolo dell'alloggio</label>
                        <input type="text" name="titolo" class="form-control" placeholder="Es. Casa Vacanze Assisi Centro" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Indirizzo</label>
                        <input type="text" name="indirizzo" class="form-control" placeholder="Es. Via Roma 15" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Posti Letto</label>
                        <input type="number" name="posti_letto" class="form-control" value="2" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Località</label>
                        <select name="localita_id" class="form-select" required>
                            <option value="">Seleziona una località...</option>
                            <?php foreach ($localita_list as $loc): ?>
                                <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prezzo a Notte (€)</label>
                        <input type="number" step="0.01" name="prezzo_notte" class="form-control" placeholder="Es. 75.00" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descrizione e Servizi</label>
                        <textarea name="descrizione" class="form-control" rows="4" placeholder="Inserisci una descrizione dettagliata dei servizi inclusi (WiFi, Parcheggio, Vista panoramica...)" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">Pubblica Immobile</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
