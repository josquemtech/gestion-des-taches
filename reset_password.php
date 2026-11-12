<?php
session_start();
require_once '../inc/DB_connection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: /login.php');
    exit;
}

// Vérification rapide du token avant d'afficher le formulaire
try {
    $stmt = $pdo->prepare("SELECT id, token_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user || strtotime($user['token_expiry']) < time()) {
        $_SESSION['error'] = "Lien invalide ou expiré.";
        header('Location: /forgot_password.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
    } elseif (strlen($new_password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            
            $_SESSION['success'] = "Mot de passe réinitialisé avec succès !";
            header('Location: /login.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour.";
        }
    }
}

// Si on arrive ici, c'est qu'on doit afficher le formulaire
header("Location: /reset_admin.php?token=$token");
exit;
?>