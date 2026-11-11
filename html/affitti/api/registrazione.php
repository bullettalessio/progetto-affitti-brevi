<?php
include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["errore" => "Metodo non consentito."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nome) && !empty($data->email) && !empty($data->password) && !empty($data->ruolo)) {
    
    if (!in_array($data->ruolo, ['host', 'ospite'])) {
        http_response_code(400);
        echo json_encode(["errore" => "Ruolo non valido (usare 'host' o 'ospite')."]);
        exit();
    }

    // Cifratura sicura della password con password_hash()
    $passwordHash = password_hash($data->password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO utenti (nome, email, password, ruolo) VALUES (:nome, :email, :password, :ruolo)");
        if ($stmt->execute([
            ':nome' => $data->nome,
            ':email' => $data->email,
            ':password' => $passwordHash,
            ':ruolo' => $data->ruolo
        ])) {
            http_response_code(201);
            echo json_encode(["messaggio" => "Registrazione completata con successo."]);
        }
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(["errore" => "Email già registrata."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["errore" => "Dati incompleti."]);
}
?>
