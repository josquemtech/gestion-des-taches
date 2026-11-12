<?php 
session_start();
require_once 'app/inc/DB_connection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CGU - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #4f46e5; --bg-gradient-start: #667eea; --bg-gradient-end: #764ba2; }
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%); color: white; padding: 4rem 0 3rem; margin-bottom: 3rem; }
        .legal-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 3rem; margin-bottom: 2rem; }
        .legal-section { margin-bottom: 2.5rem; }
        .legal-section h4 { color: var(--primary-color); font-weight: 700; margin-bottom: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; }
        .legal-section p, .legal-section li { color: #64748b; line-height: 1.7; }
        .back-link { color: rgba(255,255,255,0.9); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
        .back-link:hover { color: white; }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <a href="/" class="back-link"><i class="bi bi-arrow-left"></i> Retour</a>
            <h1 class="fw-bold"><i class="bi bi-file-text me-3"></i>Conditions Générales d'Utilisation</h1>
            <p class="lead mb-0 opacity-90">Dernière mise à jour : <?= date('d F Y') ?></p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="legal-card">
            <div class="legal-section">
                <h4>1. Acceptation des conditions</h4>
                <p>En accédant et en utilisant la plateforme "Gestion des Tâches", vous acceptez pleinement et sans réserve les présentes conditions générales d'utilisation. Si vous n'acceptez pas ces conditions, vous devez cesser d'utiliser le service.</p>
            </div>

            <div class="legal-section">
                <h4>2. Description du service</h4>
                <p>Le service permet aux utilisateurs de créer, gérer et suivre des tâches professionnelles ou personnelles. Il inclut des fonctionnalités de collaboration, de notification et de suivi de performance.</p>
            </div>

            <div class="legal-section">
                <h4>3. Compte utilisateur</h4>
                <p>Pour accéder à certaines fonctionnalités, vous devez créer un compte. Vous êtes responsable de la confidentialité de vos identifiants et de toutes les activités effectuées sous votre compte.</p>
                <ul>
                    <li>Vous devez fournir des informations exactes lors de l'inscription.</li>
                    <li>Vous devez notifier immédiatement toute utilisation non autorisée de votre compte.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h4>4. Propriété intellectuelle</h4>
                <p>Tout le contenu présent sur la plateforme (logos, textes, code source, design) est la propriété exclusive de @JOSQUEM sauf mention contraire. Toute reproduction ou distribution non autorisée est interdite.</p>
            </div>

            <div class="legal-section">
                <h4>5. Limitation de responsabilité</h4>
                <p>Le service est fourni "tel quel". Nous ne garantissons pas que le service sera ininterrompu ou exempt d'erreurs. Nous ne sommes pas responsables des pertes de données ou des dommages indirects résultant de l'utilisation du service.</p>
            </div>

            <div class="legal-section">
                <h4>6. Modification des conditions</h4>
                <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications entrent en vigueur dès leur publication sur la plateforme.</p>
            </div>
            
            <div class="alert alert-light border mt-4">
                <small class="text-muted">Pour toute question concernant ces conditions, contactez-nous à <a href="mailto:josquem.k@gmail.com">josquem.k@gmail.com</a>.</small>
            </div>
        </div>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>