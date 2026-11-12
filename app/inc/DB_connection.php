<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_des_taches');
define('DB_USER', 'root');
define('DB_PASS', ''); // Par défaut vide sous Laragon/XAMPP

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // En production, ne jamais afficher les détails d'erreur
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Une erreur technique est survenue. Veuillez contacter l'administrateur.");
}
?>