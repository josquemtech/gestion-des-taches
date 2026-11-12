<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/app/inc/DB_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /my_task.php');
    exit;
}

$allowedStatuses = ['pending', 'in_progress', 'completed'];
$filter = $_GET['status'] ?? '';
if (!in_array($filter, $allowedStatuses, true)) {
    $filter = '';
}
$highlightCompletionTaskId = (int) (filter_var($_GET['completion'] ?? 0, FILTER_VALIDATE_INT) ?: 0);
$highlightRequestId = (int) (filter_var($_GET['request'] ?? 0, FILTER_VALIDATE_INT) ?: 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['decision'])) {
    $requestId = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);
    $decision = $_POST['decision'];
    if ($requestId && in_array($decision, ['approved', 'rejected'], true)) {
        $pdo->beginTransaction();
        try {
            $requestStmt = $pdo->prepare(
                "SELECT r.*, t.id AS current_task_id
                 FROM task_change_requests r
                 JOIN tasks t ON t.id = r.task_id
                 WHERE r.id = :id AND r.status = 'pending'
                 FOR UPDATE"
            );
            $requestStmt->execute(['id' => $requestId]);
            $request = $requestStmt->fetch();

            if ($request) {
                if ($decision === 'approved' && $request['action'] === 'delete') {
                    $trashStmt = $pdo->prepare(
                        "INSERT INTO task_trash (task_id, user_id, permanently_deleted)
                         VALUES (:task_id, :user_id, 0)
                         ON DUPLICATE KEY UPDATE
                           deleted_at = CURRENT_TIMESTAMP,
                           permanently_deleted = 0"
                    );
                    $trashStmt->execute([
                        'task_id' => $request['task_id'],
                        'user_id' => $request['user_id']
                    ]);
                } elseif ($decision === 'approved' && $request['action'] === 'edit') {
                    $updateStmt = $pdo->prepare(
                        "UPDATE tasks
                         SET title = :title, description = :description, due_date = :due_date,
                             status = COALESCE(:status, status)
                         WHERE id = :id"
                    );
                    $updateStmt->execute([
                        'title' => $request['proposed_title'],
                        'description' => $request['proposed_description'],
                        'due_date' => $request['proposed_due_date'],
                        'status' => $request['proposed_status'],
                        'id' => $request['task_id']
                    ]);
                }

                $reviewStmt = $pdo->prepare(
                    "UPDATE task_change_requests
                     SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW()
                     WHERE id = :id"
                );
                $reviewStmt->execute([
                    'status' => $decision,
                    'reviewed_by' => $_SESSION['user_id'],
                    'id' => $requestId
                ]);

                $_SESSION['success'] = $decision === 'approved'
                    ? 'Demande approuvée et appliquée.'
                    : 'Demande rejetée.';
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = "Impossible de traiter cette demande.";
            error_log($e->getMessage());
        }
    }
    header('Location: /task.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'];
    if ($taskId && in_array($newStatus, $allowedStatuses, true)) {
        $stmt = $pdo->prepare(
            "UPDATE tasks SET status = :status
             WHERE id = :id"
        );
        $stmt->execute([
            'status' => $newStatus,
            'id' => $taskId
        ]);
        $_SESSION['success'] = 'Statut de la tâche mis à jour.';
    }
    header('Location: /task.php?status=' . urlencode($newStatus));
    exit;
}

$sql = "SELECT tasks.*, assigned.username AS assigned_username,
               creator.username AS creator_username
        FROM tasks
        LEFT JOIN users assigned ON assigned.id = tasks.assigned_to
        LEFT JOIN users creator ON creator.id = COALESCE(tasks.created_by, tasks.user_id)
        WHERE NOT EXISTS (
            SELECT 1 FROM task_trash tr
            WHERE tr.task_id = tasks.id
              AND tr.user_id = :trash_user_id
        )";
