<?php
include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }

    $nomeCognome = trim($data['nome'] ?? '');
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $ruolo = $data['ruolo'] ?? 'ospite';

    if (empty($nomeCognome) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["errore" => "Tutti i campi sono obbligatori."]);
        exit();
    }

    $parti = explode(' ', $nomeCognome, 2);
    $nome = $parti[0];
    $cognome = $parti[1] ?? '';

    try {
        $stmtCheck = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            http_response_code(400);
            echo json_encode(["errore" => "Email già registrata."]);
            exit();
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Verifica se la tabella usa 'password_hash' o 'password'
        $colTest = $conn->query("SHOW COLUMNS FROM utenti LIKE 'password_hash'");
        $targetCol = ($colTest->rowCount() > 0) ? 'password_hash' : 'password';

        $colCognome = $conn->query("SHOW COLUMNS FROM utenti LIKE 'cognome'");
        
        if ($colCognome->rowCount() > 0) {
            $stmtIns = $conn->prepare("INSERT INTO utenti (nome, cognome, email, $targetCol, ruolo) VALUES (?, ?, ?, ?, ?)");
            $eseguito = $stmtIns->execute([$nome, $cognome, $email, $passwordHash, $ruolo]);
        } else {
            $stmtIns = $conn->prepare("INSERT INTO utenti (nome, email, $targetCol, ruolo) VALUES (?, ?, ?, ?)");
            $eseguito = $stmtIns->execute([$nomeCognome, $email, $passwordHash, $ruolo]);
        }

        if ($eseguito) {
            http_response_code(201);
            echo json_encode(["messaggio" => "Registrazione avvenuta con successo."]);
        } else {
            http_response_code(500);
            echo json_encode(["errore" => "Errore durante il salvataggio."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["errore" => "Errore database: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["errore" => "Metodo non consentito."]);
}
?>
