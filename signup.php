<?php 
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

// Correction du chemin - ajustez selon votre structure
require_once __DIR__ . '/app/inc/DB_connection.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tous les champs sont requis";
    } elseif (strlen($username) < 3) {
        $error = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                $error = "Ce nom d'utilisateur ou email est déjà utilisé";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, notification_enabled) VALUES (?, ?, ?, 1)");
                $stmt->execute([$username, $hashedPassword, $email]);
                
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .signup-container {
            width: 100%;
            max-width: 500px;
        }

        .signup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .signup-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .signup-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .signup-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }

        .signup-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 0.95rem;
        }

        .signup-body {
            padding: 2.5rem 2rem;
        }

        .form-floating {
            margin-bottom: 1.25rem;
        }

        .form-floating > .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
            color: #64748b;
        }

        .btn-signup {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .login-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 2rem;
        }

        .back-home a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-home a:hover {
            color: white;
            transform: translateX(-4px);
        }

        .password-wrapper {
            position: relative;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-textfield-decoration-container,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0.25rem;
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .form-text {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        @media (max-width: 576px) {
            .signup-header {
                padding: 2rem 1.5rem;
            }
            
            .signup-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <!-- Header -->
            <div class="signup-header">
                <i class="bi bi-person-plus"></i>
                <h2>Inscription</h2>
                <p>Créez votre compte gratuitement</p>
            </div>

            <!-- Body -->
            <div class="signup-body">
                <?php if($error): ?>
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success alert-custom">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        <div class="mt-2">
                            <a href="/login.php" class="btn btn-sm btn-success">Se connecter maintenant</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!$success): ?>
                <form method="POST" action="">
                    <!-- Username -->
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Nom d'utilisateur"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               pattern="[a-zA-Z0-9_]{3,}"
                               required>
                        <label for="username">
                            <i class="bi bi-person me-2"></i>Nom d'utilisateur
                        </label>
                    </div>
                    <small class="form-text d-block mb-3">3 caractères minimum, lettres, chiffres et underscores uniquement</small>

                    <!-- Email -->
                    <div class="form-floating">
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="nom@exemple.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                        <label for="email">
                            <i class="bi bi-envelope me-2"></i>Adresse email
                        </label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating password-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Mot de passe"
                               minlength="8"
                               required>
                        <label for="password">
                            <i class="bi bi-lock me-2"></i>Mot de passe
                        </label>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', 'icon1')">
                            <i class="bi bi-eye" id="icon1"></i>
                        </button>
                    </div>
                    <small class="form-text d-block mb-3">Doit contenir au moins 8 caractères</small>

                    <!-- Confirm Password -->
                    <div class="form-floating password-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Confirmer le mot de passe"
                               required>
                        <label for="confirm_password">
                            <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
                        </label>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', 'icon2')">
                            <i class="bi bi-eye" id="icon2"></i>
                        </button>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary btn-signup w-100 mt-3">
                        <i class="bi bi-person-plus me-2"></i>Créer mon compte
                    </button>
                </form>

                <div class="login-link">
                    Vous avez déjà un compte ? 
                    <a href="/login.php">Se connecter</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back to home -->
        <div class="back-home">
            <a href="/">
                <i class="bi bi-arrow-left"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(fieldId, iconId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            const usernameField = document.getElementById('username');
            if (usernameField && !document.querySelector('.alert-success')) {
                usernameField.focus();
            }
        });
    </script>
</body>
</html>