<?php 
session_start();
require_once 'app/inc/DB_connection.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Tous les champs sont requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide";
    } else {
        // Ici vous pouvez ajouter l'envoi d'email ou sauvegarde en base
        $success = "Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Gestion des Tâches</title>
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

        .contact-header {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 4rem 0 3rem;
            margin-bottom: 3rem;
        }

        .contact-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .contact-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2.5rem;
        }

        .contact-info-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2rem;
            height: 100%;
        }

        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .contact-method:hover {
            background: rgba(79, 70, 229, 0.05);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .contact-icon.email {
            background: rgba(234, 67, 53, 0.1);
            color: #EA4335;
        }

        .contact-icon.github {
            background: rgba(51, 51, 51, 0.1);
            color: #333;
        }

        .contact-icon.whatsapp {
            background: rgba(37, 211, 102, 0.1);
            color: #25D366;
        }

        .contact-icon.linkedin {
            background: rgba(10, 102, 194, 0.1);
            color: #0A66C2;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
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
    <div class="contact-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <a href="/" class="back-link mb-3 d-inline-block">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                    <h1><i class="bi bi-envelope me-3"></i>Contactez-nous</h1>
                    <p class="lead mb-0 opacity-90">Nous sommes là pour vous aider</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container pb-5">
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="contact-card">
                    <h3 class="fw-bold mb-4">Envoyez-nous un message</h3>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-custom">
                            <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success alert-custom">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Nom complet</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="subject" class="form-label fw-semibold">Sujet</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Choisir un sujet</option>
                                <option value="general">Question générale</option>
                                <option value="technical">Problème technique</option>
                                <option value="billing">Facturation</option>
                                <option value="feature">Suggestion de fonctionnalité</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="mt-3">
                            <label for="message" class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      placeholder="Décrivez votre demande en détail..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit w-100 mt-4">
                            <i class="bi bi-send me-2"></i>Envoyer le message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-5">
                <div class="contact-info-card">
                    <h4 class="fw-bold mb-4">Informations de contact</h4>
                    
                    <div class="contact-method">
                        <div class="contact-icon email">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email</h6>
                            <a href="mailto:josquem.k@gmail.com" class="text-decoration-none" style="color: var(--primary-color);">
                                josquem.k@gmail.com
                            </a>
                            <p class="small text-muted mb-0 mt-1">Réponse sous 24-48h</p>
                        </div>
                    </div>

                    <div class="contact-method">
                        <div class="contact-icon github">
                            <i class="bi bi-github"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">GitHub</h6>
                            <a href="https://github.com/josquemtech" target="_blank" class="text-decoration-none" style="color: var(--primary-color);">
                                @josquemtech
                            </a>
                            <p class="small text-muted mb-0 mt-1">Pour les contributions</p>
                        </div>
                    </div>

                    <div class="contact-method">
                        <div class="contact-icon whatsapp">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">WhatsApp</h6>
                            <a href="https://wa.me/+22897608323" target="_blank" class="text-decoration-none" style="color: var(--primary-color);">
                                +22897608323
                            </a>
                            <p class="small text-muted mb-0 mt-1">Lun-Ven, 9h-18h</p>
                        </div>
                    </div>
                    <hr class="my-4">

                    <div class="text-center">
                        <h6 class="fw-bold mb-2">Heures d'ouverture</h6>
                        <p class="text-muted mb-1">Lundi - Vendredi : 9h00 - 18h00</p>
                        <p class="text-muted mb-0">Weekend : Fermé</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'app/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>