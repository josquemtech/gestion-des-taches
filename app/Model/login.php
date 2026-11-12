<?php
session_start();
require_once 'app/inc/DB_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($identifier) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role, avatar FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : '/app/img/user.svg';
                
                header('Location: /index.php');
                exit();
            } else {
                $error = "Identifiants incorrects";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données";
        }
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .login-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; max-width: 450px; width: 100%; animation: slideUp 0.5s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-header { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); padding: 2.5rem 2rem; text-align: center; color: white; }
        .login-body { padding: 2.5rem 2rem; }
        .btn-login { background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%); border: none; border-radius: 12px; padding: 0.875rem; font-weight: 600; transition: all 0.3s ease; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); }
        .form-control { border-radius: 12px; padding: 0.875rem; border: 2px solid #e2e8f0; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-textfield-decoration-container,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-check2-square fs-1 mb-2"></i>
            <h2 class="fw-bold mb-0">Connexion</h2>
            <p class="mb-0 opacity-75">Accédez à votre espace personnel</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="identifier" class="form-label fw-semibold">Nom d'utilisateur ou email</label>
                    <input type="text" class="form-control" id="identifier" name="identifier" required placeholder="ex: jean_dupont">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </button>
                
                <div class="text-center">
                    <a href="/forgot_password.php" class="text-decoration-none small text-muted">Mot de passe oublié ?</a>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <p class="mb-0 text-muted">Pas encore de compte ? <a href="/signup.php" class="text-decoration-none fw-bold" style="color: var(--primary-color);">Créer un compte</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>