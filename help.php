<?php 
session_start();
require_once 'app/inc/DB_connection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .help-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 4rem 0 3rem;
            margin-bottom: 3rem;
        }

        .help-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .help-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }

        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .help-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .help-icon.primary {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .help-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .help-icon.info {
            background: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        .accordion-item {
            border: none;
            margin-bottom: 1rem;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .accordion-button {
            font-weight: 600;
            padding: 1.25rem;
            background: white;
        }

        .accordion-button:not(.collapsed) {
            background: rgba(79, 70, 229, 0.05);
            color: var(--primary-color);
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(79, 70, 229, 0.2);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-4px);
        }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <!-- Header -->
    <div class="help-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <a href="/" class="back-link mb-3 d-inline-block">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                    <h1><i class="bi bi-question-circle me-3"></i>Centre d'aide</h1>
                    <p class="lead mb-0 opacity-90">Trouvez rapidement des réponses à vos questions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container pb-5">
        <!-- Quick Links (Sans Vidéo) -->
        <div class="row g-4 mb-5 justify-content-center">
            <div class="col-md-4">
                <div class="help-card p-4 text-center">
                    <div class="help-icon primary mx-auto">
                        <i class="bi bi-book"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Guide de démarrage</h5>
                    <p class="text-muted small mb-0">Apprenez les bases en quelques minutes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="help-card p-4 text-center">
                    <div class="help-icon warning mx-auto">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h5 class="fw-bold mb-2">FAQ</h5>
                    <p class="text-muted small mb-0">Questions fréquemment posées</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="help-card p-4 text-center">
                    <div class="help-icon info mx-auto">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Contact support</h5>
                    <p class="text-muted small mb-0">Besoin d'aide personnalisée ?</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h3 class="fw-bold mb-4">Questions fréquentes</h3>
                
                <div class="accordion" id="helpAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                <i class="bi bi-person-plus me-2"></i>Comment créer un compte ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                Pour créer un compte, cliquez sur le bouton "Créer un compte" sur la page d'accueil. Remplissez le formulaire avec votre nom d'utilisateur, email et mot de passe. Vous recevrez un email de confirmation pour activer votre compte.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                <i class="bi bi-list-task me-2"></i>Comment ajouter une tâche ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                Une fois connecté, allez dans "Mes tâches" et cliquez sur le bouton "Nouvelle tâche". Remplissez les détails comme le titre, la description, la date d'échéance et assignez-la si nécessaire.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                <i class="bi bi-bell me-2"></i>Comment fonctionnent les notifications ?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                Les notifications vous informent en temps réel des événements importants : nouvelle tâche assignée, rappel avant l'échéance, demande de preuve de fin, résultat de validation d'une preuve et demande de modification d'une tâche. Elles apparaissent dans la cloche en haut à droite et sur la page Notifications.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                <i class="bi bi-shield-check me-2"></i>Mes données sont-elles sécurisées ?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                Oui, absolument ! Nous utilisons le cryptage bcrypt pour les mots de passe et HTTPS pour toutes les communications. Vos données sont stockées de manière sécurisée et ne sont jamais partagées avec des tiers.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                <i class="bi bi-people me-2"></i>Comment collaborer avec mon équipe ?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                Vous pouvez assigner des tâches à d'autres utilisateurs, envoyer une preuve de fin avec un fichier justificatif et suivre l'avancement en temps réel. Invitez vos collègues à rejoindre la plateforme pour commencer à collaborer efficacement.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="mt-5 p-4 bg-light rounded-3 text-center">
                    <h5 class="fw-bold mb-2">Vous n'avez pas trouvé votre réponse ?</h5>
                    <p class="text-muted mb-3">Notre équipe de support est là pour vous aider</p>
                    <a href="/contact.php" class="btn btn-primary px-4">
                        <i class="bi bi-envelope me-2"></i>Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'app/inc/footer.php'; ?>