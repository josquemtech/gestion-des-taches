<?php

function generateTaskReminders(PDO $pdo, ?int $userId = null): int
{
    $today = new DateTimeImmutable('today');
    $lastReminderDay = $today->modify('+3 days')->format('Y-m-d');
    $todayText = $today->format('Y-m-d');

    if ($userId !== null) {
        $userStmt = $pdo->prepare(
            "SELECT id FROM users
             WHERE id = :user_id AND role = 'user' AND notification_enabled = 1"
        );
        $userStmt->execute(['user_id' => $userId]);
    } else {
        $userStmt = $pdo->query(
            "SELECT id FROM users WHERE role = 'user' AND notification_enabled = 1"
        );
    }
    $targetUsers = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$targetUsers) {
        return 0;
    }

    $taskStmt = $pdo->prepare(
        "SELECT id, title, due_date, assigned_to
         FROM tasks
         WHERE status != 'completed'
           AND due_date IS NOT NULL
           AND due_date >= :today
           AND due_date <= :last_day"
    );
    $taskStmt->execute([
        'today' => $todayText,
        'last_day' => $lastReminderDay
    ]);
    $tasks = $taskStmt->fetchAll();

    $logStmt = $pdo->prepare(
        "INSERT IGNORE INTO task_reminder_log
         (task_id, user_id, reminder_date)
         VALUES (:task_id, :user_id, :reminder_date)"
    );
    $notificationStmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, message, is_read, task_id, notification_type, link)
         VALUES (:user_id, :message, 0, :task_id, 'task_reminder', :link)"
    );

    $created = 0;
    foreach ($tasks as $task) {
        foreach ($targetUsers as $targetUserId) {
            $targetUserId = (int) $targetUserId;
            if (!empty($task['assigned_to']) && (int) $task['assigned_to'] !== $targetUserId) {
                continue;
            }

            $trashStmt = $pdo->prepare(
                "SELECT 1 FROM task_trash
                 WHERE task_id = :task_id AND user_id = :user_id AND permanently_deleted = 0
                 LIMIT 1"
            );
            $trashStmt->execute([
                'task_id' => $task['id'],
                'user_id' => $targetUserId
            ]);
            if ($trashStmt->fetchColumn()) {
                continue;
            }

            $logStmt->execute([
                'task_id' => $task['id'],
                'user_id' => $targetUserId,
                'reminder_date' => $todayText
            ]);
            if ($logStmt->rowCount() === 0) {
                continue;
            }

            $due = new DateTimeImmutable($task['due_date']);
            $daysLeft = (int) $today->diff($due)->format('%a');
            $dayLabel = $daysLeft === 0
                ? "aujourd'hui"
                : ($daysLeft === 1 ? 'dans 1 jour' : "dans {$daysLeft} jours");

            $notificationStmt->execute([
                'user_id' => $targetUserId,
                'message' => "Rappel quotidien : la tâche « {$task['title']} » doit être terminée {$dayLabel}.",
                'task_id' => (int) $task['id'],
                'link' => '/my_task.php?task=' . (int) $task['id']
            ]);
            $created++;
        }
    }

    return $created;
}
