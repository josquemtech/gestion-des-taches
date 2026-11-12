<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/app/inc/DB_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$stmt = $pdo->prepare(
    "    SELECT t.*, tr.deleted_at, assigned.username AS assigned_username,
           creator.username AS creator_username
    FROM task_trash tr
    JOIN tasks t ON t.id = tr.task_id
    LEFT JOIN users assigned ON assigned.id = t.assigned_to
    LEFT JOIN users creator ON creator.id = COALESCE(t.created_by, t.user_id)
     WHERE tr.user_id = :user_id
       AND tr.permanently_deleted = 0
     ORDER BY tr.deleted_at DESC"
);
$stmt->execute(['user_id' => $userId]);
$trashedTasks = $stmt->fetchAll();

require_once __DIR__ . '/app/inc/header.php';
?>
<section class="py-4">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1"><i class="bi bi-trash3 me-2"></i>Ma corbeille</h1>
                <p class="text-muted mb-0">Tâches masquées uniquement dans votre espace privé.</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($trashedTasks)): ?>
            <div class="text-center py-5 trash-empty-state rounded-3 shadow-sm">
                <i class="bi bi-trash3 display-4 text-muted"></i>
                <h3 class="h5 mt-3">Votre corbeille est vide</h3>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($trashedTasks as $task): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 shadow-sm border-0">
                            <div class="card-body p-4">
                                <span class="badge bg-secondary mb-3">Dans la corbeille</span>
                                <h5 class="fw-bold"><?= htmlspecialchars($task['title']) ?></h5>
                                <p class="text-secondary"><?= htmlspecialchars($task['description'] ?: 'Aucune description.') ?></p>
                                <small class="text-muted d-block mb-3">
                                    Supprimée le <?= date('d/m/Y à H:i', strtotime($task['deleted_at'])) ?>
                                </small>
                                <div class="text-muted small mb-3">
                                    <div><i class="bi bi-person-badge me-1"></i>Créée par <strong><?= htmlspecialchars($task['creator_username'] ?: 'Utilisateur inconnu') ?></strong></div>
                                    <div class="mt-1"><i class="bi bi-clock-history me-1"></i><?= $task['created_at'] ? htmlspecialchars(date('d/m/Y à H:i:s', strtotime($task['created_at']))) : 'Date inconnue' ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <form action="/app/Model/task-trash.php" method="POST">
                                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                        <button class="btn btn-sm btn-success" name="action" value="restore" type="submit">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer
                                        </button>
                                    </form>
                                    <form action="/app/Model/task-trash.php" method="POST" onsubmit="return confirm('Supprimer définitivement de votre corbeille ?')">
                                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" name="action" value="delete_private" type="submit">
                                            <i class="bi bi-x-lg me-1"></i>Supprimer définitivement
                                        </button>
                                    </form>
                                    <?php if ($isAdmin && (int) $task['created_by'] === $userId): ?>
                                        <form action="/app/Model/task-trash.php" method="POST" onsubmit="return confirm('Supprimer cette tâche pour tous les utilisateurs ?')">
                                            <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                            <button class="btn btn-sm btn-danger" name="action" value="delete_global" type="submit">
                                                Supprimer partout
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/app/inc/footer.php'; ?>
