<?php
session_start();
include_once 'includes/api_client.php';

if (!isset($_SESSION['utente']) || $_SESSION['utente']['ruolo'] !== 'host') {
    header("Location: index.php");
    exit();
}

$hostId = $_SESSION['utente']['id'];

// Azione inserimento nuovo immobile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crea_immobile'])) {
    $nuovoImmobile = [
        "host_id" => $hostId,
        "localita_id" => $_POST['localita_id'],
        "titolo" => $_POST['titolo'],
        "descrizione" => $_POST['descrizione'],
        "prezzo_notte" => $_POST['prezzo_notte'],
        "servizi" => $_POST['servizi'] ?? []
    ];
    chiamaApi('immobili.php', 'POST', $nuovoImmobile);
    header("Location: host_dashboard.php");
    exit();
}

// Azione disattivazione immobile
if (isset($_POST['disattiva_id'])) {
    chiamaApi('immobili.php', 'PATCH', ["id" => $_POST['disattiva_id']]);
    header("Location: host_dashboard.php");
    exit();
}

// Azione conferma prenotazione
if (isset($_POST['conferma_prenotazione_id'])) {
    chiamaApi('prenotazioni.php', 'PATCH', [
        "id" => $_POST['conferma_prenotazione_id'],
        "stato" => "confermata"
    ]);
    header("Location: host_dashboard.php");
    exit();
}

// Recupera dati per la dashboard
$resLoc = chiamaApi('localita.php', 'GET');
$localitaList = ($resLoc['code'] === 200) ? $resLoc['data'] : [];

$resServ = chiamaApi('servizi.php', 'GET'); // Opzionale o query diretta se serve
// Recupera immobili dell'host
$resImm = chiamaApi("immobili.php", 'GET');
$immobiliHost = [];
if ($resImm['code'] === 200) {
    foreach ($resImm['data'] as $imm) {
        if ($imm['host_id'] == $hostId) {
            $immobiliHost[] = $imm;
        }
    }
}

// Recupera prenotazioni ricevute
$resPren = chiamaApi("prenotazioni.php?host_id=$hostId", 'GET');
$prenotazioniRicevute = ($resPren['code'] === 200) ? $resPren['data'] : [];
?>

<?php include_once 'includes/header.php'; ?>

<h1 class="mb-4">Area Host - Dashboard</h1>

<!-- Inserimento Nuovo Immobile -->
<div class="card shadow-sm mb-5 p-4 bg-white">
    <h3 class="mb-3">Pubblica un nuovo immobile</h3>
    <form method="POST" action="host_dashboard.php" class="row g-3">
        <input type="hidden" name="crea_immobile" value="1">
        <div class="col-md-6">
            <label class="form-label">Titolo</label>
            <input type="text" name="titolo" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Località</label>
            <select name="localita_id" class="form-select" required>
                <?php foreach ($localitaList as $loc): ?>
                    <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Prezzo a notte (€)</label>
            <input type="number" step="0.01" name="prezzo_notte" class="form-control" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Descrizione</label>
            <textarea name="descrizione" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Servizi (Seleziona con CTRL)</label>
            <select name="servizi[]" class="form-select" multiple>
                <option value="1">Wi-Fi</option>
                <option value="2">Aria Condizionata</option>
                <option value="3">Cucina</option>
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-success">Pubblica Immobile</button>
        </div>
    </form>
</div>

<!-- I Miei Immobili -->
<h3 class="mb-3">I tuoi immobili pubblicati</h3>
<div class="table-responsive bg-white shadow-sm p-3 rounded mb-5">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Titolo</th>
                <th>Località</th>
                <th>Prezzo</th>
                <th>Stato</th>
                <th>Azione</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($immobiliHost)): ?>
                <tr><td colspan="5" class="text-center text-muted">Non hai ancora pubblicato alcun immobile.</td></tr>
            <?php else: ?>
                <?php foreach ($immobiliHost as $imm): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($imm['titolo']) ?></td>
                        <td><?= htmlspecialchars($imm['localita_nome']) ?></td>
                        <td>€ <?= number_format($imm['prezzo_notte'], 2) ?></td>
                        <td><span class="badge bg-success">Attivo</span></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Sei sicuro di voler disattivare questo immobile?');">
                                <input type="hidden" name="disattiva_id" value="<?= $imm['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm">Disattiva</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Prenotazioni Ricevute -->
<h3 class="mb-3">Prenotazioni ricevute</h3>
<div class="table-responsive bg-white shadow-sm p-3 rounded">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Immobile</th>
                <th>Ospite</th>
                <th>Arrivo</th>
                <th>Partenza</th>
                <th>Totale</th>
                <th>Stato</th>
                <th>Azione</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prenotazioniRicevute)): ?>
                <tr><td colspan="7" class="text-center text-muted">Nessuna prenotazione ricevuta.</td></tr>
            <?php else: ?>
                <?php foreach ($prenotazioniRicevute as $p): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($p['immobile_titolo']) ?></td>
                        <td><?= htmlspecialchars($p['ospite_nome']) ?></td>
                        <td><?= $p['arrivo'] ?></td>
                        <td><?= $p['partenza'] ?></td>
                        <td>€ <?= number_format($p['totale'], 2) ?></td>
                        <td>
                            <?php 
                                $badge = ['in attesa' => 'bg-warning text-dark', 'confermata' => 'bg-success', 'annullata' => 'bg-danger'];
                                $st = $p['stato'];
                                echo "<span class='badge {$badge[$st]}'>$st</span>";
                            ?>
                        </td>
                        <td>
                            <?php if ($p['stato'] === 'in attesa'): ?>
                                <form method="POST" onsubmit="return confirm('Confermi questa prenotazione?');">
                                    <input type="hidden" name="conferma_prenotazione_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Conferma</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once 'includes/footer.php'; ?>
