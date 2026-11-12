<?php 
session_start();
require_once 'app/inc/DB_connection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; }
        
        .page-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 4rem 0 3rem;
            margin-bottom: 3rem;
        }
        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 3rem;
            margin-bottom: 2rem;
        }
        .feature-box {
            padding: 1.5rem;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.05);
            height: 100%;
            transition: transform 0.2s;
        }
        .feature-box:hover { transform: translateY(-5px); }
        .back-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .back-link:hover { color: white; }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <a href="/" class="back-link"><i class="bi bi-arrow-left"></i> Retour</a>
            <h1 class="fw-bold"><i class="bi bi-info-circle me-3"></i>À propos de nous</h1>
            <p class="lead mb-0 opacity-90">Notre mission et notre vision</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="content-card">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-3" style="color: var(--primary-color);">Notre Mission</h2>
                    <p class="text-muted lead" style="line-height: 1.8;">
                        Chez <strong>Gestion des Tâches</strong>, nous croyons que l'organisation est la clé de la réussite. 
                        Notre plateforme a été conçue pour simplifier la gestion de projets complexes et permettre aux équipes 
                        de se concentrer sur ce qui compte vraiment : l'innovation et la collaboration.
                    </p>
                    <p class="text-muted" style="line-height: 1.8;">
                        Fondée par @JOSQUEM, notre solution vise à offrir une expérience utilisateur fluide, 
                        sécurisée et efficace pour les professionnels modernes.
                    </p>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-rocket-takeoff display-1" style="color: var(--primary-color); opacity: 0.2;"></i>
                </div>
            </div>

            <hr class="my-5">

            <h3 class="fw-bold text-center mb-4">Nos Valeurs</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="bi bi-lightning-charge fs-1 text-primary mb-3"></i>
                        <h5 class="fw-bold">Efficacité</h5>
                        <p class="small text-muted mb-0">Des outils rapides pour gagner du temps au quotidien.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="bi bi-shield-lock fs-1 text-primary mb-3"></i>
                        <h5 class="fw-bold">Sécurité</h5>
                        <p class="small text-muted mb-0">Vos données sont protégées avec les meilleurs standards.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="bi bi-heart fs-1 text-primary mb-3"></i>
                        <h5 class="fw-bold">Simplicité</h5>
                        <p class="small text-muted mb-0">Une interface intuitive qui ne nécessite pas de formation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>