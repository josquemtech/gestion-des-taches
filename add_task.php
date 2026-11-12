<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
require_once 'app/inc/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <a href="/my_task.php" class="text-decoration-none text-muted small fw-semibold mb-3 d-inline-block hover-lift">
                        <i class="bi bi-arrow-left me-1"></i> Retour aux tâches
                    </a>
                    <h2 class="fw-bold text-dark mb-4">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Nouvelle tâche
                    </h2>
                </div>
                
                <div class="card-body p-4">
                    <?php if(isset($_SESSION['error_add_task'])): ?>
                        <div class="alert alert-danger rounded-3 mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error_add_task']); unset($_SESSION['error_add_task']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- CORRECTION : Action pointe vers le script de traitement -->
                    <form method="POST" action="/app/Model/add-task.php">
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold text-secondary">Titre de la tâche</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="title" name="title" required placeholder="Ex: Mettre à jour la homepage">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold text-secondary">Description</label>
                            <textarea class="form-control rounded-3" id="description" name="description" rows="4" placeholder="Détails de la tâche..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="due_date" class="form-label fw-semibold text-secondary">Date d'échéance</label>
                            <input type="date" class="form-control rounded-3" id="due_date" name="due_date">
                        </div>

                        <div class="mb-4">
                            <label for="assigned_to" class="form-label fw-semibold text-secondary">Destinataire</label>
                            <select class="form-select rounded-3" id="assigned_to" name="assigned_to" required>
                                <option value="all">Tous les utilisateurs</option>
                                <?php
                                require_once __DIR__ . '/app/inc/DB_connection.php';
                                $usersStmt = $pdo->query("SELECT id, username FROM users WHERE role = 'user' ORDER BY username");
                                foreach ($usersStmt->fetchAll() as $userOption):
                                ?>
                                    <option value="<?= (int) $userOption['id'] ?>">
                                        <?= htmlspecialchars($userOption['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold shadow-sm hover-lift">
                            <i class="bi bi-check-lg me-2"></i>Créer la tâche
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift { transition: transform 0.2s ease; }
    .hover-lift:hover { transform: translateY(-2px); }
    .form-control:focus, .form-select:focus { 
        border-color: #4f46e5; 
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
    }
    /* Harmonisation avec le thème violet */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }
</style>

<?php require_once 'app/inc/footer.php'; ?>