<?php
session_start();
include_once 'config/database.php';

$localita_id = $_GET['localita_id'] ?? '';
$prezzo_max = $_GET['prezzo_max'] ?? '';

try {
    $query = "SELECT i.*, l.nome AS localita_nome FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE 1=1";
    $params = [];

    if (!empty($localita_id)) {
        $query .= " AND i.localita_id = ?";
        $params[] = $localita_id;
    }

    if (!empty($prezzo_max)) {
        $query .= " AND i.prezzo_notte <= ?";
        $params[] = $prezzo_max;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $immobili = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtLoc = $conn->query("SELECT * FROM localita");
    $localita_list = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);

    // Recupera le prenotazioni se l'utente è loggato
    $prenotazioni = [];
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $ruolo = $_SESSION['ruolo'] ?? 'ospite';
        
        if ($ruolo === 'host') {
            $stmtP = $conn->prepare("
                SELECT p.*, i.titolo, l.nome AS localita_nome, u.nome AS utente_nome 
                FROM prenotazioni p 
                JOIN immobili i ON p.immobile_id = i.id 
                JOIN localita l ON i.localita_id = l.id 
                JOIN utenti u ON p.utente_id = u.id 
                ORDER BY p.id DESC
            ");
            $stmtP->execute();
        } else {
            $stmtP = $conn->prepare("
                SELECT p.*, i.titolo, l.nome AS localita_nome 
                FROM prenotazioni p 
                JOIN immobili i ON p.immobile_id = i.id 
                JOIN localita l ON i.localita_id = l.id 
                WHERE p.utente_id = ?
                ORDER BY p.id DESC
            ");
            $stmtP->execute([$user_id]);
        }
        $prenotazioni = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $immobili = [];
    $localita_list = [];
    $prenotazioni = [];
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Trova il tuo alloggio ideale</h1>
        <p class="text-muted">Le migliori soluzioni nel territorio umbro</p>
    </div>

    <!-- Filtri di ricerca -->
    <div class="card shadow-sm border-0 p-4 mb-5 bg-white rounded">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Località</label>
                <select name="localita_id" class="form-select">
                    <option value="">Tutte le località</option>
                    <?php foreach ($localita_list as $loc): ?>
                        <option value="<?= $loc['id'] ?>" <?= ($localita_id == $loc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Prezzo Max (€)</label>
                <input type="number" name="prezzo_max" class="form-control" placeholder="Es. 150" value="<?= htmlspecialchars($prezzo_max) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Cerca Alloggio</button>
            </div>
        </form>
    </div>

    <!-- Griglia Immobili -->
    <h3 class="fw-bold mb-4">Alloggi Disponibili</h3>
    <div class="row g-4">
        <?php if (empty($immobili)): ?>
            <div class="col-12">
                <div class="alert alert-warning">Nessun immobile trovato con i filtri selezionati.</div>
            </div>
        <?php else: ?>
            <?php foreach ($immobili as $immobile): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 rounded">
                        <div class="card-body d-flex flex-column">
                            <h4 class="card-title fw-bold text-primary"><?= htmlspecialchars($immobile['titolo']) ?></h4>
                            <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($immobile['localita_nome']) ?></p>
                            <p class="card-text text-secondary small flex-grow-1"><?= htmlspecialchars($immobile['descrizione']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fs-5 fw-bold text-success">€<?= number_format($immobile['prezzo_notte'], 2) ?> <small class="text-muted fw-normal fs-6">/ notte</small></span>
                                <a href="dettaglio.php?id=<?= $immobile['id'] ?>" class="btn btn-outline-primary btn-sm fw-semibold">Visualizza Dettaglio</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sezione Tabella Prenotazioni (Visibile se l'utente ha effettuato l'accesso) -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <hr class="my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Riepilogo Prenotazioni Effettuate</h3>
            <span class="badge bg-success fs-6">Benvenuto, <?= htmlspecialchars($_SESSION['nome'] ?? 'Utente') ?></span>
        </div>

        <?php if (empty($prenotazioni)): ?>
            <div class="alert alert-info">Non ci sono prenotazioni registrate al momento. Effettua una prenotazione cliccando su un immobile per vederla qui!</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle bg-white shadow-sm rounded">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Immobile</th>
                            <th>Località</th>
                            <th>Data Inizio</th>
                            <th>Data Fine</th>
                            <th>Prezzo Complessivo</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prenotazioni as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($p['titolo']) ?></td>
                                <td><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($p['localita_nome']) ?></td>
                                <td><?= htmlspecialchars($p['data_inizio'] ?? $p['arrivo'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['data_fine'] ?? $p['partenza'] ?? '-') ?></td>
                                <td class="text-success fw-bold">€<?= number_format($p['prezzo_totale'] ?? $p['totale'] ?? 0, 2) ?></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($p['stato'] ?? 'confermata') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php include_once 'includes/footer.php'; ?>
