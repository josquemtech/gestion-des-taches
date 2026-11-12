<?php
session_start();
require_once 'app/inc/DB_connection.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Génération d'un token sécurisé
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Sauvegarde en base de données
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
                $stmt->execute([$token, $expiry, $user['id']]);
                
                // Lien de réinitialisation (Mode Dev : affiché à l'écran)
                $resetLink = "http://localhost/gestion_des_taches/reset_password.php?token=$token";
                
                $success = "✅ Demande reçue !<br><br>
                <strong>Mode Développement :</strong> Copiez ce lien pour continuer :<br>
                <div class='input-group mt-2'>
                    <input type='text' class='form-control bg-light' value='$resetLink' readonly onclick='this.select()'>
                    <button class='btn btn-outline-secondary' onclick='navigator.clipboard.writeText(\"$resetLink\")'><i class='bi bi-clipboard'></i></button>
                </div>
                <small class='text-muted mt-2 d-block'>ℹ️ En production, ce lien serait envoyé par email.</small>";
            } else {
                // Sécurité : on ne dit pas si l'email existe ou non en prod, mais ici on reste simple
                $error = "Aucun compte n'est associé à cette adresse email.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
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
        body { 
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 2rem; 
        }
        .recovery-card { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); 
            overflow: hidden; 
            max-width: 500px; 
            width: 100%; 
            animation: slideUp 0.5s ease; 
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .recovery-header { 
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); 
            padding: 2.5rem 2rem; 
            text-align: center; 
            color: white; 
        }
        .recovery-body { padding: 2.5rem 2rem; }
        .btn-recover { 
            background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%); 
            border: none; 
            border-radius: 12px; 
            padding: 0.875rem; 
            font-weight: 600; 
            transition: all 0.3s ease; 
        }
        .btn-recover:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); }
        .back-link { color: var(--primary-color); text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem; }
        .back-link:hover { color: #4338ca; }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="recovery-header">
            <i class="bi bi-shield-lock fs-1 mb-2"></i>
            <h2 class="fw-bold mb-0">Récupération</h2>
            <p class="mb-0 opacity-75">Réinitialisez votre mot de passe</p>
        </div>
        
        <div class="recovery-body">
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success rounded-3"><?= $success ?></div>
            <?php else: ?>
                <p class="text-muted text-center mb-4">
                    Entrez votre adresse email ci-dessous. Nous vous enverrons un lien sécurisé pour créer un nouveau mot de passe.
                </p>
                
                <form method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Adresse email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0 rounded-end-3" id="email" name="email" required placeholder="nom@exemple.com">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-recover w-100 mb-3">
                        <i class="bi bi-send me-2"></i>Envoyer le lien
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="/login.php" class="back-link">
                    <i class="bi bi-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>