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
$message = trim($_POST['proof_message'] ?? '');
$userId = (int) $_SESSION['user_id'];
if (!$taskId || $message === '') {
    $_SESSION['error'] = "Ajoutez un message et une tâche valide.";
    header('Location: /my_task.php');
    exit;
}

$check = $pdo->prepare(
    "SELECT id FROM tasks
     WHERE id = :id AND status != 'completed'
       AND (assigned_to = :user_id OR assigned_to IS NULL OR user_id = :owned_id)"
);
$check->execute(['id' => $taskId, 'user_id' => $userId, 'owned_id' => $userId]);
if (!$check->fetch()) {
    $_SESSION['error'] = "Cette tâche ne peut pas recevoir une preuve de fin.";
    header('Location: /my_task.php');
    exit;
}

$pending = $pdo->prepare(
    "SELECT id FROM task_completion_requests
     WHERE task_id = :task_id AND user_id = :user_id AND status = 'pending'"
);
$pending->execute(['task_id' => $taskId, 'user_id' => $userId]);
if ($pending->fetch()) {
    $_SESSION['error'] = "Une preuve est déjà en attente pour cette tâche.";
    header('Location: /my_task.php');
    exit;
}

$proofFile = null;
if (!empty($_FILES['proof_file']['name'])) {
    $uploadDir = __DIR__ . '/../../uploads/completion_proofs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $_SESSION['error'] = "Impossible de préparer le dossier des preuves.";
        header('Location: /my_task.php');
        exit;
    }
    $allowed = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'pdf' => 'application/pdf', 'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain'
    ];
    $extension = strtolower(pathinfo($_FILES['proof_file']['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$extension]) || $_FILES['proof_file']['size'] > 10 * 1024 * 1024) {
        $_SESSION['error'] = "Fichier invalide ou supérieur à 10 Mo.";
        header('Location: /my_task.php');
        exit;
    }
    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($_FILES['proof_file']['tmp_name'], $uploadDir . '/' . $fileName)) {
        $_SESSION['error'] = "Échec du téléchargement de la preuve.";
        header('Location: /my_task.php');
        exit;
    }
    $proofFile = '/uploads/completion_proofs/' . $fileName;
}

$stmt = $pdo->prepare(
    "INSERT INTO task_completion_requests (task_id, user_id, proof_message, proof_file, status)
     VALUES (:task_id, :user_id, :message, :proof_file, 'pending')"
);
$stmt->execute([
    'task_id' => $taskId,
    'user_id' => $userId,
    'message' => $message,
    'proof_file' => $proofFile
]);

$taskInfoStmt = $pdo->prepare(
    "SELECT t.title, u.username AS submitter
     FROM tasks t
     JOIN users u ON u.id = :user_id
     WHERE t.id = :task_id"
);
$taskInfoStmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
$taskInfo = $taskInfoStmt->fetch();

$adminIds = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
$notif = $pdo->prepare(
    "INSERT INTO notifications (user_id, message, is_read, task_id, notification_type, link)
     VALUES (?, ?, 0, ?, ?, ?)"
);
foreach ($adminIds as $adminId) {
    $notif->execute([
        $adminId,
        "Preuve de fin reçue pour « " . ($taskInfo['title'] ?? 'Tâche') . " » par " . ($taskInfo['submitter'] ?? 'un employé') . ". Confirmation requise.",
        $taskId,
        'completion_proof',
        '/task.php?completion=' . $taskId
    ]);
}

$_SESSION['success'] = "Preuve envoyée uniquement à l’administrateur.";
header('Location: /my_task.php');
exit;
