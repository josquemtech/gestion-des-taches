<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/DB_connection.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$role = (string) ($_SESSION['role'] ?? 'user');

/**
 * Normalise un texte : minuscules, sans accents, sans ponctuation.
 * Permet de reconnaître « tâche », « tache » et « TÂCHE » de la même façon.
 */
function josNormalize(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'œ' => 'oe', 'æ' => 'ae', 'ß' => 'ss',
    ]);
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '';
    return trim(preg_replace('/\s+/', ' ', $text) ?? '');
}

// GET : renvoie l'historique récent de la conversation (rechargement de la page).
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare(
            "SELECT sender, message, created_at
             FROM jos_messages
             WHERE user_id = ?
             ORDER BY id DESC
             LIMIT 40"
        );
        $stmt->execute([$userId]);
        $history = array_reverse($stmt->fetchAll());
        echo json_encode(['success' => true, 'messages' => $history], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log('Erreur historique JOS IA: ' . $e->getMessage());
        echo json_encode(['success' => true, 'messages' => []], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$message = is_array($input) ? trim((string) ($input['message'] ?? '')) : '';
if ($message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Écrivez un message.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// On limite la longueur pour éviter les messages démesurés.
if (mb_strlen($message) > 1000) {
    $message = mb_substr($message, 0, 1000);
}

$normalized = josNormalize($message);

/**
 * Règles ordonnées : la première règle ayant le meilleur score l'emporte.
 * Les règles spécifiques sont placées avant la règle générique « tâche ».
 */
$rules = [
    [
        'keywords' => ['corbeille', 'restaur', 'supprimer definitivement', 'suppression definitive', 'recuperer une tache'],
        'answer' => "La corbeille est accessible depuis le menu principal. Vous y retrouvez les tâches supprimées : vous pouvez les restaurer ou les supprimer définitivement.",
    ],
    [
        'keywords' => ['supprimer mon compte', 'desactiver mon compte', 'supprimer un utilisateur', 'supprimer un membre'],
        'answer' => $role === 'admin'
            ? "Pour supprimer un compte, ouvrez « Gestion des utilisateurs », cliquez sur l'icône de suppression de la ligne concernée puis confirmez. Vous ne pouvez pas supprimer votre propre compte."
            : "Seul un administrateur peut supprimer un compte utilisateur. Contactez-le si vous souhaitez que votre compte soit retiré.",
    ],
    [
        'keywords' => ['supprim', 'effac', 'retirer', 'enlever'],
        'answer' => "Pour supprimer une tâche, ouvrez « Mes tâches », repérez la tâche concernée et cliquez sur l'icône de corbeille. Elle part dans la corbeille, d'où vous pourrez la restaurer. Un employé peut aussi demander une suppression depuis la tâche ; l'administrateur doit alors valider.",
    ],
    [
        'keywords' => ['statut', 'en cours', 'termine', 'terminer', 'valider', 'approuv', 'rejet', 'progression', 'avancement'],
        'answer' => $role === 'admin'
            ? "Dans « Gestion des tâches », utilisez le sélecteur de statut d'une tâche pour la passer en « En attente », « En cours » ou « Terminée ». Vous pouvez aussi approuver ou rejeter une preuve de fin envoyée par un employé."
            : "Vous ne pouvez pas modifier librement le statut : envoyez une « preuve de fin » depuis la tâche. L'administrateur la validera et la tâche passera en « Terminée ».",
    ],
    [
        'keywords' => ['assign', 'attribu', 'destinataire', 'confier', 'employe', 'collaborateur'],
        'answer' => "Lors de la création d'une tâche, choisissez le destinataire dans le champ « Assigner à ». La tâche apparaîtra ensuite dans son espace « Mes tâches ». Seul l'administrateur peut assigner une tâche à un autre utilisateur.",
    ],
    [
        'keywords' => ['mot de passe oublie', 'oublie mon mot de passe', 'reinitialis', 'recuperer mon compte', 'lien de reinitialisation'],
        'answer' => "Sur la page de connexion, cliquez sur « Mot de passe oublié ? », saisissez votre email : un lien de réinitialisation vous sera fourni. Ouvrez-le, choisissez un nouveau mot de passe d'au moins 8 caractères puis validez.",
    ],
    [
        'keywords' => ['modifi', 'modifier', 'changer', 'mettre a jour', 'mise a jour', 'corriger', 'editer'],
        'answer' => "Pour modifier une tâche, ouvrez-la depuis « Mes tâches ». Un employé ne peut pas la modifier directement : il envoie une demande de modification que l'administrateur approuve ou rejette. L'administrateur, lui, peut modifier directement titre, description et échéance.",
    ],
    [
        'keywords' => ['preuve', 'justificatif', 'fichier', 'photo', 'document', 'piece jointe'],
        'answer' => "Sur une tâche non terminée, cliquez sur « Envoyer une preuve de fin », ajoutez un message et éventuellement un fichier (image, PDF ou document). L'administrateur reçoit la preuve et peut valider la tâche ou la rejeter avec un commentaire.",
    ],
    [
        'keywords' => ['notification', 'rappel', 'cloche', 'alerte', 'pas notifie', 'pas de notification'],
        'answer' => "La cloche en haut de l'écran affiche vos notifications. Cliquez dessus pour voir le détail ; la page « Notifications » permet de tout consulter et de marquer comme lu. Des rappels sont générés pendant les trois jours précédant une échéance, et vous pouvez activer/désactiver les notifications dans « Mon profil ».",
    ],
    [
        'keywords' => ['export', 'pdf', 'historique', 'imprimer', 'telecharger'],
        'answer' => $role === 'admin'
            ? "Depuis « Gestion des tâches » ou « Mes tâches », cliquez sur « Exporter PDF » pour télécharger l'historique complet des tâches avec leur statut et leurs dates."
            : "Seul l'administrateur peut exporter l'historique complet des tâches au format PDF. Contactez-le si vous avez besoin d'un export.",
    ],
    [
        'keywords' => ['ajouter un utilisateur', 'creer un utilisateur', 'nouvel utilisateur', 'gestion des utilisateurs', 'gerer les utilisateurs', 'ajouter un membre', 'role', 'administrateur'],
        'answer' => $role === 'admin'
            ? "Ouvrez « Gestion des utilisateurs » dans le menu, cliquez sur « Ajouter un utilisateur », renseignez le nom, l'email, le mot de passe (8 caractères minimum) et le rôle (Administrateur ou Employé), puis validez."
            : "La gestion des comptes est réservée à l'administrateur. Il peut créer, modifier ou supprimer des utilisateurs depuis « Gestion des utilisateurs ».",
    ],
    [
        'keywords' => ['profil', 'avatar', 'mon compte', 'mes informations', 'changer email', 'changer mon email', 'mot de passe'],
        'answer' => "Ouvrez le menu avec votre nom/avatar puis « Mon profil ». Vous pouvez y modifier votre email, votre mot de passe et vos préférences de notifications. Le changement de mot de passe demande votre mot de passe actuel.",
    ],
    [
        'keywords' => ['connecter', 'connexion', 'se connecter', 'inscription', 'inscrire', 'creer un compte', 's inscrire', 'deconnexion', 'deconnecter'],
        'answer' => "Pour vous connecter, utilisez votre email et votre mot de passe sur la page « Connexion ». Sans compte, cliquez sur « Inscription » pour en créer un. Pour vous déconnecter, ouvrez le menu de votre profil puis « Déconnexion ».",
    ],
    [
        'keywords' => ['bonjour', 'salut', 'hello', 'bonsoir', 'coucou', 'hey'],
        'answer' => "Bonjour ! Je suis JOS IA, votre guide sur le site. Je peux vous aider sur les tâches, les notifications, les preuves de fin, la corbeille, le profil, les utilisateurs ou l'export PDF.",
    ],
    [
        'keywords' => ['merci', 'super', 'parfait', 'top', 'genial', 'impeccable'],
        'answer' => "Avec plaisir ! Si vous avez une autre question sur les tâches ou votre compte, je suis là.",
    ],
    [
        'keywords' => ['qui es tu', 'qui etes vous', 'que peux tu', 'que sais tu', 'ton role', 'tu es qui', 'aide', 'aider', 'fonctionnalite', 'comment ca marche', 'utiliser le site'],
        'answer' => "Je suis JOS IA, l'assistant intégré. Je réponds aux questions courantes : créer, modifier, supprimer ou assigner une tâche, changer un statut, envoyer une preuve de fin, consulter les notifications, utiliser la corbeille, gérer votre profil ou exporter en PDF.",
    ],
    [
        'keywords' => ['creer', 'creation', 'nouvelle tache', 'ajouter une tache', 'ajout tache', 'tache'],
        'answer' => "Pour créer une tâche, cliquez sur « Créer une tâche » dans le menu. Renseignez le titre, la description, l'échéance et le destinataire, puis validez. La tâche apparaît dans « Mes tâches » et son destinataire est notifié.",
    ],
];

$bestAnswer = null;
$bestScore = 0;

foreach ($rules as $rule) {
    $score = 0;
    foreach ($rule['keywords'] as $keyword) {
        if ($keyword !== '' && str_contains($normalized, $keyword)) {
            $score++;
        }
    }
    // En cas d'égalité, la première règle (la plus spécifique) est conservée.
    if ($score > $bestScore) {
        $bestScore = $score;
        $bestAnswer = $rule['answer'];
    }
}

if ($bestAnswer === null) {
    $bestAnswer = "Je n'ai pas bien compris votre demande. Je peux vous guider pour : créer, modifier, supprimer ou assigner une tâche, changer un statut, envoyer une preuve de fin, consulter les notifications, utiliser la corbeille, gérer votre profil, gérer les utilisateurs ou exporter en PDF.";
}

// Enregistre l'échange (sans bloquer la réponse en cas d'erreur de base).
try {
    $pdo->beginTransaction();
    $insert = $pdo->prepare(
        "INSERT INTO jos_messages (user_id, sender, message) VALUES (?, ?, ?)"
    );
    $insert->execute([$userId, 'user', $message]);
    $insert->execute([$userId, 'assistant', $bestAnswer]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur JOS IA: ' . $e->getMessage());
}

echo json_encode(['success' => true, 'message' => $bestAnswer], JSON_UNESCAPED_UNICODE);
