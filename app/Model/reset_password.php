<?php
session_start();
require_once '../inc/DB_connection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        header("Location: /reset_password_view.php?token=$token"); // Redirection vers la vue formulaire
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
        header("Location: /reset_password_view.php?token=$token");
        exit;
    }

    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
        header("Location: /reset_password_view.php?token=$token");
        exit;
    }

    try {
        // Vérifier le token en base de données
        $stmt = $pdo->prepare("SELECT id, token_expiry FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = "Lien de réinitialisation invalide.";
            header('Location: /forgot_password.php');
            exit;
        }

        // Vérifier l'expiration (1 heure)
        if (strtotime($user['token_expiry']) < time()) {
            $_SESSION['error'] = "Le lien a expiré. Veuillez en demander un nouveau.";
            header('Location: /forgot_password.php');
            exit;
        }

        // Mise à jour du mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);

        $_SESSION['success'] = "Mot de passe modifié avec succès ! Vous pouvez vous connecter.";
        header('Location: /login.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Une erreur est survenue.";
        header("Location: /reset_password_view.php?token=$token");
        exit;
    }
} else {
    // Si accès direct sans POST, on redirige vers la vue qui contient le formulaire
    header("Location: /reset_password_view.php?token=$token");
    exit;
}
?>