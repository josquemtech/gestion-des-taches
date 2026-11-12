<?php
session_start();
require_once 'app/inc/DB_connection.php';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            // En mode dev, on affiche le lien. En prod, on utiliserait mailer.php
            $resetLink = "http://localhost/gestion_des_taches/reset_password.php?token=$token";
            $success = "Lien de réinitialisation généré (Mode Dev) : <br><a href='$resetLink'>$resetLink</a>";
        } else {
            // Sécurité : ne pas dire si l'email existe ou non en prod
            $success = "Si un compte existe avec cet email, un lien a été envoyé.";
        }
    } else {
        $error = "Veuillez entrer une adresse email valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .recovery-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; max-width: 450px; width: 100%; animation: slideUp 0.5s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .card-header-custom { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); padding: 2rem; text-align: center; color: white; }
        .card-body-custom { padding: 2.5rem; }
        .btn-recover { background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%); border: none; border-radius: 12px; padding: 0.875rem; font-weight: 600; width: 100%; }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="card-header-custom">
            <i class="bi bi-shield-lock fs-1 mb-2"></i>
            <h3 class="fw-bold mb-0">Récupération</h3>
        </div>
        <div class="card-body-custom">
            <p class="text-muted text-center mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            
            <?php if($error): ?>
                <div class="alert alert-danger rounded-3"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success rounded-3"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="nom@exemple.com">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-recover mb-3">
                    Envoyer le lien
                </button>
                <div class="text-center">
                    <a href="/login.php" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i>Retour à la connexion</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>