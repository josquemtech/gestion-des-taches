<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement automatique de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Configure et retourne une instance PHPMailer
 * @return PHPMailer|null
 */
function getMailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'localhost'; // À changer pour votre serveur SMTP (ex: smtp.gmail.com)
        $mail->Port       = 1025;        // Port standard pour MailHog, 587 pour TLS, 465 pour SSL
        $mail->SMTPAuth   = false;       // Mettre à true si authentification requise
        $mail->CharSet    = 'UTF-8';
        
        // Identifiants (à décommenter si SMTPAuth est true)
        // $mail->Username = 'votre_email@domaine.com';
        // $mail->Password = 'votre_mot_de_passe';
        
        // Expéditeur
        $mail->setFrom('no-reply@gestion-taches.com', 'Gestion des Tâches');
        $mail->addReplyTo('josquem.k@gmail.com', 'Support Technique');
        
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return null;
    }
}

/**
 * Envoie un email simple
 * @param string $to Destinataire
 * @param string $subject Sujet
 * @param string $body Corps du message (HTML autorisé)
 * @return bool
 */
function sendEmail($to, $subject, $body) {
    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Test direct si le fichier est appelé directement
if (basename($_SERVER['PHP_SELF']) === 'mailer.php') {
    header('Content-Type: application/json');
    $mail = getMailer();
    echo json_encode([
        'status' => $mail ? 'success' : 'error',
        'message' => $mail ? 'PHPMailer configuré avec succès' : 'Erreur de configuration',
        'config' => [
            'host' => 'localhost',
            'port' => 1025,
            'web_interface' => 'http://localhost:8025'
        ]
    ]);
}