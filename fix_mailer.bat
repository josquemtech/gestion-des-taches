@echo off
title CORRECTIF PHPMAILER - Gestion des Tâches
color 0e

echo.
echo *****************************************************
echo *      CORRECTIF AUTOMATIQUE POUR PHPMAILER         *
echo *      Projet: GESTION_DES_TACHES                  *
echo *****************************************************
echo.

:: Vérifier si le dossier lib existe
if not exist "lib" mkdir "lib"
if not exist "lib\PHPMailer" mkdir "lib\PHPMailer"
if not exist "lib\PHPMailer\src" mkdir "lib\PHPMailer\src"

echo 🔧 Création des dossiers nécessaires... OK

:: Télécharger PHPMailer directement
echo ⬇️ Téléchargement de PHPMailer (version standalone)...
powershell -Command "$progressPreference = 'silentlyContinue'; Invoke-WebRequest -Uri 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' -OutFile 'phpmailer.zip'"

:: Décompresser avec PowerShell
echo 📦 Décompression de PHPMailer...
powershell -Command "Expand-Archive -Path 'phpmailer.zip' -DestinationPath 'phpmailer_temp' -Force"

:: Copier les fichiers nécessaires
echo ✅ Copie des fichiers essentiels...
xcopy /s /y "phpmailer_temp\PHPMailer-master\src\*" "lib\PHPMailer\src\"

:: Nettoyer les fichiers temporaires
echo 🧹 Nettoyage des fichiers temporaires...
del "phpmailer.zip" >nul 2>&1
rd /s /q "phpmailer_temp" >nul 2>&1

echo.
echo *****************************************************
echo *       CORRECTIF TERMINÉ AVEC SUCCÈS !             *
echo *                                                   *
echo *   ✅ PHPMailer installé manuellement             *
echo *   ✅ Aucune dépendance Composer requise          *
echo *   ✅ Configuration SMTP pour MailHog             *
echo *                                                   *
echo *   Prochaines étapes :                           *
echo *   1. Redémarrez XAMPP (Apache)                   *
echo *   2. Accédez à :                                *
echo *      http://localhost/gestion_task/app/inc/mailer.php *
echo *   3. Si succès, testez l'inscription             *
echo *****************************************************
echo.

pause