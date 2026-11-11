<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $conn->prepare("SELECT i.*, l.nome AS localita_nome FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE i.id = ?");
        $stmt->execute([$id]);
        $immobile = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($immobile) {
            http_response_code(200);
            echo json_encode($immobile, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(["errore" => "Immobile non trovato"]);
        }
    } else {
        $localita = $_GET['localita'] ?? '';
        $prezzo_min = $_GET['prezzo_min'] ?? '';
        $prezzo_max = $_GET['prezzo_max'] ?? '';
        $ospiti = $_GET['ospiti'] ?? '';

        $query = "SELECT i.*, l.nome AS localita_nome FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE 1=1";
        $params = [];

        if (!empty($localita)) {
            $query .= " AND l.nome LIKE ?";
            $params[] = "%" . $localita . "%";
        }
        if (!empty($prezzo_min)) {
            $query .= " AND i.prezzo_notte >= ?";
            $params[] = $prezzo_min;
        }
        if (!empty($prezzo_max)) {
            $query .= " AND i.prezzo_notte <= ?";
            $params[] = $prezzo_max;
        }
        if (!empty($ospiti)) {
            $query .= " AND i.posti_letto >= ?";
            $params[] = $ospiti;
        }

        $query .= " ORDER BY i.prezzo_notte ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        http_response_code(200);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    $titolo = $data['titolo'] ?? '';
    $descrizione = $data['descrizione'] ?? '';
    $localita_id = $data['localita_id'] ?? '';
    $prezzo_notte = $data['prezzo_notte'] ?? '';
    $host_id = $data['host_id'] ?? 1;

    if (empty($titolo) || empty($localita_id) || empty($prezzo_notte)) {
        http_response_code(400);
        echo json_encode(["errore" => "Campi obbligatori mancanti"]);
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO immobili (titolo, descrizione, localita_id, prezzo_notte, host_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$titolo, $descrizione, $localita_id, $prezzo_notte, $host_id])) {
            http_response_code(201);
            echo json_encode(["successo" => true, "id" => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(["errore" => "Errore inserimento"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["errore" => $e->getMessage()]);
    }
} elseif ($method === 'DELETE' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM immobili WHERE id = ?");
        if ($stmt->execute([$id])) {
            http_response_code(200);
            echo json_encode(["successo" => true, "messaggio" => "Immobile disattivato/eliminato"]);
        } else {
            http_response_code(500);
            echo json_encode(["errore" => "Errore eliminazione"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["errore" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["errore" => "Metodo non consentito"]);
}
