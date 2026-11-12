<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/app/inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$filter = $_GET['status'] ?? '';
$allowedStatuses = ['pending', 'in_progress', 'completed'];
if (!in_array($filter, $allowedStatuses, true)) {
    $filter = '';
}
$highlightTaskId = (int) (filter_var($_GET['task'] ?? 0, FILTER_VALIDATE_INT) ?: 0);

try {
    if ($isAdmin) {
        $sql = "SELECT tasks.*, assigned.username AS assigned_username,
                       creator.username AS creator_username
                FROM tasks
                LEFT JOIN users assigned ON assigned.id = tasks.assigned_to
                LEFT JOIN users creator ON creator.id = COALESCE(tasks.created_by, tasks.user_id)
                WHERE (tasks.created_by = :created_by
                   OR tasks.user_id = :owned_by
                   OR tasks.assigned_to = :assigned_by)
                  AND NOT EXISTS (
                    SELECT 1 FROM task_trash tr
                    WHERE tr.task_id = tasks.id
                      AND tr.user_id = :trash_user_id
                  )";
        $params = [
            'created_by' => $userId,
            'owned_by' => $userId,
            'assigned_by' => $userId,
            'trash_user_id' => $userId
        ];
    } else {
        $sql = "SELECT tasks.*, assigned.username AS assigned_username,
                       creator.username AS creator_username
                FROM tasks
                LEFT JOIN users assigned ON assigned.id = tasks.assigned_to
                LEFT JOIN users creator ON creator.id = COALESCE(tasks.created_by, tasks.user_id)
                WHERE (tasks.assigned_to = :assigned_to
                   OR tasks.assigned_to IS NULL
                   OR tasks.user_id = :owned_by)
                  AND NOT EXISTS (
                    SELECT 1 FROM task_trash tr
                    WHERE tr.task_id = tasks.id AND tr.user_id = :trash_user_id
                  )";
        $params = [
            'assigned_to' => $userId,
            'owned_by' => $userId,
                            'trash_user_id' => $userId
        ];
    }

    if ($filter !== '') {
        $sql .= " AND tasks.status = :status";
        $params['status'] = $filter;
    }
    $sql .= " ORDER BY tasks.due_date ASC, tasks.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur Mes tâches: ' . $e->getMessage());
    $tasks = [];
    $_SESSION['error'] = "Impossible de charger les tâches pour le moment.";
}

$statusLabels = [
    'pending' => 'En attente',
    'in_progress' => 'En cours',
    'completed' => 'Terminée'
];

require_once __DIR__ . '/app/inc/header.php';
?>

