<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur n'est pas connecté, on le redirige vers la page d'accueil publique ou login
if(!isset($_SESSION['user_id'])) {
    header('Location: /index.php'); // Redirection vers la racine qui gère la landing page
    exit();
}

require_once 'inc/header.php'; 
?>

<!-- Tableau de bord pour utilisateurs connectés -->
<div class="dashboard-container">
    <div class="container-fluid py-4 px-4">
        <!-- En-tête du tableau de bord -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1 text-dark">Bonjour, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
                <p class="text-secondary mb-0">Voici un aperçu de vos activités aujourd'hui.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                    <?= date('d F Y') ?>
                </span>
            </div>
        </div>
        
        <!-- Cartes de statistiques -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card stat-primary h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Tâches en cours</h6>
                                <h2 class="fw-bold mb-0 display-6">12</h2>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-list-task fs-4"></i>
                            </div>
                        </div>
                        <div class="progress-stat mt-3">
                            <div class="progress-bar-custom" style="width: 65%"></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">65% des tâches terminées ce mois</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card stat-success h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Notifications</h6>
                                <h2 class="fw-bold mb-0 display-6">5</h2>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="bi bi-bell fs-4"></i>
                            </div>
                        </div>
                        <div class="progress-stat mt-3">
                            <div class="progress-bar-custom bg-success" style="width: 80%"></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">80% non lues</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card stat-info h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Performance</h6>
                                <h2 class="fw-bold mb-0 display-6">92%</h2>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                        </div>
                        <div class="progress-stat mt-3">
                            <div class="progress-bar-custom bg-info" style="width: 92%"></div>
                        </div>
                        <small class="text-secondary mt-2 d-block">Taux de réussite des tâches</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Section tâches récentes -->
        <div class="row">
            <div class="col-12">
                <div class="card modern-card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Tâches récentes</h5>
                        <a href="/my_task.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 hover-lift">
                            Voir toutes<i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <th class="border-0 pb-3 ps-2">Titre</th>
                                        <th class="border-0 pb-3">Statut</th>
                                        <th class="border-0 pb-3">Échéance</th>
                                        <th class="border-0 pb-3 pe-2">Assigné à</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="task-row">
                                        <td class="ps-2">
                                            <span class="fw-semibold text-dark">Mettre à jour le site web</span>
                                        </td>
                                        <td><span class="status-badge status-warning">En cours</span></td>
                                        <td class="text-secondary">15 janv. 2026</td>
                                        <td class="pe-2">John Doe</td>
                                    </tr>
                                    <tr class="task-row">
                                        <td class="ps-2">
                                            <span class="fw-semibold text-dark">Préparation rapport trimestriel</span>
                                        </td>
                                        <td><span class="status-badge status-danger">En attente</span></td>
                                        <td class="text-secondary">20 janv. 2026</td>
                                        <td class="pe-2">Marie Dubois</td>
                                    </tr>
                                    <tr class="task-row">
                                        <td class="ps-2">
                                            <span class="fw-semibold text-dark">Réunion équipe projet</span>
                                        </td>
                                        <td><span class="status-badge status-success">Terminé</span></td>
                                        <td class="text-secondary">10 janv. 2026</td>
                                        <td class="pe-2">Admin</td>
                                    </tr>
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
.dashboard-container {
    background-color: #f8fafc;
    min-height: calc(100vh - 76px); /* Ajustement hauteur navbar */
}

.stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-stat {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar-custom {
    height: 100%;
    background: #0d6efd;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.modern-card {
    border-radius: 16px;
    background: white;
}

.task-row {
    transition: background-color 0.2s ease;
}

.task-row:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-warning {
    background: rgba(255, 193, 7, 0.15);
    color: #997404;
}

.status-danger {
    background: rgba(220, 53, 69, 0.15);
    color: #b02a37;
}

.status-success {
    background: rgba(25, 135, 84, 0.15);
    color: #0f5132;
}

.hover-lift {
    transition: transform 0.2s ease;
}
.hover-lift:hover {
    transform: translateX(3px);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'inc/footer.php'; ?>