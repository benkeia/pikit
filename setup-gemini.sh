#!/bin/bash

# Script de configuration Gemini API
# Pour utiliser Gemini avec Claude Code

echo "=== Configuration Gemini API ==="
echo ""

# Demander la clé API
read -p "Rentre ta clé API Gemini: " API_KEY

# Valider qu'une clé a été entrée
if [ -z "$API_KEY" ]; then
    echo "❌ Clé API requise!"
    exit 1
fi

# Créer/mettre à jour le fichier .env
cat > /Users/baptiste/Documents/Projets/pikit/.env << EOF
# Configuration Gemini API
CLAUDE_CODE_USE_OPENAI=1
OPENAI_API_KEY=$API_KEY
OPENAI_BASE_URL=https://generativelanguage.googleapis.com/v1beta/openai/
OPENAI_MODEL=gemini-2.0-flash
EOF

echo ""
echo "✅ Configuration sauvegardée dans .env"
echo ""
echo "Prêt à démarrer!"
echo "Run: source .env && npm run dev"