<style>
    .task-page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    .task-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, .08);
        border: 0;
        height: 100%;
    }
    .status-badge {
        display: inline-block;
        padding: .35rem .75rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
    }
    .status-pending { background: #fee2e2; color: #b91c1c; }
    .status-in_progress { background: #fef3c7; color: #92400e; }
    .status-completed { background: #dcfce7; color: #166534; }
    .task-highlight {
        outline: 3px solid #4f46e5;
        outline-offset: 3px;
        box-shadow: 0 0 0 6px rgba(79, 70, 229, .12) !important;
    }
</style>

<section class="task-page-header">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="fw-bold mb-1"><i class="bi bi-list-task me-2"></i>Mes tâches</h1>
                <p class="mb-0 opacity-75">Toutes les tâches qui vous concernent</p>
            </div>
            <?php if ($isAdmin): ?>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/export_tasks_pdf.php" class="btn btn-outline-light rounded-pill fw-bold px-4">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Exporter PDF
                    </a>
                    <a href="/add_task.php" class="btn btn-light new-task-button rounded-pill fw-bold px-4">
                        <i class="bi bi-plus-lg me-2"></i>Nouvelle tâche
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container-fluid px-4 pb-5">
    <div class="d-flex align-items-center flex-wrap gap-2 mb-4">
        <a href="/my_task.php" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Toutes</a>
        <a href="/my_task.php?status=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">En attente</a>
        <a href="/my_task.php?status=in_progress" class="btn btn-sm <?= $filter === 'in_progress' ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill">En cours</a>
        <a href="/my_task.php?status=completed" class="btn btn-sm <?= $filter === 'completed' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">Terminées</a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm">
            <i class="bi bi-clipboard-check display-1 text-muted mb-3"></i>
            <h3 class="fw-bold">Aucune tâche à afficher</h3>
            <p class="text-muted mb-0">Aucune tâche ne correspond à ce filtre.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($tasks as $task): ?>
                <?php
                $status = $task['status'] ?? 'pending';
                if (!isset($statusLabels[$status])) $status = 'pending';
                $recipient = $task['assigned_username'] ? $task['assigned_username'] : 'Tous les utilisateurs';
                ?>
                <div class="col-md-6 col-xl-4">
                    <article id="task-<?= (int) $task['id'] ?>" class="task-card p-4 d-flex flex-column <?= $highlightTaskId === (int) $task['id'] ? 'task-highlight' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <span class="status-badge status-<?= htmlspecialchars($status) ?>">
                                <?= htmlspecialchars($statusLabels[$status]) ?>
                            </span>
                            <small class="text-muted text-nowrap">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= $task['due_date'] ? htmlspecialchars(date('d/m/Y', strtotime($task['due_date']))) : 'Sans échéance' ?>
                            </small>
                        </div>

                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($task['title']) ?></h5>
                        <p class="text-secondary flex-grow-1 mb-3">
                            <?= htmlspecialchars($task['description'] ?: 'Aucune description.') ?>
                        </p>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                            <small class="text-secondary">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($recipient) ?>
                            </small>
                            <div class="d-flex gap-2">
                            <?php if ($isAdmin): ?>
                                <form action="/app/Model/delete-task.php" method="POST" onsubmit="return confirm('Supprimer cette tâche ?')">
                                    <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Supprimer la tâche">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="d-flex gap-2">
                                    <?php if ((int) $task['created_by'] === $userId || (int) $task['user_id'] === $userId): ?>
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-task-<?= (int) $task['id'] ?>">
                                            <i class="bi bi-pencil me-1"></i>Modifier
                                        </button>
                                        <form action="/app/Model/delete-task.php" method="POST" onsubmit="return confirm('Supprimer définitivement votre tâche ?')">
                                            <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Supprimer votre tâche">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <small class="text-muted">Créée par un autre utilisateur</small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top text-secondary small">
                            <div>
                                <i class="bi bi-person-badge me-1"></i>
                                Créée par <strong><?= htmlspecialchars($task['creator_username'] ?: 'Utilisateur inconnu') ?></strong>
                            </div>
                            <div class="mt-1">
                                <i class="bi bi-clock-history me-1"></i>
                                <?= $task['created_at'] ? htmlspecialchars(date('d/m/Y à H:i:s', strtotime($task['created_at']))) : 'Date inconnue' ?>
                            </div>
                        </div>
                        <?php if (!$isAdmin && $status !== 'completed'): ?>
                            <div class="border-top mt-3 pt-3">
                                <button class="btn btn-sm btn-success w-100" type="button" data-bs-toggle="collapse" data-bs-target="#proof-task-<?= (int) $task['id'] ?>">
                                    <i class="bi bi-check2-circle me-1"></i>Envoyer une preuve de fin
                                </button>
                                <div class="collapse mt-2" id="proof-task-<?= (int) $task['id'] ?>">
                                    <form action="/app/Model/submit-completion.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold" for="proof-message-<?= (int) $task['id'] ?>">Message privé à l’administrateur</label>
                                            <textarea class="form-control form-control-sm" id="proof-message-<?= (int) $task['id'] ?>" name="proof_message" rows="2" required></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold" for="proof-file-<?= (int) $task['id'] ?>">Preuve (image, PDF ou document)</label>
                                            <input class="form-control form-control-sm" id="proof-file-<?= (int) $task['id'] ?>" type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                                        </div>
                                        <button class="btn btn-sm btn-primary w-100" type="submit">Envoyer à l’administrateur</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!$isAdmin): ?>
                            <div class="collapse mt-3" id="edit-task-<?= (int) $task['id'] ?>">
                                <form action="/app/Model/request-task-change.php" method="POST" class="border-top pt-3">
                                    <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold" for="title-<?= (int) $task['id'] ?>">Titre demandé</label>
                                        <input class="form-control form-control-sm" id="title-<?= (int) $task['id'] ?>" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold" for="description-<?= (int) $task['id'] ?>">Description demandée</label>
                                        <textarea class="form-control form-control-sm" id="description-<?= (int) $task['id'] ?>" name="description" rows="2"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold" for="due-<?= (int) $task['id'] ?>">Échéance</label>
                                            <input class="form-control form-control-sm" id="due-<?= (int) $task['id'] ?>" type="date" name="due_date" value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold" for="status-<?= (int) $task['id'] ?>">Statut</label>
                                            <select class="form-select form-select-sm" id="status-<?= (int) $task['id'] ?>" name="status">
                                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>En attente</option>
                                                <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Terminée</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-primary w-100" type="submit">
                                        <i class="bi bi-send me-1"></i>Envoyer pour confirmation
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($highlightTaskId): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const task = document.getElementById('task-<?= $highlightTaskId ?>');
    if (task) task.scrollIntoView({ behavior: 'smooth', block: 'center' });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/app/inc/footer.php'; ?>
