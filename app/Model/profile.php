<?php 
session_start();
require_once 'app/inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

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
    die("Erreur: " . $e->getMessage());
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logique de mise à jour du profil ici
    $success = "Profil mis à jour avec succès !";
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
        .profile-header { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); color: white; padding: 4rem 0 6rem; margin-bottom: -3rem; }
        .profile-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 2rem; position: relative; z-index: 10; }
        .avatar-lg { width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); object-fit: cover; }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <div class="profile-header text-center">
        <div class="container">
            <img src="<?= htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : '/app/img/user.svg') ?>" alt="Avatar" class="avatar-lg mb-3" onerror="this.src='/app/img/user.svg'">
            <h2 class="fw-bold"><?= htmlspecialchars($user['username']) ?></h2>
            <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
            <span class="badge bg-light text-dark mt-2 px-3 py-2"><?= ucfirst($user['role']) ?></span>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <h4 class="fw-bold mb-4"><i class="bi bi-gear me-2 text-primary"></i>Paramètres du compte</h4>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success rounded-3"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom d'utilisateur</label>
                                <input type="text" class="form-control rounded-3" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control rounded-3" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="fw-bold mb-3">Changer le mot de passe</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" class="form-control rounded-3" name="new_password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirmer</label>
                                <input type="password" class="form-control rounded-3" name="confirm_password">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="bi bi-check-lg me-2"></i>Sauvegarder les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>