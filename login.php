<?php 
require_once 'app/inc/DB_connection.php';
session_start();

// Redirection si déjà connecté
if(isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if(empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        // Vérification des identifiants
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : '/app/img/user.svg';
            
            header('Location: /index.php');
            exit();
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    }
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

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
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

        .login-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .login-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }

        .login-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 0.95rem;
        }

        .login-body {
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

        .password-wrapper > .form-control {
            padding-right: 3.25rem;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-textfield-decoration-container,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .signup-link a:hover {
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

        /* Password toggle */
        .password-wrapper {
            position: relative;
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

        @media (max-width: 576px) {
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <i class="bi bi-check2-square"></i>
                <h2>Connexion</h2>
                <p>Accédez à votre espace personnel</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <?php if($error): ?>
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success alert-custom">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
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
                               required>
                        <label for="password">
                            <i class="bi bi-lock me-2"></i>Mot de passe
                        </label>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Afficher le mot de passe" aria-controls="password" id="togglePasswordButton">
                            <i class="bi bi-eye" id="toggleIcon" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Remember me & Forgot password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-secondary" for="remember" style="font-size: 0.9rem;">
                                Se souvenir de moi
                            </label>
                        </div>
                        <a href="/forgot_password.php" class="text-decoration-none" style="font-size: 0.9rem; color: var(--primary-color);">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>

                <div class="divider">
                    <span>ou</span>
                </div>

                <div class="signup-link">
                    Pas encore de compte ? 
                    <a href="/signup.php">Créer un compte</a>
                </div>
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleButton = document.getElementById('togglePasswordButton');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
                toggleButton.setAttribute('aria-label', 'Masquer le mot de passe');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
                toggleButton.setAttribute('aria-label', 'Afficher le mot de passe');
            }
        }

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>