<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: /task.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['task_id'], $_POST['action'])) {
    header('Location: /my_task.php');
    exit;
}

$taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
$action = $_POST['action'];
$allowedActions = ['edit', 'delete'];
if (!$taskId || !in_array($action, $allowedActions, true)) {
    $_SESSION['error'] = "Demande invalide.";
    header('Location: /my_task.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$check = $pdo->prepare(
    "SELECT id FROM tasks
     WHERE id = :id AND (assigned_to = :user_id OR assigned_to IS NULL OR user_id = :owned_id)"
);
$check->execute(['id' => $taskId, 'user_id' => $userId, 'owned_id' => $userId]);
if (!$check->fetch()) {
    $_SESSION['error'] = "Vous ne pouvez pas demander une modification de cette tâche.";
    header('Location: /my_task.php');
    exit;
}

$pending = $pdo->prepare(
    "SELECT id FROM task_change_requests
     WHERE task_id = :task_id AND user_id = :user_id AND status = 'pending'"
);
$pending->execute(['task_id' => $taskId, 'user_id' => $userId]);
if ($pending->fetch()) {
    $_SESSION['error'] = "Une demande est déjà en attente pour cette tâche.";
    header('Location: /my_task.php');
    exit;
}

$title = $action === 'edit' ? trim($_POST['title'] ?? '') : null;
$description = $action === 'edit' ? trim($_POST['description'] ?? '') : null;
$dueDate = $action === 'edit' ? ($_POST['due_date'] ?: null) : null;
$status = $action === 'edit' ? ($_POST['status'] ?? '') : null;
if ($action === 'edit' && $title === '') {
    $_SESSION['error'] = "Le titre est requis.";
    header('Location: /my_task.php');
    exit;
}
if (!in_array($status, ['pending', 'in_progress', 'completed'], true) && $action === 'edit') {
    $status = null;
}
$reason = trim($_POST['reason'] ?? '');

$stmt = $pdo->prepare(
    "INSERT INTO task_change_requests
     (task_id, user_id, action, proposed_title, proposed_description, proposed_due_date, proposed_status, reason, status)
     VALUES (:task_id, :user_id, :action, :title, :description, :due_date, :status, :reason, 'pending')"
);
$stmt->execute([
    'task_id' => $taskId,
    'user_id' => $userId,
    'action' => $action,
    'title' => $title,
    'description' => $description,
    'due_date' => $dueDate,
    'status' => $status,
    'reason' => $reason ?: null
]);
$requestId = (int) $pdo->lastInsertId();

$adminIds = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
$notif = $pdo->prepare(
    "INSERT INTO notifications (user_id, message, is_read, task_id, notification_type, link)
     VALUES (?, ?, 0, ?, 'task_change_request', ?)"
);
foreach ($adminIds as $adminId) {
    $notif->execute([
        $adminId,
        "Demande de " . ($action === 'delete' ? 'suppression' : 'modification') . " d’une tâche à confirmer.",
        $taskId,
        '/task.php?request=' . $requestId
    ]);
}

$_SESSION['success'] = "Demande envoyée à l’administrateur pour confirmation.";
header('Location: /my_task.php');
exit;
