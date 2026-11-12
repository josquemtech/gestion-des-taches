<?php 
session_start();
require_once 'app/inc/DB_connection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confidentialité - Gestion des Tâches</title>
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
        .privacy-icon { font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <a href="/" class="back-link"><i class="bi bi-arrow-left"></i> Retour</a>
            <h1 class="fw-bold"><i class="bi bi-shield-check me-3"></i>Politique de Confidentialité</h1>
            <p class="lead mb-0 opacity-90">Comment nous protégeons vos données</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="legal-card">
            <div class="text-center mb-5">
                <i class="bi bi-lock-fill privacy-icon"></i>
                <p class="text-muted">Votre vie privée est importante pour nous. Cette politique explique quelles données nous collectons et comment nous les utilisons.</p>
            </div>

            <div class="legal-section">
                <h4>1. Données collectées</h4>
                <p>Lors de votre inscription et de l'utilisation du service, nous pouvons collecter les informations suivantes :</p>
                <ul>
                    <li><strong>Informations personnelles :</strong> Nom d'utilisateur, adresse email, mot de passe (crypté).</li>
                    <li><strong>Données d'utilisation :</strong> Tâches créées, commentaires, dates de connexion.</li>
                    <li><strong>Données techniques :</strong> Adresse IP, type de navigateur, système d'exploitation.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h4>2. Utilisation des données</h4>
                <p>Nous utilisons vos données uniquement pour :</p>
                <ul>
                    <li>Fournir et améliorer nos services.</li>
                    <li>Vous envoyer des notifications importantes liées à vos tâches.</li>
                    <li>Assurer la sécurité de votre compte.</li>
                    <li>Respecter nos obligations légales.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h4>3. Partage des données</h4>
                <p>Nous ne vendons jamais vos données personnelles. Vos informations ne sont partagées qu'avec :</p>
                <ul>
                    <li>Les membres de l'équipe auxquels vous assignez des tâches.</li>
                    <li>Nos prestataires techniques hébergeant la base de données (sous stricte confidentialité).</li>
                </ul>
            </div>

            <div class="legal-section">
                <h4>4. Sécurité</h4>
                <p>Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos données contre l'accès non autorisé, la modification ou la destruction. Les mots de passe sont hachés avec l'algorithme bcrypt.</p>
            </div>

            <div class="legal-section">
                <h4>5. Vos droits</h4>
                <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> Vous pouvez demander une copie de vos données.</li>
                    <li><strong>Droit de rectification :</strong> Vous pouvez modifier vos informations via votre profil.</li>
                    <li><strong>Droit à l'oubli :</strong> Vous pouvez demander la suppression de votre compte et de toutes les données associées.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h4>6. Cookies</h4>
                <p>Nous utilisons des cookies essentiels pour maintenir votre session active et assurer le bon fonctionnement de la plateforme. Aucun cookie publicitaire n'est utilisé.</p>
            </div>
            
            <div class="alert alert-light border mt-4">
                <small class="text-muted">Contact DPO : <a href="mailto:josquem.k@gmail.com">josquem.k@gmail.com</a></small>
            </div>
        </div>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>