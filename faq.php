<?php 
session_start();
require_once 'app/inc/DB_connection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Gestion des Tâches</title>
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

        .faq-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 4rem 0 3rem;
            margin-bottom: 3rem;
        }

        .faq-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .search-box {
            max-width: 600px;
            margin: 2rem auto 0;
        }

        .search-box .form-control {
            border-radius: 50px;
            padding: 1rem 1.5rem;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .search-box .form-control:focus {
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.2);
        }

        .faq-category {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .faq-category-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            padding: 1.5rem 2rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }

        .faq-category-header h3 {
            margin: 0;
            font-weight: 700;
            color: var(--primary-color);
        }

        .faq-item {
            border-bottom: 1px solid #e2e8f0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            padding: 1.5rem 2rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .faq-question:hover {
            background: rgba(79, 70, 229, 0.03);
        }

        .faq-answer {
            padding: 0 2rem 1.5rem;
            display: none;
            color: #64748b;
            line-height: 1.7;
        }

        .faq-answer.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .faq-toggle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(79, 70, 229, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .faq-item.active .faq-toggle {
            background: var(--primary-color);
            color: white;
            transform: rotate(180deg);
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

        .still-need-help {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php require_once 'app/inc/nav.php'; ?>

    <!-- Header -->
    <div class="faq-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <a href="/" class="back-link mb-3 d-inline-block">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                    <h1><i class="bi bi-chat-dots me-3"></i>FAQ</h1>
                    <p class="lead mb-0 opacity-90">Trouvez rapidement des réponses à vos questions</p>
                    
                    <div class="search-box">
                        <input type="text" class="form-control" id="faqSearch" placeholder="Rechercher dans la FAQ...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container pb-5">
        <!-- Catégorie: Compte -->
        <div class="faq-category">
            <div class="faq-category-header">
                <h3><i class="bi bi-person me-2"></i>Compte & Inscription</h3>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment créer un compte ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Cliquez sur "Créer un compte" sur la page d'accueil, remplissez le formulaire avec votre nom d'utilisateur, email et mot de passe. Un email de confirmation vous sera envoyé pour activer votre compte.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>J'ai oublié mon mot de passe, que faire ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Cliquez sur "Mot de passe oublié" sur la page de connexion. Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Puis-je modifier mon nom d'utilisateur ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Actuellement, le nom d'utilisateur ne peut pas être modifié après l'inscription. Si vous avez besoin de changer votre nom d'utilisateur, veuillez contacter notre support.
                </div>
            </div>
        </div>

        <!-- Catégorie: Tâches -->
        <div class="faq-category">
            <div class="faq-category-header">
                <h3><i class="bi bi-list-task me-2"></i>Gestion des Tâches</h3>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment ajouter une nouvelle tâche ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Allez dans "Mes tâches" et cliquez sur le bouton "Nouvelle tâche". Remplissez les détails requis : titre, description, date d'échéance et priorité. Vous pouvez aussi assigner la tâche à un membre de l'équipe.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment modifier le statut d'une tâche ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Cliquez sur la tâche concernée pour ouvrir ses détails. Vous pouvez changer le statut depuis le menu déroulant : "En attente", "En cours", "Terminé" ou "Annulé".
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Puis-je supprimer une tâche ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Oui, vous pouvez supprimer vos propres tâches. Attention : cette action est irréversible. Assurez-vous de bien vouloir supprimer la tâche avant de confirmer.
                </div>
            </div>
        </div>

        <!-- Catégorie: Notifications -->
        <div class="faq-category">
            <div class="faq-category-header">
                <h3><i class="bi bi-bell me-2"></i>Notifications</h3>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment fonctionnent les notifications ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Les notifications vous informent en temps réel des événements importants : nouvelle tâche assignée, rappel avant l'échéance, demande de preuve de fin, résultat de validation et demande de modification d'une tâche. Elles apparaissent dans la cloche en haut à droite et sur la page Notifications.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Puis-je désactiver les notifications ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Oui, vous pouvez activer ou désactiver les notifications de tâches dans les paramètres de votre profil. Cette préférence s'applique aux nouvelles tâches assignées et aux rappels d'échéance ; les notifications liées à la validation d'une preuve de fin restent toujours envoyées. Vous pouvez aussi autoriser ou bloquer les notifications de votre navigateur dans le profil.
                </div>
            </div>
        </div>

        <!-- Catégorie: Sécurité -->
        <div class="faq-category">
            <div class="faq-category-header">
                <h3><i class="bi bi-shield-check me-2"></i>Sécurité & Confidentialité</h3>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Mes données sont-elles sécurisées ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Absolument ! Nous utilisons le cryptage bcrypt pour les mots de passe et HTTPS pour toutes les communications. Vos données sont stockées de manière sécurisée et ne sont jamais partagées avec des tiers.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Qui peut voir mes tâches ?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-answer">
                    Par défaut, seules vous et les personnes à qui vous assignez des tâches peuvent les voir. Les administrateurs ont également accès à toutes les tâches pour la gestion de la plateforme.
                </div>
            </div>
        </div>

        <!-- Still need help -->
        <div class="still-need-help mt-5">
            <i class="bi bi-headset display-4 text-primary mb-3"></i>
            <h4 class="fw-bold mb-2">Vous avez encore des questions ?</h4>
            <p class="text-muted mb-4">Notre équipe de support est prête à vous aider</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="/contact.php" class="btn btn-primary px-4">
                    <i class="bi bi-envelope me-2"></i>Nous contacter
                </a>
                <a href="/help.php" class="btn btn-outline-primary px-4">
                    <i class="bi bi-book me-2"></i>Centre d'aide
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const isActive = faqItem.classList.contains('active');
            
            // Fermer tous les autres
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-answer').classList.remove('active');
            });
            
            // Ouvrir celui-ci si n'était pas actif
            if (!isActive) {
                faqItem.classList.add('active');
                answer.classList.add('active');
            }
        }

        // Recherche dans la FAQ
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
    <?php require_once 'app/inc/footer.php'; ?>