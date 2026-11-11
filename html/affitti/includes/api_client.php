<?php
include_once __DIR__ . '/../config/database.php';

function chiamaApi($endpoint, $metodo = 'GET', $dati = null) {
    global $conn;
    
    $parti = explode('?', $endpoint);
    $path = $parti[0];
    
    if (isset($parti[1])) {
        parse_str($parti[1], $_GET);
    }

    ob_start();
    try {
        if ($path === 'localita.php') {
            $stmt = $conn->prepare("SELECT id, nome, regione FROM localita ORDER BY nome ASC");
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($res);
            $code = 200;
        } elseif ($path === 'immobili.php') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $conn->prepare("SELECT i.*, l.nome AS localita_nome, l.regione FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE i.id = ? AND i.attivo = TRUE");
                $stmt->execute([$id]);
                $immobile = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($immobile) {
                    $stmtServ = $conn->prepare("SELECT s.id, s.nome FROM servizi s JOIN immobile_servizio ins ON s.id = ins.servizio_id WHERE ins.immobile_id = ?");
                    $stmtServ->execute([$id]);
                    $immobile['servizi'] = $stmtServ->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($immobile);
                    $code = 200;
                } else {
                    http_response_code(404);
                    echo json_encode(["errore" => "Immobile non trovato."]);
                    $code = 404;
                }
            } else {
                $sql = "SELECT i.*, l.nome AS localita_nome, l.regione FROM immobili i JOIN localita l ON i.localita_id = l.id WHERE i.attivo = TRUE";
                $params = [];
                if (!empty($_GET['localita_id'])) {
                    $sql .= " AND i.localita_id = ?";
                    $params[] = $_GET['localita_id'];
                }
                if (!empty($_GET['prezzo_max'])) {
                    $sql .= " AND i.prezzo_notte <= ?";
                    $params[] = $_GET['prezzo_max'];
                }
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $immobili = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($immobili);
                $code = 200;
            }
        } elseif (($path === 'register.php' || $path === 'registrazione.php') && $metodo === 'POST') {
            $nome = $dati['nome'] ?? '';
            $email = $dati['email'] ?? '';
            $password = $dati['password'] ?? '';
            $ruolo = $dati['ruolo'] ?? 'ospite';

            if (empty($nome) || empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(["errore" => "Tutti i campi sono obbligatori."]);
                $code = 400;
            } else {
                $stmtCheck = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
                $stmtCheck->execute([$email]);
                if ($stmtCheck->fetch()) {
                    http_response_code(400);
                    echo json_encode(["errore" => "Email già registrata."]);
                    $code = 400;
                } else {
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmtIns = $conn->prepare("INSERT INTO utenti (nome, email, password, ruolo) VALUES (?, ?, ?, ?)");
                    if ($stmtIns->execute([$nome, $email, $passwordHash, $ruolo])) {
                        http_response_code(201);
                        echo json_encode(["messaggio" => "Registrazione avvenuta con successo."]);
                        $code = 201;
                    } else {
                        http_response_code(500);
                        echo json_encode(["errore" => "Errore durante il salvataggio."]);
                        $code = 500;
                    }
                }
            }
        } else {
            $url = "http://localhost/affitti/api/" . $endpoint;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($metodo === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dati));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
            $risposta = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            echo $risposta;
            return ['code' => $code, 'data' => json_decode($risposta, true)];
        }
    } catch (Exception $e) {
        $code = 500;
        echo json_encode(["errore" => $e->getMessage()]);
    }

    $output = ob_get_clean();
    return [
        'code' => $code,
        'data' => json_decode($output, true)
    ];
}
?>
