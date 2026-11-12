<?php
require_once 'app/inc/header.php';

$stats = ['pending' => 0, 'in_progress' => 0, 'completed' => 0, 'unread' => 0];
$recentTasks = [];
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        $visibilitySql = "created_by = :created_by OR user_id = :owned_by OR assigned_to = :assigned_by";
        $params = [
            'created_by' => $_SESSION['user_id'],
            'owned_by' => $_SESSION['user_id'],
            'assigned_by' => $_SESSION['user_id']
        ];
    } else {
        $visibilitySql = "assigned_to = :assigned_to OR assigned_to IS NULL OR user_id = :owned_by";
        $params = [
            'assigned_to' => $_SESSION['user_id'],
            'owned_by' => $_SESSION['user_id']
        ];
    }

    $countStmt = $pdo->prepare("SELECT status, COUNT(*) FROM tasks WHERE $visibilitySql GROUP BY status");
    $countStmt->execute($params);
    foreach ($countStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $status => $count) {
        if (isset($stats[$status])) $stats[$status] = (int) $count;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['unread'] = (int) $stmt->fetchColumn();

    $filter = $_GET['filter'] ?? '';
    $allowedFilters = ['in_progress', 'completed', 'pending'];
    if (!in_array($filter, $allowedFilters, true)) {
        $filter = '';
    }

    $filterSql = $filter ? " AND tasks.status = :filter" : '';
    $sql = "SELECT tasks.*, assigned.username AS assigned_username,
                   creator.username AS creator_username
            FROM tasks
            LEFT JOIN users assigned ON assigned.id = tasks.assigned_to
            LEFT JOIN users creator ON creator.id = COALESCE(tasks.created_by, tasks.user_id)
            WHERE ($visibilitySql)$filterSql
            ORDER BY tasks.created_at DESC LIMIT 50";
    $queryParams = $params;
    if ($filter) $queryParams['filter'] = $filter;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($queryParams);
    $recentTasks = $stmt->fetchAll();
}
?>

<?php if(!isset($_SESSION['user_id'])): ?>
<!-- Page d'accueil publique -->
<div class="hero-section">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-75">
            <div class="col-lg-8 text-center">
                <div class="mb-4">
                    <div class="icon-circle bg-primary-subtle mx-auto mb-4" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check2-square text-primary" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h1 class="display-3 fw-bold mb-3 text-dark">Gestion des Tâches</h1>
                <p class="lead text-secondary mb-5 fs-5">Organisez vos projets, collaborez efficacement et boostez votre productivité au quotidien</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap mb-5">
                    <a href="/login.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm hover-lift"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a>
                    <a href="/signup.php" class="btn btn-outline-primary btn-lg px-5 py-3 rounded-pill hover-lift"><i class="bi bi-person-plus me-2"></i>Créer un compte</a>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-5 pt-5">
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-wrapper mb-3"><i class="bi bi-list-check fs-1 text-primary"></i></div>
                    <h4 class="fw-semibold mb-2">Gestion Intuitive</h4>
                    <p class="text-secondary mb-0">Interface moderne et ergonomique pour une expérience utilisateur fluide</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-wrapper mb-3"><i class="bi bi-bell fs-1 text-success"></i></div>
                    <h4 class="fw-semibold mb-2">Notifications Instantanées</h4>
                    <p class="text-secondary mb-0">Restez informé en temps réel des mises à jour importantes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-wrapper mb-3"><i class="bi bi-people fs-1 text-info"></i></div>
                    <h4 class="fw-semibold mb-2">Collaboration d'Équipe</h4>
                    <p class="text-secondary mb-0">Travaillez ensemble efficacement sur vos projets communs</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: calc(100vh - 76px); }
