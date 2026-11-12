<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) { header('Location: /login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    try {
        if (!$taskId) throw new InvalidArgumentException('Tâche invalide.');
        $userId = (int) $_SESSION['user_id'];
        $isAdmin = (($_SESSION['role'] ?? '') === 'admin');

        if (!$isAdmin) {
            $check = $pdo->prepare(
                "SELECT id FROM tasks
                 WHERE id = :id AND (created_by = :user_id OR user_id = :owned_id)"
            );
            $check->execute(['id' => $taskId, 'user_id' => $userId, 'owned_id' => $userId]);
            if (!$check->fetch()) {
                throw new InvalidArgumentException('Vous ne pouvez supprimer que les tâches que vous avez créées.');
            }
        }

        $stmt = $pdo->prepare(
            "INSERT INTO task_trash (task_id, user_id, permanently_deleted)
             VALUES (:task_id, :user_id, 0)
             ON DUPLICATE KEY UPDATE
               deleted_at = CURRENT_TIMESTAMP,
               permanently_deleted = 0"
        );
        $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        $_SESSION['success'] = "Tâche déplacée dans votre corbeille privée.";
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression.";
    }
    
    header("Location: /trash.php"); exit;
}
header("Location: /my_task.php"); exit;
?>