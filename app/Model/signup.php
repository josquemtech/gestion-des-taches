<?php
session_start();
// Si déjà connecté, on renvoie à l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

require_once '../inc/DB_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation basique
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Tous les champs sont requis";
        header('Location: /signup.php');
        exit;
    } elseif (strlen($username) < 3) {
        $_SESSION['error'] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
        header('Location: /signup.php');
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse email invalide";
        header('Location: /signup.php');
        exit;
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
        header('Location: /signup.php');
        exit;
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
        header('Location: /signup.php');
        exit;
    } else {
        try {
            // Vérifier si l'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Ce nom d'utilisateur ou email est déjà utilisé";
                header('Location: /signup.php');
                exit;
            }

            // Création du compte
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $hashedPassword, $email]);
            
            $_SESSION['success'] = "Inscription réussie ! Connectez-vous maintenant.";
            header('Location: /login.php');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur technique lors de l'inscription.";
            header('Location: /signup.php');
            exit;
        }
    }
} else {
    // Accès direct interdit
    header('Location: /signup.php');
    exit;
}
?>