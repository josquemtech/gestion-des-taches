<?php
require_once __DIR__ . '/app/inc/DB_connection.php';

function addColumnIfMissing(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);
    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

addColumnIfMissing($pdo, 'users', 'avatar', "VARCHAR(255) DEFAULT '/app/img/user.svg'");
addColumnIfMissing($pdo, 'users', 'notification_enabled', 'TINYINT(1) NOT NULL DEFAULT 1');
$pdo->exec("ALTER TABLE users MODIFY notification_enabled TINYINT(1) NOT NULL DEFAULT 1");
addColumnIfMissing($pdo, 'tasks', 'assigned_to', 'INT NULL');
addColumnIfMissing($pdo, 'tasks', 'created_by', 'INT NULL');
$pdo->exec("UPDATE tasks SET created_by = user_id WHERE created_by IS NULL");
addColumnIfMissing($pdo, 'tasks', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
$pdo->exec("ALTER TABLE tasks MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

foreach (
    [
        'tasks_assigned_to_fk' => "ALTER TABLE tasks ADD CONSTRAINT tasks_assigned_to_fk
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL",
        'tasks_created_by_fk' => "ALTER TABLE tasks ADD CONSTRAINT tasks_created_by_fk
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL"
    ] as $constraintName => $sql
) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND TABLE_NAME = 'tasks'
           AND CONSTRAINT_NAME = ?"
    );
    $stmt->execute([$constraintName]);
    if ((int) $stmt->fetchColumn() === 0) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log('Erreur lors de la contrainte ' . $constraintName . ': ' . $e->getMessage());
        }
    }
}

$pdo->exec(
    "UPDATE users SET avatar = '/app/img/user.svg'
     WHERE avatar IS NULL OR avatar = ''"
);
$pdo->exec(
    "UPDATE users SET avatar = '/app/img/user.svg'
     WHERE avatar = '/app/img/user.png'"
);
$pdo->exec(
    "UPDATE users SET notification_enabled = 1
     WHERE notification_enabled IS NULL"
);

$hash = '$2y$10$DxO4CXQQn6WZRrUm2sZyCO8.1iV/jqte/Tup748o3BPil9pBlFKeW';
$stmt = $pdo->prepare(
    "INSERT INTO users (username, password, email, role, avatar, notification_enabled)
     SELECT ?, ?, ?, 'admin', '/app/img/user.svg', 1
     WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin')"
);
$stmt->execute(['admin', $hash, 'admin@gestion-taches.local']);

$stmt = $pdo->prepare(
    "UPDATE users
     SET username = 'admin', password = ?, email = 'admin@gestion-taches.local', role = 'admin'
     WHERE role = 'admin'
     ORDER BY id ASC
     LIMIT 1"
);
$stmt->execute([$hash]);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS task_change_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        action ENUM('edit','delete') NOT NULL,
        proposed_title VARCHAR(100) NULL,
        proposed_description TEXT NULL,
        proposed_due_date DATE NULL,
        proposed_status ENUM('pending','in_progress','completed') NULL,
        reason TEXT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        reviewed_by INT NULL,
        reviewed_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_task_change_status (status),
        CONSTRAINT task_change_task_fk FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        CONSTRAINT task_change_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT task_change_admin_fk FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS task_trash (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY task_trash_private_unique (task_id, user_id),
        CONSTRAINT task_trash_task_fk FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        CONSTRAINT task_trash_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
addColumnIfMissing($pdo, 'task_trash', 'permanently_deleted', 'TINYINT(1) NOT NULL DEFAULT 0');
addColumnIfMissing($pdo, 'notifications', 'task_id', 'INT NULL');
addColumnIfMissing($pdo, 'notifications', 'notification_type', "VARCHAR(50) NOT NULL DEFAULT 'general'");
addColumnIfMissing($pdo, 'notifications', 'link', 'VARCHAR(255) NULL');

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS task_reminder_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        reminder_date DATE NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY task_reminder_daily_unique (task_id, user_id, reminder_date),
        CONSTRAINT reminder_task_fk FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        CONSTRAINT reminder_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS task_completion_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        proof_message TEXT NOT NULL,
        proof_file VARCHAR(255) NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        admin_message TEXT NULL,
        reviewed_by INT NULL,
        reviewed_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT completion_task_fk FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        CONSTRAINT completion_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT completion_admin_fk FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS jos_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        sender ENUM('user','assistant') NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_jos_user_created (user_id, created_at),
        CONSTRAINT jos_message_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

echo "Migration appliquée. Administrateur: admin / Admin@2026!\n";
