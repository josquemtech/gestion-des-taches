<?php
// app/inc/nav.php
// Garde dédiée : header.php peut être inclus puis nav.php à nouveau sans doublon.
if (!empty($GLOBALS['navRendered'])) {
    return;
}
$GLOBALS['navRendered'] = true;

// 1. Gestion sécurisée de la session pour éviter les erreurs "headers already sent"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Connexion DB sécurisée
if (!isset($pdo)) {
    require_once __DIR__ . '/DB_connection.php';
}

// 3. Calcul des notifications non lues
$notificationCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $notificationCount = $result ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        // Ignorer l'erreur si la table n'existe pas encore ou est vide
        $notificationCount = 0;
    }
}

// 4. Vérification robuste de l'avatar pour la navbar
$hasAvatar = isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../..' . $_SESSION['avatar']);
$headerNotifications = [];
if (isset($_SESSION['user_id'])) {
    try {
        $headerStmt = $pdo->prepare(
            "SELECT id, message, is_read, created_at
             FROM notifications WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC LIMIT 5"
        );
        $headerStmt->execute(['user_id' => $_SESSION['user_id']]);
        $headerNotifications = $headerStmt->fetchAll();
    } catch (PDOException $e) {
        $headerNotifications = [];
    }
}
?>

<?php
// Détection fiable de la page courante (la racine "/" doit compter comme index.php).
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentPage = substr($requestPath, -1) === '/'
    ? 'index.php'
    : basename($requestPath);
if ($currentPage === '' || $currentPage === '.') {
    $currentPage = 'index.php';
}
?>

<!-- En-tête principal -->
<header class="site-header">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between py-3">
            <a class="site-brand" href="/">
                <span class="brand-icon"><i class="bi bi-check2-square"></i></span>
                <span>GESTION DES TÂCHES</span>
            </a>

            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle-btn" id="themeToggle" type="button" aria-label="Activer le mode sombre" title="Changer le thème">
                    <i class="bi bi-moon-stars-fill" id="themeIcon" aria-hidden="true"></i>
                </button>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown position-relative header-action">
                        <button class="header-icon-btn" type="button" aria-expanded="false" aria-label="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if($notificationCount > 0): ?>
                                <span class="notification-badge"><?= $notificationCount ?></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-2 notification-menu">
                            <li><h6 class="dropdown-header ps-2">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ($headerNotifications as $headerNotification): ?>
                            <li>
                                <a class="dropdown-item notification-dropdown-item <?= empty($headerNotification['is_read']) ? 'fw-semibold' : '' ?>" href="/notifications.php?notification_id=<?= (int) $headerNotification['id'] ?>">
                                    <div class="small"><?= htmlspecialchars(mb_strimwidth($headerNotification['message'], 0, 72, '...')) ?></div>
                                    <small class="text-muted"><?= date('d/m/Y à H:i', strtotime($headerNotification['created_at'])) ?></small>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <?php if (empty($headerNotifications)): ?>
                            <li><span class="dropdown-item-text text-muted small">Aucune notification</span></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-center text-primary fw-semibold" href="/notifications.php">Voir toutes les notifications</a></li>
                        </ul>
                    </div>

                    <div class="dropdown header-action">
                        <button class="header-user-btn" type="button" aria-expanded="false">
                            <?php if ($hasAvatar): ?>
                                <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="Utilisateur" class="user-avatar">
                            <?php else: ?>
                                <span class="user-avatar-initials"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></span>
                            <?php endif; ?>
                            <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?></span>
                            <i class="bi bi-chevron-down d-none d-md-inline"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm header-dropdown">
                            <li><a class="dropdown-item" href="/profile.php"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                            <li><a class="dropdown-item" href="/my_task.php"><i class="bi bi-list-task me-2"></i>Mes tâches</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/add_task.php"><i class="bi bi-plus-circle me-2"></i>Créer une tâche</a></li>
                            <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                            <li><a class="dropdown-item" href="/task.php"><i class="bi bi-kanban me-2"></i>Gestion des tâches</a></li>
                            <li><a class="dropdown-item" href="/user.php"><i class="bi bi-people me-2"></i>Gestion des utilisateurs</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/app/Model/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="header-login-link" href="/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Connexion</a>
                    <a class="header-signup-link" href="/signup.php"><i class="bi bi-person-plus me-1"></i>Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Navigation placée sous le header -->
