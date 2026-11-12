<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    die("Accès réservé aux administrateurs");
}
require_once __DIR__ . '/../inc/DB_connection.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Tous les champs sont requis";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, notification_enabled) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $hashedPassword, $email, $role]);
            $success = "Utilisateur <strong>$username</strong> ajouté avec succès !";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Ce nom d'utilisateur ou email est déjà utilisé";
            } else {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; }
        .admin-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 2.5rem; max-width: 600px; margin: 3rem auto; }
        .btn-submit { background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%); border: none; border-radius: 12px; padding: 0.875rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../inc/nav.php'; ?>

    <div class="container pb-5">
        <div class="admin-card">
            <h2 class="fw-bold mb-4"><i class="bi bi-person-plus me-2 text-primary"></i>Ajouter un utilisateur</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success rounded-3"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Nom d'utilisateur</label>
                    <input type="text" class="form-control rounded-3" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control rounded-3" id="email" name="email" required>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Mot de passe</label>
                        <input type="password" class="form-control rounded-3" id="password" name="password" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label fw-semibold">Rôle</label>
                        <select class="form-select rounded-3" id="role" name="role">
                            <option value="user">Utilisateur standard</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="/user.php" class="btn btn-light rounded-3 flex-grow-1">Annuler</a>
                    <button type="submit" class="btn btn-primary btn-submit flex-grow-1">Ajouter l'utilisateur</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>