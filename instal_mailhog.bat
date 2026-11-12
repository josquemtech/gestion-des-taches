#!/bin/bash
set -e

# Script d'installation de MailHog pour GESTION_DES_TACHES
# Compatible Linux et macOS

echo ""
echo "*****************************************************"
echo "*      INSTALLATION AUTOMATIQUE DE MAILHOG         *"
echo "*      Projet: GESTION_DES_TACHES                 *"
echo "*****************************************************"
echo ""

# Déterminer l'architecture
ARCH=$(uname -m)
OS=$(uname -s)

echo "🔧 Système détecté: $OS $ARCH"

# Télécharger MailHog selon l'architecture
if [ "$OS" = "Darwin" ]; then
    echo "⬇️  Téléchargement de MailHog pour macOS..."
    curl -L "https://github.com/mailhog/MailHog/releases/latest/download/MailHog_darwin_amd64" -o MailHog
elif [ "$OS" = "Linux" ]; then
    if [ "$ARCH" = "x86_64" ]; then
        echo "⬇️  Téléchargement de MailHog pour Linux x86_64..."
        curl -L "https://github.com/mailhog/MailHog/releases/latest/download/MailHog_linux_amd64" -o MailHog
    elif [ "$ARCH" = "aarch64" ] || [ "$ARCH" = "arm64" ]; then
        echo "⬇️  Téléchargement de MailHog pour Linux ARM64..."
        curl -L "https://github.com/mailhog/MailHog/releases/latest/download/MailHog_linux_arm64" -o MailHog
    else
        echo "❌ Architecture non supportée: $ARCH"
        exit 1
    fi
else
    echo "❌ Système non supporté: $OS"
    exit 1
fi

# Vérifier le téléchargement
if [ ! -f MailHog ]; then
    echo "❌ Erreur: Le téléchargement de MailHog a échoué"
    exit 1
fi

echo "✅ MailHog téléchargé avec succès"

# Configurer les permissions
echo "🔑 Configuration des permissions..."
chmod +x MailHog

# Démarrer MailHog en arrière-plan
echo "🚀 Démarrage de MailHog en arrière-plan..."
./MailHog > /dev/null 2>&1 &
MAILHOG_PID=$!

# Attendre que MailHog soit prêt
echo "⏳ Attente du démarrage de MailHog..."
sleep 3

# Vérifier si MailHog est en cours d'exécution
if ps -p $MAILHOG_PID > /dev/null; then
    echo ""
    echo "*****************************************************"
    echo "*       MAILHOG DEMARRE AVEC SUCCES                *"
    echo "*                                                   *"
    echo "*   Interface web: http://localhost:8025           *"
    echo "*   SMTP server:   localhost:1025                  *"
    echo "*   PID:           $MAILHOG_PID                     *"
    echo "*                                                   *"
    echo "*   Le service fonctionnera en arrière-plan        *"
    echo "*   jusqu'à l'arrêt du script.                     *"
    echo "*****************************************************"
    echo ""
else
    echo "❌ Erreur: Impossible de démarrer MailHog"
    exit 1
fi

# Installer les dépendances PHP
echo "📥 Installation des dépendances PHP..."
if [ -f composer.phar ]; then
    php composer.phar install
elif command -v composer &> /dev/null; then
    composer install
else
    echo "⚠️  Composer non trouvé. Veuillez installer Composer manuellement."
    echo "   Instructions: https://getcomposer.org/download/"
fi

echo ""
echo "*****************************************************"
echo "*       PROCHAINES ETAPES                          *"
echo "*                                                   *"
echo "*   1. Importez la base de données:                *"
echo "*      - Ouvrez PHPMyAdmin                          *"
echo "*      - Créez la base 'gestion_des_taches'         *"
echo "*      - Importez 'gestion_des_taches_db.sql'       *"
echo "*                                                   *"
echo "*   2. Démarrez le serveur PHP:                    *"
echo "*      php -S localhost:8000 -t .                  *"
echo "*                                                   *"
echo "*   3. Accédez à l'application:                    *"
echo "*      http://localhost:8000/app/Model/signup.php   *"
echo "*                                                   *"
echo "*   4. Interface MailHog:                          *"
echo "*      http://localhost:8025                        *"
echo "*****************************************************"
echo ""

# Instructions d'arrêt
echo "ℹ️  Pour arrêter MailHog, exécutez:"
echo "    kill $MAILHOG_PID"
echo ""

# Garder le script en cours d'exécution
trap 'echo "🛑 Arrêt de MailHog..."; kill $MAILHOG_PID 2>/dev/null; exit 0' INT TERM
echo "⏳ MailHog est en cours d'exécution. Appuyez sur CTRL+C pour arrêter."
wait $MAILHOG_PID 2>/dev/null