$params = ['trash_user_id' => (int) $_SESSION['user_id']];
if ($filter !== '') {
    $sql .= " AND tasks.status = :status";
    $params['status'] = $filter;
}
$sql .= " ORDER BY tasks.due_date ASC, tasks.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$pendingRequests = $pdo->query(
    "SELECT r.*, u.username, t.title AS current_title
     FROM task_change_requests r
     JOIN users u ON u.id = r.user_id
     JOIN tasks t ON t.id = r.task_id
     WHERE r.status = 'pending'
     ORDER BY r.created_at DESC"
)->fetchAll();

$pendingCompletionRequests = $pdo->query(
    "SELECT cr.*, t.title AS task_title, u.username AS employee_username
     FROM task_completion_requests cr
     JOIN tasks t ON t.id = cr.task_id
     JOIN users u ON u.id = cr.user_id
     WHERE cr.status = 'pending'
     ORDER BY cr.created_at DESC"
)->fetchAll();

$labels = [
    'pending' => 'En attente',
    'in_progress' => 'En cours',
    'completed' => 'Terminées'
];

require_once __DIR__ . '/app/inc/header.php';
?>

<section class="py-4">
    <style>
        .task-highlight {
            outline: 3px solid #4f46e5;
            outline-offset: 3px;
            box-shadow: 0 0 0 6px rgba(79, 70, 229, .12);
        }
    </style>
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-1"><i class="bi bi-kanban me-2"></i>Gestion des tâches</h1>
                <p class="text-muted mb-0">Filtrez les tâches et modifiez leur statut.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/export_tasks_pdf.php" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Exporter l’historique PDF
                </a>
                <a href="/add_task.php" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle tâche
                </a>
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

        <?php if (!empty($pendingRequests)): ?>
            <div class="card border-warning shadow-sm mb-4">
                <div class="card-header bg-warning-subtle fw-bold">
                    <i class="bi bi-hourglass-split me-2"></i>
                    Demandes des utilisateurs à confirmer (<?= count($pendingRequests) ?>)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($pendingRequests as $request): ?>
                            <div class="col-lg-6">
                                <div id="request-<?= (int) $request['id'] ?>" class="border rounded-3 p-3 h-100 <?= $highlightRequestId === (int) $request['id'] ? 'task-highlight' : '' ?>">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong><?= htmlspecialchars($request['username']) ?></strong>
                                        <span class="badge <?= $request['action'] === 'delete' ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= $request['action'] === 'delete' ? 'Suppression' : 'Modification' ?>
                                        </span>
                                    </div>
                                    <p class="mb-2 mt-2"><b>Tâche :</b> <?= htmlspecialchars($request['current_title']) ?></p>
                                    <?php if ($request['action'] === 'edit'): ?>
                                        <p class="mb-1"><b>Nouveau titre :</b> <?= htmlspecialchars($request['proposed_title']) ?></p>
                                        <p class="mb-1"><b>Nouveau statut :</b> <?= htmlspecialchars($labels[$request['proposed_status']] ?? 'Conserver') ?></p>
                                        <p class="mb-2"><b>Nouvelle échéance :</b> <?= $request['proposed_due_date'] ? date('d/m/Y', strtotime($request['proposed_due_date'])) : 'Sans échéance' ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($request['reason'])): ?>
                                        <p class="text-muted mb-2"><?= htmlspecialchars($request['reason']) ?></p>
                                    <?php endif; ?>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                        <button class="btn btn-sm btn-success" type="submit" name="decision" value="approved">
                                            <i class="bi bi-check-lg me-1"></i>Approuver
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" name="decision" value="rejected">
                                            <i class="bi bi-x-lg me-1"></i>Refuser
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($pendingCompletionRequests)): ?>
            <div class="card border-success shadow-sm mb-4">
                <div class="card-header bg-success-subtle fw-bold">
                    <i class="bi bi-file-earmark-check me-2"></i>
                    Preuves de fin de tâches (<?= count($pendingCompletionRequests) ?>)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($pendingCompletionRequests as $completion): ?>
                            <div class="col-lg-6">
                                <div id="completion-<?= (int) $completion['task_id'] ?>" class="border rounded-3 p-3 h-100 <?= $highlightCompletionTaskId === (int) $completion['task_id'] ? 'task-highlight' : '' ?>">
                                    <strong><?= htmlspecialchars($completion['task_title']) ?></strong>
                                    <div class="text-muted small mt-1">
                                        Envoyée par <?= htmlspecialchars($completion['employee_username']) ?>
                                        le <?= date('d/m/Y à H:i', strtotime($completion['created_at'])) ?>
                                    </div>
                                    <p class="mt-3 mb-2"><?= nl2br(htmlspecialchars($completion['proof_message'])) ?></p>
                                    <?php if (!empty($completion['proof_file'])): ?>
                                        <a class="btn btn-sm btn-outline-primary mb-3" href="<?= htmlspecialchars($completion['proof_file']) ?>" target="_blank">
                                            <i class="bi bi-paperclip me-1"></i>Voir la preuve jointe
                                        </a>
                                    <?php endif; ?>
                                    <form action="/app/Model/review-completion.php" method="POST" class="mt-2">
                                        <input type="hidden" name="request_id" value="<?= (int) $completion['id'] ?>">
                                        <textarea class="form-control form-control-sm mb-2" name="admin_message" rows="2" placeholder="Message à l’employé (facultatif)"></textarea>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-success" type="submit" name="decision" value="approved">
                                                <i class="bi bi-check-lg me-1"></i>Approuver
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" type="submit" name="decision" value="rejected">
                                                <i class="bi bi-x-lg me-1"></i>Refuser
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="/task.php" class="btn <?= $filter === '' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">Toutes</a>
            <?php foreach ($labels as $status => $label): ?>
                <?php $color = $status === 'pending' ? 'danger' : ($status === 'in_progress' ? 'warning' : 'success'); ?>
                <a href="/task.php?status=<?= $status ?>" class="btn <?= $filter === $status ? 'btn-' . $color : 'btn-outline-' . $color ?> rounded-pill"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <i class="bi bi-clipboard-x display-4 text-muted"></i>
                <h3 class="h5 mt-3 mb-0">Aucune tâche ne correspond à ce filtre.</h3>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($tasks as $task): ?>
                    <?php
                    $status = $task['status'] ?? 'pending';
                    if (!isset($labels[$status])) $status = 'pending';
                    $color = $status === 'pending' ? 'danger' : ($status === 'in_progress' ? 'warning' : 'success');
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <span class="badge bg-<?= $color ?>"><?= $labels[$status] ?></span>
                                    <small class="text-muted">
                                        <?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'Sans échéance' ?>
                                    </small>
                                </div>
                                <h5 class="fw-bold"><?= htmlspecialchars($task['title']) ?></h5>
                                <p class="text-secondary"><?= htmlspecialchars($task['description'] ?: 'Aucune description.') ?></p>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($task['assigned_username'] ?: 'Tous les utilisateurs') ?>
                                </div>
                                <div class="text-muted small mb-3">
                                    <div><i class="bi bi-person-badge me-1"></i>Créée par <strong><?= htmlspecialchars($task['creator_username'] ?: 'Utilisateur inconnu') ?></strong></div>
                                    <div class="mt-1"><i class="bi bi-clock-history me-1"></i><?= $task['created_at'] ? htmlspecialchars(date('d/m/Y à H:i:s', strtotime($task['created_at']))) : 'Date inconnue' ?></div>
                                </div>
                                <form method="POST" class="d-flex gap-2 mb-2">
                                    <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                    <label class="visually-hidden" for="status-<?= (int) $task['id'] ?>">Statut</label>
                                    <select class="form-select form-select-sm" id="status-<?= (int) $task['id'] ?>" name="status">
                                        <?php foreach ($labels as $statusOption => $statusLabel): ?>
                                            <option value="<?= $statusOption ?>" <?= $status === $statusOption ? 'selected' : '' ?>><?= $statusLabel ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Changer</button>
                                </form>
                                <form action="/app/Model/delete-task.php" method="POST" onsubmit="return confirm('Supprimer cette tâche ?')">
                                    <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($highlightCompletionTaskId || $highlightRequestId): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const target = document.querySelector('.task-highlight');
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/app/inc/footer.php'; ?>
