<?php
session_start();
require_once '../inc/DB_connection.php';

// Sécurité : Vérifier la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $assigned_to = $_POST['assigned_to'] ?? 'all';

    if (empty($title)) {
        $_SESSION['error_add_task'] = "Le titre est requis";
        header('Location: /add_task.php');
        exit;
    }

    try {
        // 1. Insertion de la tâche (avec status par défaut 'pending')
        $assignedUserId = $assigned_to === 'all' ? null : filter_var($assigned_to, FILTER_VALIDATE_INT);
        if ($assigned_to !== 'all' && !$assignedUserId) {
            throw new InvalidArgumentException("Destinataire invalide.");
        }

        $stmt = $pdo->prepare("INSERT INTO tasks
            (user_id, assigned_to, created_by, title, description, due_date, status)
            VALUES (:user_id, :assigned_to, :created_by, :title, :description, :due_date, 'pending')");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'assigned_to' => $assignedUserId,
            'created_by' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date ?: null
        ]);
        $newTaskId = (int) $pdo->lastInsertId();

        $notifStmt = $pdo->prepare(
            "INSERT INTO notifications (user_id, message, is_read, task_id, notification_type, link)
             VALUES (:user_id, :message, 0, :task_id, 'task_created', :link)"
        );
        if ($assignedUserId) {
            $targetsStmt = $pdo->prepare(
                "SELECT id FROM users
                 WHERE id = ? AND role IN ('user', 'admin') AND notification_enabled = 1"
            );
            $targetsStmt->execute([$assignedUserId]);
            $targetIds = $targetsStmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $targetIds = $pdo->query(
                "SELECT id FROM users WHERE role = 'user' AND notification_enabled = 1"
            )->fetchAll(PDO::FETCH_COLUMN);
        }
        foreach ($targetIds as $targetId) {
            $dueMessage = $due_date
                ? " Date d'échéance : " . date('d/m/Y', strtotime($due_date)) . "."
                : '';
            $notifStmt->execute([
                'user_id' => $targetId,
                'message' => "Nouvelle tâche créée par " . ($_SESSION['username'] ?? 'un utilisateur') . " : " . $title . $dueMessage,
                'task_id' => $newTaskId,
                'link' => '/my_task.php?task=' . $newTaskId,
            ]);
        }

        $_SESSION['success'] = "Tâche créée avec succès !";
        header('Location: /my_task.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_add_task'] = "Erreur lors de la création : " . $e->getMessage();
        header('Location: /add_task.php');
        exit;
    }
} else {
    // Accès direct interdit
    header('Location: /add_task.php');
    exit;
}
?>