<nav class="navbar navbar-expand-lg navbar-modern shadow-sm">
    <div class="container-fluid px-4">
        <button class="navbar-toggler border-0 ms-auto text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="/"><i class="bi bi-house-door me-2"></i>Accueil</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'my_task.php' ? 'active' : '' ?>" href="/my_task.php"><i class="bi bi-list-check me-2"></i>Mes tâches</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'notifications.php' ? 'active' : '' ?>" href="/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'trash.php' ? 'active' : '' ?>" href="/trash.php"><i class="bi bi-trash3 me-2"></i>Corbeille</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'add_task.php' ? 'active' : '' ?>" href="/add_task.php"><i class="bi bi-plus-circle me-2"></i>Créer une tâche</a></li>
                    <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'task.php' ? 'active' : '' ?>" href="/task.php"><i class="bi bi-kanban me-2"></i>Gestion des tâches</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'user.php' ? 'active' : '' ?>" href="/user.php"><i class="bi bi-people me-2"></i>Utilisateurs</a></li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-auto">
                        <a class="site-nav-link logout-nav-link" href="/app/Model/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="/"><i class="bi bi-house-door me-2"></i>Accueil</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="/about.php"><i class="bi bi-info-circle me-2"></i>À propos</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'help.php' ? 'active' : '' ?>" href="/help.php"><i class="bi bi-question-circle me-2"></i>Aide</a></li>
                    <li class="nav-item"><a class="site-nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="/contact.php"><i class="bi bi-envelope me-2"></i>Contact</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
(function () {
    const savedTheme = localStorage.getItem('appTheme');
    const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = savedTheme === 'light' || savedTheme === 'dark'
        ? savedTheme
        : (systemDark ? 'dark' : 'light');

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-app-theme', theme);
        document.body?.classList.toggle('dark-theme', theme === 'dark');

        const toggle = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');
        if (!toggle || !icon) return;

        const darkMode = theme === 'dark';
        icon.className = darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        toggle.setAttribute('aria-label', darkMode ? 'Activer le mode clair' : 'Activer le mode sombre');
        toggle.setAttribute('title', darkMode ? 'Mode clair' : 'Mode sombre');
    }

    applyTheme(initialTheme);
    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(initialTheme);
        const toggle = document.getElementById('themeToggle');
        if (!toggle) return;

        toggle.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('appTheme', nextTheme);
            applyTheme(nextTheme);
        });
    });
})();

