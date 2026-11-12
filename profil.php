<?php
// Page de profil historique : on redirige vers la page de profil actuelle
// pour éviter d'afficher deux documents HTML complets (double header).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Location: /profile.php', true, 302);
exit;
