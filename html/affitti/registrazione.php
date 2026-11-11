<?php
session_start();
include_once 'includes/api_client.php';

$errore = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = [
        'nome' => $_POST['nome'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'ruolo' => $_POST['ruolo'] ?? 'ospite'
    ];

    $risposta = chiamaApi('register.php', 'POST', $dati);
    
    if ($risposta['code'] === 201 || $risposta['code'] === 200) {
        $successo = "Registrazione completata con successo! Ora puoi effettuare il login.";
    } else {
        $errore = $risposta['data']['errore'] ?? "Errore durante la registrazione.";
    }
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 p-4 bg-white">
            <h2 class="text-center fw-bold mb-4">Registrati</h2>

            <?php if (!empty($errore)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
            <?php endif; ?>

            <?php if (!empty($successo)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($successo) ?> <a href="login.php" class="alert-link">Accedi ora</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome e Cognome</label>
                    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ruolo</label>
                    <select name="ruolo" class="form-select">
                        <option value="ospite">Ospite (Voglio prenotare)</option>
                        <option value="host">Host (Voglio offrire alloggi)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Registrati</button>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
