<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/DB_connection.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /task.php');
    exit;
}

$requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
$decision = $_POST['decision'] ?? '';
$message = trim($_POST['admin_message'] ?? '');
if (!$requestId || !in_array($decision, ['approved', 'rejected'], true)) {
    $_SESSION['error'] = 'Demande invalide.';
    header('Location: /task.php');
    exit;
}

try {
    $select = $pdo->prepare(
        "SELECT cr.*, t.title
         FROM task_completion_requests cr
         JOIN tasks t ON t.id = cr.task_id
         WHERE cr.id = :id AND cr.status = 'pending'"
    );
    $select->execute(['id' => $requestId]);
    $request = $select->fetch();
    if (!$request) throw new RuntimeException('Demande introuvable.');

    $status = $decision === 'approved' ? 'approved' : 'rejected';
    $update = $pdo->prepare(
        "UPDATE task_completion_requests
         SET status = :status, admin_message = :message, reviewed_by = :admin_id, reviewed_at = NOW()
         WHERE id = :id"
    );
    $update->execute([
        'status' => $status,
        'message' => $message,
        'admin_id' => $_SESSION['user_id'],
        'id' => $requestId
    ]);

    if ($decision === 'approved') {
        $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?")
            ->execute([$request['task_id']]);
    }

    $pdo->prepare(
        "INSERT INTO notifications (user_id, message, is_read, task_id, notification_type, link)
         VALUES (?, ?, 0, ?, 'completion_reviewed', ?)"
    )->execute([
        $request['user_id'],
        $decision === 'approved'
            ? "Votre preuve pour « {$request['title']} » a été approuvée. La tâche est terminée."
            : "Votre preuve pour « {$request['title']} » a été refusée.",
        $request['task_id'],
        '/my_task.php?task=' . (int) $request['task_id']
    ]);

    $_SESSION['success'] = $decision === 'approved'
        ? 'Preuve approuvée et tâche terminée.'
        : 'Preuve refusée.';
} catch (Throwable $e) {
    error_log('Erreur validation preuve: ' . $e->getMessage());
    $_SESSION['error'] = 'Impossible de traiter cette preuve.';
}

header('Location: /task.php');
exit;