.icon-circle { animation: fadeInDown 0.6s ease-out; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
.feature-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s ease, box-shadow 0.2s ease; }
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
.icon-wrapper { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: rgba(13, 110, 253, 0.1); border-radius: 12px; }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php else: ?>
<!-- Tableau de bord connecté (SANS nav.php car header.php l'a déjà) -->
<div class="dashboard-container">
    <div class="container-fluid py-4 px-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h2 class="fw-bold mb-1">Bonjour, <?= htmlspecialchars($_SESSION['username']) ?></h2><p class="text-secondary mb-0">Voici votre tableau de bord personnalisé</p></div>
                    <div class="text-end"><small class="text-muted"><?= date('d F Y') ?></small></div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="/?filter=in_progress#dashboard-task-list" class="stat-card stat-primary text-decoration-none text-dark d-block <?= $filter === 'in_progress' ? 'stat-card-active' : '' ?>" aria-label="Afficher les tâches en cours">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div><h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Tâches en cours</h6><h2 class="fw-bold mb-0"><?= $stats['in_progress'] ?></h2></div>
                            <div class="stat-icon"><i class="bi bi-list-task"></i></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">Cliquer pour afficher les tâches en cours <i class="bi bi-arrow-right ms-1"></i></small>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/notifications.php" class="stat-card stat-success text-decoration-none text-dark d-block" aria-label="Afficher les notifications">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div><h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Notifications</h6><h2 class="fw-bold mb-0"><?= $stats['unread'] ?></h2></div>
                            <div class="stat-icon"><i class="bi bi-bell"></i></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">Cliquer pour afficher les notifications <i class="bi bi-arrow-right ms-1"></i></small>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/?filter=completed#dashboard-task-list" class="stat-card stat-info text-decoration-none text-dark d-block <?= $filter === 'completed' ? 'stat-card-active' : '' ?>" aria-label="Afficher les tâches terminées">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div><h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Terminées</h6><h2 class="fw-bold mb-0"><?= $stats['completed'] ?></h2></div>
                            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">Cliquer pour afficher les tâches terminées <i class="bi bi-arrow-right ms-1"></i></small>
                    </div>
                </a>
            </div>
        </div>

        <div class="row" id="dashboard-task-list">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-3 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="fw-bold mb-0">
                                <?php if ($filter === 'in_progress'): ?>Tâches en cours
                                <?php elseif ($filter === 'completed'): ?>Tâches terminées
                                <?php elseif ($filter === 'pending'): ?>Tâches en attente
                                <?php else: ?>Tâches récentes<?php endif; ?>
                            </h5>
                            <div class="d-flex gap-2">
                                <?php if ($filter): ?><a href="/#dashboard-task-list" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Tout afficher</a><?php endif; ?>
                                <a href="/my_task.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Voir toutes<i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase;"><th class="border-0 pb-3">Titre</th><th class="border-0 pb-3">Créateur</th><th class="border-0 pb-3">Créée le</th><th class="border-0 pb-3">Statut</th><th class="border-0 pb-3">Échéance</th><th class="border-0 pb-3">Assigné à</th></tr></thead>
                                <tbody>
                                    <?php if (empty($recentTasks)): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune tâche disponible.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recentTasks as $recentTask): ?>
                                            <tr class="task-row">
                                                <td><span class="fw-semibold"><?= htmlspecialchars($recentTask['title']) ?></span></td>
                                                <td>
                                                    <span class="text-secondary">
                                                        <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($recentTask['creator_username'] ?: 'Utilisateur inconnu') ?>
                                                    </span>
                                                </td>
                                                <td class="text-secondary text-nowrap">
                                                    <i class="bi bi-clock-history me-1"></i><?= $recentTask['created_at'] ? htmlspecialchars(date('d/m/Y à H:i:s', strtotime($recentTask['created_at']))) : 'Date inconnue' ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $dashboardStatusLabels = [
                                                        'pending' => 'En attente',
                                                        'in_progress' => 'En cours',
                                                        'completed' => 'Terminée'
                                                    ];
                                                    ?>
                                                    <span class="status-badge <?= $recentTask['status'] === 'completed' ? 'status-success' : ($recentTask['status'] === 'in_progress' ? 'status-warning' : 'status-danger') ?>">
                                                        <?= htmlspecialchars($dashboardStatusLabels[$recentTask['status']] ?? 'Inconnu') ?>
                                                    </span>
                                                </td>
                                                <td class="text-secondary"><?= $recentTask['due_date'] ? htmlspecialchars(date('d/m/Y', strtotime($recentTask['due_date']))) : 'Sans échéance' ?></td>
                                                <td><?= htmlspecialchars($recentTask['assigned_username'] ?? 'Tous') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container { background-color: #f8fafc; min-height: calc(100vh - 76px); }
.stat-card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease; border: 2px solid transparent; cursor: pointer; }
.stat-card:focus-visible { outline: 3px solid rgba(79,70,229,.35); outline-offset: 3px; }
.stat-card-active { border-color: #4f46e5; }
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-primary .stat-icon { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.stat-success .stat-icon { background: rgba(25, 135, 84, 0.1); color: #198754; }
.stat-info .stat-icon { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
.progress-stat { height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
.progress-bar-custom { height: 100%; background: #0d6efd; border-radius: 3px; transition: width 0.3s ease; }
.modern-card { border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.task-row { transition: background-color 0.2s ease; }
.task-row:hover { background-color: rgba(13, 110, 253, 0.03); }
.status-badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
.status-warning { background: rgba(255, 193, 7, 0.15); color: #997404; }
.status-danger { background: rgba(220, 53, 69, 0.15); color: #b02a37; }
.status-success { background: rgba(25, 135, 84, 0.15); color: #0f5132; }
</style>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'app/inc/footer.php'; ?>