<?php
session_start();
include_once 'config/database.php';

$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, nome, email, ruolo FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['ruolo'] = $user['ruolo'] ?? 'ospite';
                
                header("Location: index.php");
                exit();
            } else {
                $errore = "Email non registrata.";
            }
        } catch (PDOException $e) {
            $errore = "Errore di connessione al database.";
        }
    } else {
        $errore = "Compila tutti i campi.";
    }
}
?>

<?php include_once 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 p-4 bg-white">
            <h2 class="text-center fw-bold mb-4">Accedi</h2>

            <?php if (!empty($errore)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold py-2">Accedi</button>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
