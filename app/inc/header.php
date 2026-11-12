<?php
// app/inc/header.php
// Évite un double rendu si le fichier est inclus plusieurs fois.
if (!empty($GLOBALS['headerRendered'])) {
    return;
}
$GLOBALS['headerRendered'] = true;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/DB_connection.php';

// Personnalisation optionnelle depuis une page, avant l'inclusion :
//   $pageTitle = 'Mon profil - Gestion des Tâches';
//   $pageStyles = '<style>...</style>';
$pageTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Gestion des Tâches';
$pageStyles = $pageStyles ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #4f46e5; --grad-start: #667eea; --grad-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f8fafc; color: #1e293b; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar Moderne */
        .navbar-modern { 
            background: linear-gradient(135deg, var(--grad-start), var(--grad-end)); 
            padding: 0.75rem 0; 
        }
        .navbar-brand, .nav-link { color: white !important; font-weight: 500; }
        .nav-link:hover { background: rgba(255,255,255,0.1); border-radius: 8px; }
        
        /* Badges & Dropdowns */
        .notification-badge { 
            position: absolute; top: -5px; right: -5px; 
            background: #ef4444; color: white; font-size: 0.7rem; 
            padding: 2px 6px; border-radius: 10px; 
        }
        .dropdown-menu { border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; }
        
        /* Avatar Navbar */
        .user-avatar { 
            width: 32px; height: 32px; border-radius: 50%; 
            border: 2px solid rgba(255,255,255,0.3); object-fit: cover; 
        }
        .avatar-initials {
            width: 32px; height: 32px; border-radius: 50%;
            background: white; color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 0.9rem;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        main { flex: 1; }
    </style>
    <?= $pageStyles ?>
</head>
<body>
    <?php require __DIR__ . '/nav.php'; ?>
    <main>
