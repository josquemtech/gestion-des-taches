<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/DB_connection.php';

// Seul un admin peut supprimer un utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    die("Accès réservé aux administrateurs");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Empêcher la suppression de son propre compte
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
        header("Location: /user.php");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $_SESSION['success'] = "Utilisateur supprimé avec succès !";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
    
    header("Location: /user.php");
    exit;
}
?>