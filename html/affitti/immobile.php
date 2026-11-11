<?php
session_start();
include_once 'includes/api_client.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

$res = chiamaApi('immobili.php?id=' . $id, 'GET');
$immobile = ($res['code'] === 200) ? $res['data'] : null;

if (!$immobile) {
    include_once 'includes/header.php';
    echo '<div class="container mt-5"><div class="alert alert-danger text-center">Immobile non trovato.</div></div>';
    include_once 'includes/footer.php';
    exit();
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($immobile['titolo']) ?></h1>
            <p class="text-muted fs-5 mb-4">📍 <?= htmlspecialchars($immobile['localita_nome']) ?>, <?= htmlspecialchars($immobile['regione']) ?> - <?= htmlspecialchars($immobile['indirizzo']) ?></p>
            
            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                <h4 class="fw-bold mb-3">Descrizione</h4>
                <p class="text-secondary"><?= htmlspecialchars($immobile['descrizione']) ?></p>
            </div>

            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                <h4 class="fw-bold mb-3">Servizi inclusi</h4>
                <p class="text-secondary">
                    <?php 
                    if (!empty($immobile['servizi'])) {
                        $nomiServizi = array_column($immobile['servizi'], 'nome');
                        $ultimo = array_pop($nomiServizi);
                        $elenco = count($nomiServizi) > 0 ? implode(', ', $nomiServizi) . ' e ' . $ultimo : $ultimo;
                        echo "Questo alloggio è dotato di " . htmlspecialchars(lcfirst($elenco)) . ", offrendo tutto il necessario per garantire un soggiorno confortevole e rilassante nel territorio umbro.";
                    } else {
                        echo "Nessun servizio specifico incluso al momento.";
                    }
                    ?>
                </p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 p-4 sticky-top" style="top: 20px;">
                <h3 class="fw-bold text-success mb-3">€ <?= number_format($immobile['prezzo_notte'], 2, ',', '.') ?> <span class="fs-6 text-muted fw-normal">/ notte</span></h3>
                <p class="text-muted mb-4">Posti letto: <strong><?= htmlspecialchars($immobile['posti_letto']) ?></strong></p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="prenota.php?immobile_id=<?= $immobile['id'] ?>" class="btn btn-primary w-100 fw-bold py-2">Prenota Ora</a>
                <?php else: ?>
                    <div class="alert alert-info text-center py-2 mb-0">
                        <small>Devi effettuare il <a href="login.php">login</a> per prenotare questo immobile.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