// Repli des menus déroulants pour toutes les pages, même si Bootstrap charge tardivement.
(function () {
    function closeDropdowns(exceptMenu) {
        document.querySelectorAll('.header-action .dropdown-menu.show').forEach(function (menu) {
            if (menu !== exceptMenu) menu.classList.remove('show');
        });
        document.querySelectorAll('.header-action button[aria-expanded="true"]').forEach(function (button) {
            if (!exceptMenu || !button.parentElement.contains(exceptMenu)) button.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.header-action button');
        if (!button) {
            closeDropdowns(null);
            return;
        }

        const menu = button.parentElement.querySelector('.dropdown-menu');
        if (!menu) return;

        event.preventDefault();
        event.stopPropagation();
        const shouldOpen = !menu.classList.contains('show');
        closeDropdowns(menu);
        menu.classList.toggle('show', shouldOpen);
        button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeDropdowns(null);
    });
})();
</script>

<!-- Styles CSS intégrés pour garantir l'affichage correct -->
<style>
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-textfield-decoration-container,
    input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    html[data-app-theme="light"] {
        --app-bg: #f5f7fb;
        --app-surface: #ffffff;
        --app-surface-soft: #eef2ff;
        --app-text: #172033;
        --app-muted: #64748b;
        --app-border: #dbe3ef;
        --app-primary: #4f46e5;
        --app-header-start: #5b6ee1;
        --app-header-end: #7250a3;
        --app-nav-bg: #ffffff;
        --app-card-shadow: 0 8px 24px rgba(79, 70, 229, 0.08);
    }
    html[data-app-theme="dark"] {
        --app-bg: #090e1a;
        --app-surface: #131c2e;
        --app-surface-soft: #1b2740;
        --app-text: #edf2ff;
        --app-muted: #a8b4c9;
        --app-border: #2a3852;
        --app-primary: #8b9cff;
        --app-header-start: #111827;
        --app-header-end: #25345b;
        --app-nav-bg: #111827;
        --app-card-shadow: 0 12px 30px rgba(0, 0, 0, 0.32);
    }
    /* Aucun panneau blanc en mode sombre, y compris les classes Bootstrap utilitaires. */
    html[data-app-theme="dark"] .bg-white,
    html[data-app-theme="dark"] .bg-light,
    html[data-app-theme="dark"] .card-header.bg-white,
    html[data-app-theme="dark"] .table-light,
    html[data-app-theme="dark"] .table-secondary,
    html[data-app-theme="dark"] .accordion-button:not(.collapsed),
    html[data-app-theme="dark"] .modal-header,
    html[data-app-theme="dark"] .modal-footer,
    html[data-app-theme="dark"] .offcanvas {
        background-color: var(--app-surface) !important;
        color: var(--app-text) !important;
        border-color: var(--app-border) !important;
    }
    html[data-app-theme="dark"] .trash-empty-state {
        background-color: var(--app-surface) !important;
        color: var(--app-text) !important;
        border: 1px solid var(--app-border);
    }
    html[data-app-theme="dark"] .header-signup-link,
    html[data-app-theme="dark"] .header-login-link,
    html[data-app-theme="dark"] .user-avatar-initials,
    html[data-app-theme="dark"] .avatar-initials {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
        border-color: var(--app-border) !important;
    }
    html[data-app-theme="dark"] .alert-light,
    html[data-app-theme="dark"] .badge.bg-light {
        background-color: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
    html[data-app-theme="light"] body,
    html[data-app-theme="light"] main,
    html[data-app-theme="light"] .dashboard-container {
        background: var(--app-bg) !important;
        color: var(--app-text) !important;
    }
    html[data-app-theme="dark"] body,
    html[data-app-theme="dark"] main,
    html[data-app-theme="dark"] .dashboard-container {
        background: var(--app-bg) !important;
        color: var(--app-text) !important;
    }
    html[data-app-theme="light"] .site-header {
        background: linear-gradient(135deg, var(--app-header-start), var(--app-header-end));
    }
    html[data-app-theme="dark"] .site-header {
        background: linear-gradient(135deg, var(--app-header-start), var(--app-header-end));
        border-bottom: 1px solid var(--app-border);
    }
    html[data-app-theme="dark"] .navbar-modern {
        background: var(--app-nav-bg);
        border-bottom-color: var(--app-border);
    }
    html[data-app-theme="light"] .site-nav-link {
        color: #5a6678 !important;
    }
    html[data-app-theme="dark"] .new-task-button {
        background: var(--app-primary) !important;
        color: #09101f !important;
        border: 1px solid #aab6ff !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.28);
    }
    html[data-app-theme="dark"] .site-nav-link {
        color: var(--app-muted) !important;
    }
    html[data-app-theme="dark"] .site-nav-link:hover,
    html[data-app-theme="dark"] .site-nav-link.active {
        color: #b9c5ff !important;
        background: var(--app-surface-soft);
        border-bottom-color: #8b9cff;
    }
    html[data-app-theme="dark"] .card,
    html[data-app-theme="dark"] .modern-card,
    html[data-app-theme="dark"] .task-card,
    html[data-app-theme="dark"] .stat-card,
    html[data-app-theme="dark"] .profile-card,
    html[data-app-theme="dark"] .feature-card,
    html[data-app-theme="dark"] .list-group-item,
    html[data-app-theme="dark"] .modal-content,
    html[data-app-theme="dark"] .dropdown-menu {
        background: var(--app-surface) !important;
        color: var(--app-text) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-card-shadow) !important;
    }
    html[data-app-theme="light"] .card,
    html[data-app-theme="light"] .modern-card,
    html[data-app-theme="light"] .task-card,
    html[data-app-theme="light"] .stat-card,
    html[data-app-theme="light"] .profile-card,
    html[data-app-theme="light"] .feature-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-card-shadow) !important;
    }
    .site-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .site-brand {
        color: white !important;
        text-decoration: none;
        font-weight: 800;
        letter-spacing: .02em;
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .brand-icon {
        width: 42px; height: 42px; border-radius: 12px;
        background: rgba(255,255,255,.16);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
    }
    .navbar-modern {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 0;
        min-height: 54px;
    }
    .navbar-modern .navbar-nav { gap: .15rem; }
    .site-nav-link {
        color: #64748b !important;
        text-decoration: none;
        font-weight: 600;
        padding: .95rem 1rem !important;
        border-bottom: 3px solid transparent;
        display: block;
        transition: .2s ease;
    }
    .site-nav-link:hover { color: #4f46e5 !important; background: #f8fafc; }
    .site-nav-link.active { color: #4f46e5 !important; border-bottom-color: #4f46e5; background: #f8faff; }
    .logout-nav-link { color: #dc2626 !important; }
    .logout-nav-link:hover { color: #b91c1c !important; background: #fef2f2; border-bottom-color: #dc2626; }
    .header-icon-btn, .header-user-btn, .header-login-link, .header-signup-link {
        border: 0;
        color: white !important;
        text-decoration: none;
    }
    .header-icon-btn {
        width: 42px; height: 42px; border-radius: 50%;
        background: rgba(255,255,255,.14);
        position: relative;
    }
    .theme-toggle-btn {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 50%;
        background: rgba(255,255,255,.14);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        transition: .2s ease;
    }
    .theme-toggle-btn:hover {
        background: rgba(255,255,255,.25);
        transform: translateY(-1px);
    }
    html[data-bs-theme="dark"] body,
    html[data-bs-theme="dark"] .dashboard-container,
    html[data-bs-theme="dark"] .profile-header {
        background-color: #0f172a !important;
        color: #e2e8f0 !important;
    }
    html[data-bs-theme="dark"] .navbar-modern,
    html[data-bs-theme="dark"] .card,
    html[data-bs-theme="dark"] .stat-card,
    html[data-bs-theme="dark"] .modern-card,
    html[data-bs-theme="dark"] .task-card,
    html[data-bs-theme="dark"] .profile-card,
    html[data-bs-theme="dark"] .list-group-item,
    html[data-bs-theme="dark"] .table {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
    }
    html[data-bs-theme="dark"] .navbar-modern,
    html[data-bs-theme="dark"] .card,
    html[data-bs-theme="dark"] .profile-card,
    html[data-bs-theme="dark"] .list-group-item {
        border-color: #334155 !important;
    }
    html[data-bs-theme="dark"] .text-muted,
    html[data-bs-theme="dark"] .text-secondary,
    html[data-bs-theme="dark"] .text-dark {
        color: #94a3b8 !important;
    }
    html[data-bs-theme="dark"] .table-hover > tbody > tr:hover > * {
        color: #f8fafc;
        background-color: rgba(148,163,184,.08);
    }
    html[data-app-theme="dark"] h1,
    html[data-app-theme="dark"] h2,
    html[data-app-theme="dark"] h3,
    html[data-app-theme="dark"] h4,
    html[data-app-theme="dark"] h5,
    html[data-app-theme="dark"] h6,
    html[data-app-theme="dark"] .card-title,
    html[data-app-theme="dark"] .fw-bold {
        color: var(--app-text) !important;
    }
    html[data-app-theme="dark"] .text-muted,
    html[data-app-theme="dark"] .text-secondary,
    html[data-app-theme="dark"] small {
        color: var(--app-muted) !important;
    }
    html[data-app-theme="dark"] .table,
    html[data-app-theme="dark"] .table td,
    html[data-app-theme="dark"] .table th {
        color: var(--app-text) !important;
        border-color: var(--app-border) !important;
    }
    html[data-app-theme="dark"] .form-control,
    html[data-app-theme="dark"] .form-select,
    html[data-app-theme="dark"] input,
    html[data-app-theme="dark"] textarea,
    html[data-app-theme="dark"] select {
        background-color: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
        border-color: var(--app-border) !important;
    }
    html[data-app-theme="dark"] .modern-footer {
        background: linear-gradient(135deg, #080d17, #141f35) !important;
        border-top: 1px solid var(--app-border);
    }
    html[data-app-theme="dark"] .notification-item.unread {
        background-color: var(--app-surface-soft) !important;
    }
    html[data-bs-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-select,
    html[data-bs-theme="dark"] .dropdown-menu {
        background-color: #1e293b;
        color: #e2e8f0;
        border-color: #334155;
    }
    .header-user-btn {
        min-height: 42px;
        border-radius: 24px;
        background: rgba(255,255,255,.14);
        padding: .4rem .65rem;
        display: flex; align-items: center; gap: .55rem;
    }
    .header-login-link { font-weight: 600; padding: .55rem .75rem; }
    .header-signup-link {
        font-weight: 700; padding: .55rem .85rem;
        border-radius: 999px; background: white; color: #4f46e5 !important;
    }
    
    /* Badges & Dropdowns */
    .notification-badge {
        position: absolute; top: -5px; right: -5px;
        background: #ef4444; color: white; font-size: 0.7rem;
        padding: 2px 6px; border-radius: 10px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .dropdown-menu {
        border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px;
        z-index: 1080;
    }
    
    /* Avatars Navbar */
    .user-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3); object-fit: cover;
    }
    .user-avatar-initials {
        width: 32px; height: 32px; border-radius: 50%;
        background: white; color: #4f46e5;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 0.9rem;
        border: 2px solid rgba(255,255,255,0.3);
    }
    .header-action { position: relative; }
    /* Tous les menus du header s'ouvrent vers la gauche pour ne pas sortir de l'écran. */
    .header-action .dropdown-menu {
        top: calc(100% + .35rem);
        right: 0;
        left: auto;
        max-width: calc(100vw - 2rem);
        max-height: calc(100vh - 140px);
        overflow-y: auto;
    }
    .header-action .dropdown-menu.notification-menu {
        width: min(340px, calc(100vw - 2rem));
    }
    .notification-menu .dropdown-item {
        white-space: normal;
        overflow-wrap: anywhere;
        line-height: 1.35;
        padding-top: .55rem;
        padding-bottom: .55rem;
    }
    .notification-menu .dropdown-item .small {
        display: block;
        white-space: normal;
        overflow-wrap: anywhere;
    }
    .notification-menu .dropdown-item small {
        display: block;
        margin-top: .15rem;
    }
    .header-dropdown { min-width: 220px; }
    @media (max-width: 991px) {
        .site-nav-link { border-bottom: 0; border-left: 3px solid transparent; }
        .site-nav-link.active { border-left-color: #4f46e5; border-bottom-color: transparent; }
        .navbar-modern { padding: .4rem 0; }
    }
    @media (max-width: 575px) {
        .site-brand span:last-child { font-size: .85rem; }
        .brand-icon { width: 36px; height: 36px; }
        .header-signup-link { display: none; }
    }
</style>