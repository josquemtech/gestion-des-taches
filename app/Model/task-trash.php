<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /my_task.php');
    exit;
}

$taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$userId = (int) $_SESSION['user_id'];
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');

if (!$taskId || !in_array($action, ['trash', 'restore', 'delete_private', 'delete_global'], true)) {
    $_SESSION['error'] = "Action de corbeille invalide.";
    header('Location: /my_task.php');
    exit;
}

$accessStmt = $pdo->prepare(
    "SELECT id, created_by, user_id FROM tasks
     WHERE id = :id AND (
        assigned_to = :user_id OR assigned_to IS NULL OR user_id = :owned_id OR created_by = :creator_id
     )"
);
$accessStmt->execute([
    'id' => $taskId,
    'user_id' => $userId,
    'owned_id' => $userId,
    'creator_id' => $userId
]);
$task = $accessStmt->fetch();
if (!$task) {
    $_SESSION['error'] = "Cette tâche ne vous appartient pas.";
    header('Location: /my_task.php');
    exit;
}

if ($action === 'trash') {
    if (!$isAdmin && (int) $task['created_by'] !== $userId && (int) $task['user_id'] !== $userId) {
        $_SESSION['error'] = "Vous ne pouvez pas déplacer cette tâche dans la corbeille.";
        header('Location: /trash.php');
        exit;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO task_trash (task_id, user_id) VALUES (:task_id, :user_id)
         ON DUPLICATE KEY UPDATE
            deleted_at = CURRENT_TIMESTAMP,
            permanently_deleted = 0"
    );
    $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
    $_SESSION['success'] = "Tâche déplacée dans votre corbeille privée.";
} elseif ($action === 'restore') {
    $stmt = $pdo->prepare(
        "DELETE FROM task_trash
         WHERE task_id = :task_id AND user_id = :user_id"
    );
    $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
    $_SESSION['success'] = "Tâche restaurée.";
} elseif ($action === 'delete_private') {
    $stmt = $pdo->prepare(
        "UPDATE task_trash
         SET permanently_deleted = 1, deleted_at = CURRENT_TIMESTAMP
         WHERE task_id = :task_id AND user_id = :user_id"
    );
    $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
    $_SESSION['success'] = "Tâche retirée définitivement de votre espace. Elle reste visible ailleurs si elle est globale.";
} elseif ($action === 'delete_global') {
    if (!$isAdmin || (int) $task['created_by'] !== $userId) {
        $_SESSION['error'] = "Seul l’administrateur créateur peut supprimer définitivement une tâche.";
        header('Location: /trash.php');
        exit;
    }
    $pdo->prepare("DELETE FROM tasks WHERE id = :id")->execute(['id' => $taskId]);
    $_SESSION['success'] = "Tâche supprimée définitivement.";
}

header('Location: /trash.php');
exit;
