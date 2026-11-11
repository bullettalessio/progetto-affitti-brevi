<?php
include_once 'database.php';

function autenticaUtente($conn, $ruoloRichiesto = null) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["errore" => "Token di autenticazione mancante o non valido."]);
        exit();
    }

    $tokenFornito = $matches[1];
    $tokenHash = hash('sha256', $tokenFornito);

    // Cerca il token valido e non scaduto nel database
    $stmt = $conn->prepare("SELECT t.utente_id, t.scadenza, u.ruolo, u.email, u.nome 
                            FROM token_accesso t 
                            JOIN utenti u ON t.utente_id = u.id 
                            WHERE t.token = :token_hash");
    $stmt->execute([':token_hash' => $tokenHash]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        http_response_code(401);
        echo json_encode(["errore" => "Token non valido."]);
        exit();
    }

    // Controllo scadenza token
    if (strtotime($tokenData['scadenza']) < time()) {
        http_response_code(401);
        echo json_encode(["errore" => "Token scaduto."]);
        exit();
    }

    // Controllo ruolo (se richiesto)
    if ($ruoloRichiesto && $tokenData['ruolo'] !== $ruoloRichiesto) {
        http_response_code(403);
        echo json_encode(["errore" => "Accesso negato: permessi insufficienti."]);
        exit();
    }

    return $tokenData; // Ritorna i dati dell'utente autenticato
}
?>
