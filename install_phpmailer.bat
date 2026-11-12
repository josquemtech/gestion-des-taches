@echo off
title INSTALLATION PHPMAILER - Gestion des Tâches
color 0a

echo.
echo *****************************************************
echo *      INSTALLATION AUTOMATIQUE DE PHPMAILER        *
echo *      Projet: GESTION_DES_TACHES                  *
echo *****************************************************
echo.

:: Créer les dossiers nécessaires
echo 🔧 Création de la structure des dossiers...
if not exist "lib" mkdir "lib"
if not exist "lib\PHPMailer" mkdir "lib\PHPMailer"
if not exist "lib\PHPMailer\src" mkdir "lib\PHPMailer\src"

:: Télécharger PHPMailer directement depuis GitHub
echo ⬇️ Téléchargement de PHPMailer depuis GitHub...
powershell -Command "$progressPreference = 'silentlyContinue'; Invoke-WebRequest -Uri 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' -OutFile 'phpmailer_temp.zip'"

:: Décompresser avec PowerShell
echo 📦 Décompression de l'archive...
powershell -Command "Expand-Archive -Path 'phpmailer_temp.zip' -DestinationPath 'phpmailer_temp' -Force"

:: Copier les fichiers nécessaires
echo ✅ Copie des fichiers essentiels...
xcopy /s /y "phpmailer_temp\PHPMailer-master\src\*" "lib\PHPMailer\src\" >nul

:: Nettoyer les fichiers temporaires
echo 🧹 Nettoyage des fichiers temporaires...
del "phpmailer_temp.zip" >nul 2>&1
rd /s /q "phpmailer_temp" >nul 2>&1

:: Vérifier l'installation
echo.
echo 🔍 Vérification de l'installation...
if exist "lib\PHPMailer\src\PHPMailer.php" (
    echo ✅ Fichier PHPMailer.php trouvé
) else (
    echo ❌ Erreur: PHPMailer.php non trouvé
    goto error
)

if exist "lib\PHPMailer\src\SMTP.php" (
    echo ✅ Fichier SMTP.php trouvé
) else (
    echo ❌ Erreur: SMTP.php non trouvé
    goto error
)

if exist "lib\PHPMailer\src\Exception.php" (
    echo ✅ Fichier Exception.php trouvé
) else (
    echo ❌ Erreur: Exception.php non trouvé
    goto error
)

echo.
echo *****************************************************
echo *       INSTALLATION RÉUSSIE !                      *
echo *                                                   *
echo *   ✅ PHPMailer installé correctement             *
echo *   ✅ Chemins configurés pour XAMPP               *
echo *   ✅ Prêt pour l'envoi d'emails                  *
echo *                                                   *
echo *   Prochaines étapes :                           *
echo *   1. Redémarrez Apache dans XAMPP                *
echo *   2. Accédez à :                                *
echo *      http://localhost/gestion_task/app/inc/mailer.php *
echo *   3. Si vous voyez "success", tout fonctionne ! *
echo *****************************************************
echo.

goto end

:error
echo.
echo *****************************************************
echo *       ERREUR D'INSTALLATION                       *
echo *                                                   *
echo *   ❌ Les fichiers n'ont pas été copiés            *
echo *   ✅ Solution manuelle :                          *
echo *      1. Téléchargez https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
echo *      2. Décompressez le ZIP
echo *      3. Copiez le contenu du dossier "src" vers : *
echo *         C:\xampp\htdocs\gestion_task\lib\PHPMailer\src\
echo *      4. Redémarrez Apache                         *
echo *****************************************************
echo.

:end
pause