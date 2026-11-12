<?php 
session_start();
require_once 'app/inc/DB_connection.php';

// Sécurité : Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Récupération des données utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: /login.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur technique: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; }
        
        .profile-header { 
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); 
            color: white; 
            padding: 4rem 0 6rem; 
            margin-bottom: -3rem; 
        }
        .profile-card { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            padding: 2.5rem; 
            position: relative; 
            z-index: 10; 
        }
        
        /* STYLE AVATAR CORRIGÉ */
        .avatar-lg { 
            width: 120px; height: 120px; border-radius: 50%; 
            border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            object-fit: cover; background-color: #e2e8f0;
        }
        .avatar-initials-lg {
            width: 120px; height: 120px; border-radius: 50%;
            background: white; color: var(--primary-color);
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 3.5rem;
            border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .form-control {
            border-radius: 10px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/app/inc/nav.php'; ?>

    <!-- En-tête Profil -->
    <div class="profile-header text-center">
        <div class="container">
            <div class="mb-3">
                <?php 
                // VÉRIFICATION ROBUSTE DE L'AVATAR
                $avatarPath = !empty($user['avatar']) ? $user['avatar'] : '/app/img/user.svg';
                $avatarFile = realpath(__DIR__ . '/' . ltrim($avatarPath, '/'));
                $hasProfileAvatar = $avatarFile !== false && is_file($avatarFile);
                ?>
                
                <?php if ($hasProfileAvatar): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-lg">
                <?php else: ?>
                    <!-- Fallback élégant avec initiales -->
                    <div class="avatar-initials-lg">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 class="fw-bold"><?= htmlspecialchars($user['username']) ?></h2>
            <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
            <span class="badge bg-light text-dark mt-2 px-3 py-2 rounded-pill">
                <i class="bi bi-shield-check me-1"></i><?= ucfirst($user['role']) ?>
            </span>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <h4 class="fw-bold mb-4"><i class="bi bi-gear me-2 text-primary"></i>Paramètres du compte</h4>
                    
                    <!-- Affichage des messages d'erreur/succès -->
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-3">
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show rounded-3">
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de mise à jour -->
                    <form method="POST" action="/app/Model/update-profile.php">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Nom d'utilisateur</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>

                        <hr class="my-4 border-secondary-subtle">

                        <h5 class="fw-bold mb-3 text-primary">Notifications</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notification_enabled"
                                   name="notification_enabled" <?= !empty($user['notification_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="notification_enabled">
                                Recevoir les notifications de nouvelles tâches et les rappels d'échéance
                            </label>
                            <div class="text-muted small mt-1">
                                Les notifications liées à la validation des preuves de fin restent toujours envoyées. Vous pouvez également autoriser les notifications de votre navigateur ci-dessous.
                            </div>
                        </div>
                        <button type="button" id="enable-browser-notifications" class="btn btn-outline-primary mt-3 rounded-pill px-3">
                            <i class="bi bi-bell me-2"></i>Autoriser les notifications du navigateur
                        </button>
                        
                        <hr class="my-4 border-secondary-subtle">
                        
                        <h5 class="fw-bold mb-3 text-primary">Sécurité</h5>
                        <p class="text-muted small mb-3">Laissez ces champs vides si vous ne souhaitez pas changer de mot de passe.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mot de passe actuel</label>
                                <input type="password" class="form-control" name="current_password" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Minimum 8 caractères">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Confirmer</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Répétez le nouveau">
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <a href="/index.php" class="btn btn-light rounded-pill px-4 me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check-lg me-2"></i>Sauvegarder les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('enable-browser-notifications')?.addEventListener('click', async function () {
        if (!('Notification' in window)) {
            alert("Ce navigateur ne prend pas en charge les notifications.");
            return;
        }
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            document.getElementById('notification_enabled').checked = true;
            new Notification('Notifications activées', {
                body: 'Vous recevrez les notifications de nouvelles tâches et les rappels d’échéance.'
            });
        } else {
            alert("Autorisation refusée. Activez-la depuis les paramètres du navigateur.");
        }
    });
    </script>
    <?php require_once 'app/inc/footer.php'; ?>
