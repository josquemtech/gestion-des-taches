#!/bin/bash
echo "Installation de MailHog pour Linux/Mac..."
echo "Téléchargement de MailHog..."

# Détection de l'architecture
ARCH=$(uname -m)
case $ARCH in
    x86_64) 
        URL="https://github.com/mailhog/MailHog/releases/latest/download/MailHog_linux_amd64"
        ;;
    arm64|aarch64) 
        URL="https://github.com/mailhog/MailHog/releases/latest/download/MailHog_linux_arm"
        ;;
    *)
        echo "Architecture non supportée : $ARCH"
        exit 1
        ;;
esac

curl -L $URL -o mailhog
chmod +x mailhog

echo ""
echo "Installation terminée !"
echo ""
echo "Pour démarrer MailHog :"
echo "  ./mailhog"
echo ""
echo "Interface web : http://localhost:8025"
echo "Serveur SMTP : localhost:1025"
echo ""