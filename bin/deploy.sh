#!/bin/bash
set -euo pipefail
# Ejecutar desde la raíz del repositorio. Cambiar destino si el dominio usa otra carpeta.
MJ_DEPLOY_PATH="${MJ_DEPLOY_PATH:-$HOME/public_html}"
if [ ! -d "$MJ_DEPLOY_PATH" ]; then
  echo "No existe el destino de publicación: $MJ_DEPLOY_PATH" >&2
  exit 1
fi
# Lista explícita: nunca se copian .git, credenciales, tests ni fotos subidas.
for file in index.php styles.css script.js favicon.svg .htaccess .user.ini; do
  cp "$file" "$MJ_DEPLOY_PATH/$file"
done
for directory in app admin assets; do
  mkdir -p "$MJ_DEPLOY_PATH/$directory"
  for file in "$directory"/* "$directory"/.htaccess; do
    [ -f "$file" ] || continue
    [ "$(basename "$file")" = "config.local.php" ] && continue
    cp "$file" "$MJ_DEPLOY_PATH/$directory/"
  done
done
mkdir -p "$MJ_DEPLOY_PATH/uploads/projects"
cp uploads/.htaccess "$MJ_DEPLOY_PATH/uploads/.htaccess"
# La entrada anterior no debe seguir sirviendo una galería estática.
# No se elimina: index.php tiene prioridad y /index.html redirige mediante .htaccess.
echo "Código publicado. Fotos y configuración local conservadas."
