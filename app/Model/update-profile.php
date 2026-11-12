<?php
session_start();
require_once '../inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $notification_enabled = isset($_POST['notification_enabled']) ? 1 : 0;
    
    try {
        // 1. Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Cet email est déjà utilisé par un autre compte.";
            header("Location: /profile.php");
            exit;
        }

        // 2. Récupérer l'utilisateur actuel pour vérifier le mot de passe
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            session_destroy();
            header('Location: /login.php');
            exit;
        }

        // 3. Préparation de la requête SQL
        $sql = "UPDATE users SET email = ?, notification_enabled = ?";
        $params = [$email, $notification_enabled];

        // 4. Gestion du changement de mot de passe
        if (!empty($new_password)) {
            // Validation basique
            if (empty($current_password)) {
                $_SESSION['error'] = "Veuillez entrer votre mot de passe actuel pour le modifier.";
                header("Location: /profile.php");
                exit;
            }
            
            if (!password_verify($current_password, $user['password'])) {
                $_SESSION['error'] = "Le mot de passe actuel est incorrect.";
                header("Location: /profile.php");
                exit;
            }

            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "La confirmation du mot de passe ne correspond pas.";
                header("Location: /profile.php");
                exit;
            }

            if (strlen($new_password) < 8) {
                $_SESSION['error'] = "Le nouveau mot de passe doit faire au moins 8 caractères.";
                header("Location: /profile.php");
                exit;
            }

            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql .= ", password = ?";
            $params[] = $hashed_password;
        }

        $sql .= " WHERE id = ?";
        $params[] = $user_id;

        // 5. Exécution
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Profil mis à jour avec succès !";
        header('Location: /profile.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Une erreur technique est survenue.";
        header('Location: /profile.php');
        exit;
    }
} else {
    header('Location: /profile.php');
    exit;
}
?>