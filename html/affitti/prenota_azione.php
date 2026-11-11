<?php
session_start();
include_once 'includes/api_client.php';

if (!isset($_SESSION['utente']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$dati = [
    "immobile_id" => $_POST['immobile_id'],
    "ospite_id" => $_SESSION['utente']['id'],
    "arrivo" => $_POST['arrivo'],
    "partenza" => $_POST['partenza'],
    "num_ospiti" => (int)$_POST['num_ospiti']
];

$res = chiamaApi('prenotazioni.php', 'POST', $dati);

if ($res['code'] === 201) {
    header("Location: prenotazioni.php?successo=1");
    exit();
} else {
    $errore = $res['data']['errore'] ?? "Errore durante la prenotazione.";
    header("Location: immobile.php?id=" . $_POST['immobile_id'] . "&errore=" . urlencode($errore));
    exit();
}
?>
