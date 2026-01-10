#!/bin/bash
# Script de configuración automática de Playit.gg
# Este script te guía para configurar Playit una sola vez para toda la plataforma

echo "========================================"
echo "  Configuración de Playit.gg - CDOR"
echo "========================================"
echo ""
echo "Este proceso solo se hace UNA VEZ."
echo "Después, TODOS los servidores tendrán IP pública automáticamente."
echo ""
echo "Paso 1: Abriendo página de claim..."
echo ""

# Abrir navegador
xdg-open "https://playit.gg/claim/8fd8a3424e" 2>/dev/null || echo "Por favor abre manualmente: https://playit.gg/claim/8fd8a3424e"

echo ""
echo "Instrucciones:"
echo "1. Crea una cuenta en Playit.gg (o inicia sesión)"
echo "2. Acepta el claim del agente"
echo "3. Vuelve aquí y presiona ENTER"
echo ""
read -p "Presiona ENTER cuando hayas completado el claim en la web..."

echo ""
echo "Obteniendo secret key..."

# Intentar obtener el secret
SECRET=$(./playit/playit claim exchange 8fd8a3424e 2>&1)

if echo "$SECRET" | grep -q "error"; then
    echo ""
    echo "❌ Error al obtener el secret:"
    echo "$SECRET"
    echo ""
    echo "Posibles causas:"
    echo "- No completaste el claim en la web"
    echo "- El código ya fue usado"
    echo ""
    echo "Solución: Genera un nuevo código:"
    echo "  ./playit/playit claim generate"
    echo "  ./playit/playit claim url NUEVO_CODIGO"
    exit 1
fi

# Guardar secret
echo "$SECRET" > playit/secret.txt
chmod 600 playit/secret.txt

echo ""
echo "✅ ¡Configuración completada!"
echo ""
echo "Secret guardado en: playit/secret.txt"
echo ""
echo "Ahora TODOS los servidores tendrán IP pública automáticamente."
echo ""
echo "Prueba:"
echo "1. Ve a: https://cdor.online/minecraft/"
echo "2. Crea un servidor"
echo "3. Inícialo"
echo "4. Verás una IP como: server.playit.gg:12345"
echo ""
