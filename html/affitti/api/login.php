<?php
header('Content-Type: application/json');
include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$azione = $_GET['azione'] ?? '';

if ($method === 'POST' && $azione === 'registra') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    $nome = $data['nome'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $ruolo = $data['ruolo'] ?? 'ospite';

    if (empty($nome) || empty($email) || empty($password)) {
        echo json_encode(["errore" => "Campi obbligatori mancanti"]);
        exit();
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $conn->prepare("INSERT INTO utenti (nome, email, password, ruolo) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $email, $hash, $ruolo])) {
            echo json_encode(["successo" => true, "messaggio" => "Utente registrato con successo"]);
        } else {
            echo json_encode(["errore" => "Errore durante la registrazione"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["errore" => "Email già registrata o errore database"]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $token = bin2hex(random_bytes(32));
        // Salva o restituisce il token
        echo json_encode([
            "successo" => true,
            "token" => $token,
            "utente_id" => $user['id'],
            "nome" => $user['nome'],
            "ruolo" => $user['ruolo']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["errore" => "Credenziali non valide"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["errore" => "Metodo non consentito"]);
}
