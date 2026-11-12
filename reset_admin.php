<?php
session_start();
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .reset-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; max-width: 450px; width: 100%; animation: slideUp 0.5s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .reset-header { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); padding: 2rem; text-align: center; color: white; }
        .reset-body { padding: 2.5rem; }
        .btn-reset { background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%); border: none; border-radius: 12px; padding: 0.875rem; font-weight: 600; width: 100%; }
        .form-control { border-radius: 12px; padding: 0.875rem; border: 2px solid #e2e8f0; }
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
    <div class="reset-card">
        <div class="reset-header">
            <i class="bi bi-shield-lock fs-1 mb-2"></i>
            <h3 class="fw-bold mb-0">Nouveau mot de passe</h3>
        </div>
        <div class="reset-body">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger rounded-3"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <p class="text-muted text-center mb-4">Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>
            
            <form method="POST" action="/app/Model/reset_password.php?token=<?= $token ?>">
                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary btn-reset">
                    <i class="bi bi-check-lg me-2"></i>Réinitialiser
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>