<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../inc/DB_connection.php';
require_once __DIR__ . '/../inc/task_reminders.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Session expiree.']);
        exit;
    }

    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    if (!empty($input['notification_id'])) {
        $notificationId = filter_var($input['notification_id'], FILTER_VALIDATE_INT);
        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Notification invalide.']);
            exit;
        }
        try {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
            $stmt->execute([$notificationId, $_SESSION['user_id']]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('Erreur lors du marquage de la notification: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise a jour de la notification.']);
        }
        exit;
    }

    if (empty($input['mark_all_read'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Requete invalide.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$_SESSION['user_id']]);

        echo json_encode([
            'success' => true,
            'updated' => $stmt->rowCount()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('Erreur lors du marquage des notifications: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise a jour des notifications.']);
    }
    exit;
}

try {
    generateTaskReminders($pdo, (int) $_SESSION['user_id']);
    $stmt = $pdo->prepare(
        'SELECT id, message, is_read, created_at, task_id, notification_type, link
         FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur de chargement des notifications: ' . $e->getMessage());
    $notifications = [];
}

if (isset($_GET['json'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'notifications' => array_values(array_filter($notifications, function ($notification) {
            return empty($notification['is_read']);
        }))
    ]);
    exit;
}

// Marque comme lue la notification ouverte depuis le header (lien ?notification_id=...).
$openedNotificationId = isset($_GET['notification_id'])
    ? filter_var($_GET['notification_id'], FILTER_VALIDATE_INT)
    : 0;

if ($openedNotificationId) {
    try {
        $stmt = $pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND is_read = 0'
        );
        $stmt->execute([$openedNotificationId, $_SESSION['user_id']]);

        foreach ($notifications as &$notification) {
            if ((int) $notification['id'] === $openedNotificationId) {
                $notification['is_read'] = 1;
                break;
            }
        }
        unset($notification);
    } catch (PDOException $e) {
        error_log('Erreur lors du marquage de la notification comme lue: ' . $e->getMessage());
    }
}

