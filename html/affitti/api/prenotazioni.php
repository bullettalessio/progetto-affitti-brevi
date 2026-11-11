<?php
header('Content-Type: application/json');
include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    $utente_id = $_GET['utente_id'] ?? null;
    if ($utente_id) {
        $stmt = $conn->prepare("SELECT p.*, i.titolo FROM prenotazioni p JOIN immobili i ON p.immobile_id = i.id WHERE p.utente_id = ?");
        $stmt->execute([$utente_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        $stmt = $conn->query("SELECT p.*, i.titolo FROM prenotazioni p JOIN immobili i ON p.immobile_id = i.id");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    $immobile_id = $data['immobile_id'] ?? '';
    $utente_id = $data['utente_id'] ?? '';
    $data_inizio = $data['data_inizio'] ?? '';
    $data_fine = $data['data_fine']グレード ?? '';
    $prezzo_totale = $data['prezzo_totale'] ?? 0;

    try {
        $stmt = $conn->prepare("INSERT INTO prenotazioni (immobile_id, utente_id, data_inizio, data_fine, prezzo_totale, stato) VALUES (?, ?, ?, ?, ?, 'confermata')");
        if ($stmt->execute([$immobile_id, $utente_id, $data_inizio, $data_fine, $prezzo_totale])) {
            echo json_encode(["successo" => true, "id" => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(["errore" => "Errore prenotazione"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["errore" => $e->getMessage()]);
    }
} elseif ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    $stato = $data['stato'] ?? 'confermata';

    $stmt = $conn->prepare("UPDATE prenotazioni SET stato = ? WHERE id = ?");
    if ($stmt->execute([$stato, $id])) {
        echo json_encode(["successo" => true, "messaggio" => "Stato prenotazione aggiornato"]);
    } else {
        http_response_code(500);
        echo json_encode(["errore" => "Errore aggiornamento"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["errore" => "Metodo non consentito"]);
}
