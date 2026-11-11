<?php
header('Content-Type: application/json');
include_once '../config/database.php';

try {
    $stmt = $conn->query("SELECT * FROM localita ORDER BY nome ASC");
    $localita = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($localita);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["errore" => $e->getMessage()]);
}
