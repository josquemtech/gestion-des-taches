CREATE DATABASE IF NOT EXISTS gestion_des_taches;
USE gestion_des_taches;

-- Table des utilisateurs
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT '/app/img/user.svg',
    notification_enabled TINYINT(1) NOT NULL DEFAULT 1,
    reset_token VARCHAR(64) DEFAULT NULL,
    token_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des tâches
CREATE TABLE tasks (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    assigned_to INT(11) DEFAULT NULL,
    created_by INT(11) DEFAULT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    due_date DATE DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des notifications
CREATE TABLE notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    task_id INT(11) DEFAULT NULL,
    notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
    link VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_notifications_user_read (user_id, is_read),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE task_change_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    task_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    action ENUM('edit','delete') NOT NULL,
    proposed_title VARCHAR(100) DEFAULT NULL,
    proposed_description TEXT,
    proposed_due_date DATE DEFAULT NULL,
    proposed_status ENUM('pending','in_progress','completed') DEFAULT NULL,
    reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewed_by INT(11) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE task_trash (
    id INT(11) NOT NULL AUTO_INCREMENT,
    task_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    permanently_deleted TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY task_trash_private_unique (task_id, user_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE task_completion_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    task_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    proof_message TEXT NOT NULL,
    proof_file VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_message TEXT DEFAULT NULL,
    reviewed_by INT(11) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE jos_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    sender ENUM('user','assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_jos_user_created (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compte administrateur initial
-- Identifiant: admin
-- Mot de passe: Admin@2026!
INSERT INTO users (username, password, email, role, avatar, notification_enabled)
VALUES (
    'admin',
    '$2y$10$DxO4CXQQn6WZRrUm2sZyCO8.1iV/jqte/Tup748o3BPil9pBlFKeW',
    'admin@gestion-taches.local',
    'admin',
    '/app/img/user.svg',
    1
);

CREATE TABLE IF NOT EXISTS task_reminder_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    reminder_date DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY task_reminder_daily_unique (task_id, user_id, reminder_date),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;