<?php
require_once __DIR__ . '/../inc/DB_connection.php';
require_once __DIR__ . '/../inc/task_reminders.php';

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
        http_response_code(403);
        echo "Accès réservé à l’administrateur.";
        exit;
    }
}

try {
    $created = generateTaskReminders($pdo);
    if ($isCli) {
        echo "{$created} rappel(s) créé(s).\n";
        exit;
    }
    $_SESSION['success'] = "{$created} rappel(s) de tâches généré(s).";
    header('Location: /notifications.php');
    exit;
} catch (Throwable $e) {
    error_log('Erreur génération rappels: ' . $e->getMessage());
    if ($isCli) {
        fwrite(STDERR, "Erreur lors de la génération des rappels.\n");
        exit(1);
    }
    $_SESSION['error'] = "Erreur lors de la génération des rappels.";
    header('Location: /task.php');
    exit;
}
