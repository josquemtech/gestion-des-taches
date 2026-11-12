<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'app/inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY due_date ASC");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Tâches - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); color: white; padding: 3rem 0 2rem; margin-bottom: 2rem; }
        .task-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.2s ease; border: none; margin-bottom: 1rem; height: 100%; }
        .task-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .status-badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-pending { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .status-in_progress { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .status-completed { background: rgba(25, 135, 84, 0.1); color: #198754; }
        .btn-add-task { background: white; color: var(--primary-color); border: 2px solid white; border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 600; transition: all 0.2s; }
        .btn-add-task:hover { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="fw-bold mb-1"><i class="bi bi-list-task me-3"></i>Mes Tâches</h1>
                    <p class="mb-0 opacity-90">Gérez et suivez l'avancement de vos projets</p>
                </div>
                <a href="/add-task.php" class="btn btn-add-task">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle tâche
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (count($tasks) > 0): ?>
            <div class="row g-4">
                <?php foreach ($tasks as $task): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="task-card p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '_', $task['status'] ?? 'pending')) ?>">
                                    <?= htmlspecialchars($task['status'] ?? 'En attente') ?>
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= isset($task['due_date']) ? date('d/m/Y', strtotime($task['due_date'])) : 'Pas de date' ?>
                                </small>
                            </div>
                            
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($task['title']) ?></h5>
                            <p class="text-muted small flex-grow-1" style="line-height: 1.6;">
                                <?= htmlspecialchars(substr($task['description'] ?? '', 0, 100)) ?>...
                            </p>
                            
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-secondary"><i class="bi bi-person-circle me-1"></i> Assigné à moi</small>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Modifier</a></li>
                                        <li>
                                            <form action="/delete-task.php" method="POST" onsubmit="return confirm('Êtes-vous sûr ?')">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-trash me-2"></i>Supprimer</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <i class="bi bi-clipboard-check display-1 text-muted mb-3"></i>
                <h3 class="fw-bold text-dark">Aucune tâche pour le moment</h3>
                <p class="text-muted mb-4">Commencez par ajouter votre première tâche pour organiser votre journée.</p>
                <a href="/add-task.php" class="btn btn-primary px-4 py-2 rounded-pill">
                    <i class="bi bi-plus-lg me-2"></i>Créer une tâche
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>