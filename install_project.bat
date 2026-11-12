@echo off
title INSTALLATION COMPLÈTE - Gestion des Tâches
color 0a

echo.
echo *****************************************************
echo *      INSTALLATION AUTOMATIQUE DU PROJET           *
echo *      GESTION DES TÂCHES                           *
echo *****************************************************
echo.

:: Vérifier PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: PHP n'est pas installé ou non accessible
    echo Veuillez installer XAMPP depuis https://www.apachefriends.org/fr/index.html
    pause
    exit /b 1
)

:: Vérifier MySQL
echo Vérification de MySQL...
mysql -u root -e "SHOW DATABASES;" >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ⚠️  MySQL ne semble pas démarré
    echo    Démarrez XAMPP et activez les services Apache + MySQL
    echo    Ensuite relancez ce script
    echo.
    pause
    exit /b 1
)

:: Créer la structure des dossiers
echo 🔧 Création de la structure des dossiers...
if not exist "app" mkdir "app"
if not exist "app\Model" mkdir "app\Model"
if not exist "app\css" mkdir "app\css"
if not exist "app\inc" mkdir "app\inc"
if not exist "app\img" mkdir "app\img"
if not exist "lib" mkdir "lib"
if not exist "lib\PHPMailer" mkdir "lib\PHPMailer"
if not exist "lib\PHPMailer\src" mkdir "lib\PHPMailer\src"

:: Télécharger PHPMailer
echo ⬇️ Téléchargement de PHPMailer...
powershell -Command "$progressPreference = 'silentlyContinue'; Invoke-WebRequest -Uri 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' -OutFile 'phpmailer.zip'"

:: Décompresser PHPMailer
echo 📦 Décompression de PHPMailer...
powershell -Command "Expand-Archive -Path 'phpmailer.zip' -DestinationPath 'phpmailer_temp' -Force"

:: Copier les fichiers PHPMailer
echo ✅ Copie des fichiers PHPMailer...
xcopy /s /y "phpmailer_temp\PHPMailer-master\src\*" "lib\PHPMailer\src\" >nul

echo.
echo *****************************************************
echo *       ÉTAPES MANUELLES REQUISES                    *
echo *****************************************************
echo.
echo 1. IMPORTER LA BASE DE DONNÉES :
echo    - Ouvrez http://localhost/phpmyadmin
echo    - Créez une base nommée "gestion_des_taches"
echo    - Importez le fichier "gestion_des_taches_db.sql"
echo.
echo 2. CONFIGURER LES IDENTIFIANTS :
echo    - Exécutez : http://localhost/gestion_des_taches/reset_admin.php
echo    - Identifiants par défaut :
echo      Admin   : admin / admin123
echo      User    : user / user123
echo.
echo 3. (OPTIONNEL) INSTALLER MAILHOG POUR LES EMAILS :
echo    - Téléchargez : https://github.com/mailhog/MailHog/releases/latest/download/MailHog_windows_amd64.exe
echo    - Renommez en "MailHog.exe"
echo    - Placez dans ce dossier
echo    - Exécutez MailHog.exe
echo    - Accédez à http://localhost:8025
echo.
echo *****************************************************
echo *       PROCHAINES ÉTAPES                           *
echo *****************************************************
echo.
echo ✅ STRUCTURE DU PROJET CRÉÉE AVEC SUCCÈS !
echo ✅ PHPMAILER INSTALLÉ
echo ⚠️ BASE DE DONNÉES À IMPORTER MANUELLEMENT
echo.
echo Après avoir importé la base de données :
echo - Accédez à : http://localhost/gestion_des_taches/
echo - Identifiants : admin / admin123
echo.
echo *****************************************************

:: Nettoyer les fichiers temporaires
del "phpmailer.zip" >nul 2>&1
rd /s /q "phpmailer_temp" >nul 2>&1

pause