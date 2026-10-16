# MJ Paisajismo

Landing y panel de proyectos para María José Suárez. PHP 8.2+, MySQL 8 o MariaDB 10.6+, Apache/LiteSpeed (Netuy Linux). No requiere Node, WordPress ni Supabase en producción.

## Qué incluye

- Landing con galería de proyectos publicados y fotos ampliables (teclado, Escape y botones).
- `/admin/`: acceso privado, borradores, publicación, título, descripción, ubicación, año y orden.
- Varias fotos por proyecto, portada, orden, texto alternativo y eliminación.
- Imágenes JPG/PNG/WebP de hasta 10 MB y 24 megapíxeles; hasta 10 por subida y 30 por proyecto. El servidor las convierte a JPG de hasta 1920 px y elimina metadatos. HEIC debe exportarse a JPG antes de subir.
- Contraseñas con hash, consultas parametrizadas, CSRF, sesiones con vencimiento de una hora de inactividad y límite de intentos por IP.
- Recuperación por email con enlace de un solo uso, vencimiento de 30 minutos e invalidación de sesiones anteriores.
- El contacto sigue abriendo WhatsApp; no registra consultas ni envía emails de contacto.

## Configuración inicial

1. Crear una base de datos y usuario con permisos sobre ella. En cPanel: **Bases de datos MySQL**.
2. Habilitar PHP 8.2 o superior con `pdo_mysql`, `gd` (JPEG, PNG y WebP), `fileinfo`, `mbstring`; `exif` recomendado para orientación de fotos.
3. Copiar `app/config.example.php` a `app/config.local.php` y completar conexión, URL definitiva y remitente de recuperación. Este archivo está excluido de Git. Usar una casilla habilitada en el hosting como remitente y configurar SPF/DKIM con el proveedor.
4. Desde la raíz, ejecutar en terminal:

   ```sh
   php bin/setup.php administradora@ejemplo.com
   ```

   Crea las tablas y pide la contraseña sin incluirla en comandos ni Git. No hay usuario o contraseña por defecto. El comando solo se ejecuta por terminal, no por navegador.
5. Opcionalmente importar las 11 fotos actuales (solo si no hay proyectos):

   ```sh
   php bin/seed.php
   ```

   Asigna nombres editoriales distintos y conserva el año original, que deben revisarse con María José; no supone qué fotos pertenecen al mismo trabajo. Para agruparlas, crear el proyecto real, subir sus fotos y ocultar los registros antiguos.
6. Dar escritura al usuario PHP en `uploads/projects/` (normalmente permisos 0755; no usar 0777).
7. Entrar a `/admin/` y probar creación, borrador, publicación y recuperación por email.

Si no hay configuración, la landing mantiene la galería estática original. Una vez configurada la base, solo se muestran los proyectos publicados; si falla la conexión no reaparecen trabajos ocultos. Un proyecto publicado debe tener al menos una foto. La primera foto según portada/orden es su imagen en la landing.

## Desarrollo local

Con PHP instalado y una base MySQL/MariaDB de prueba configurada:

```sh
php -S 127.0.0.1:8080 -t .
```

Abrir http://127.0.0.1:8080. El servidor integrado es para desarrollo: no aplica `.htaccess` y no debe exponerse a Internet. Sin base configurada se puede revisar la landing original, pero para usar el panel hace falta configurar la base.

## Publicación en Netuy / cPanel

1. En **Git Version Control**, clonar el repositorio en una carpeta privada fuera de `public_html`. Seleccionar la rama `codex/panel-administracion` para probar; no hace falta modificar `main`.
2. `.cpanel.yml` ejecuta `bin/deploy.sh`, que copia únicamente los archivos necesarios a `$HOME/public_html`. Si el dominio usa otra carpeta, ajustar `MJ_DEPLOY_PATH` en el script antes del primer despliegue.
3. Crear `public_html/app/config.local.php` con la configuración real. El despliegue nunca lo sobrescribe ni elimina fotos subidas. Tampoco copia `.git`, pruebas o herramientas de instalación a la web.
4. Para instalar usando la configuración publicada, desde la raíz del repositorio privado:

   ```sh
   MJ_CONFIG="$HOME/public_html/app/config.local.php" php bin/setup.php administradora@ejemplo.com
   MJ_CONFIG="$HOME/public_html/app/config.local.php" php bin/seed.php
   ```

   Si no hay terminal disponible, solicitar habilitación a Netuy. No publicar un instalador abierto en Internet.
5. Activar SSL y forzar HTTPS desde cPanel antes de usar el panel. La cookie de sesión se marca Secure cuando PHP recibe HTTPS.
6. Configurar en cPanel los límites de `.user.ini`: 10M por archivo, 110M por petición, 10 archivos y 256M de memoria. Confirmar también límites del proxy/LiteSpeed y tiempo de subida. `.user.ini` puede demorar varios minutos en aplicarse.
7. Usar **Update from Remote** y **Deploy HEAD Commit** para traer y publicar cambios de GitHub. Esta configuración no agrega un webhook automático.
8. Comprobar que `/app/config.local.php`, `/database/schema.sql`, `/.git/config` y scripts privados den 403/404. `.htaccess` requiere AllowOverride habilitado (Apache/LiteSpeed). La entrada anterior `/index.html` redirige a `/`.

Las fotos del panel no pasan por GitHub ni requieren un nuevo despliegue: al guardar un proyecto publicado se ven al recargar la landing. Una pestaña ya abierta no se actualiza en tiempo real.

## Respaldos y mantenimiento

Respaldar **la base de datos, `uploads/` y la configuración local**. GitHub solo guarda código y las fotos originales de `assets/`. Confirmar con Netuy la frecuencia y retención del backup y ensayar una restauración antes del lanzamiento.

Los archivos de fotos se sirven por URL pública, aunque su proyecto esté en borrador u oculto; ocultar controla su aparición en la galería, no revoca una URL ya compartida. Eliminar borra las fotos subidas del proyecto. Los originales en `assets/` se conservan para no afectar la portada ni otras secciones.

Si el email no funciona, revisar la entrega en Netuy y configurar el remitente. Una alternativa de mantenimiento por terminal es:

```sh
php bin/setup.php administradora@ejemplo.com --reset
```

Este comando invalida las sesiones previas y enlaces de recuperación.

## Estructura

- `index.php`, `styles.css`, `script.js`: web pública.
- `admin/`: pantallas del panel.
- `app/`: conexión, seguridad, procesamiento de imágenes y respaldo de galería.
- `database/schema.sql`: tablas iniciales (no contiene credenciales).
- `bin/`: instalación, importación y despliegue; solo terminal.
- `assets/`: fotos originales y logos.
- `uploads/projects/`: fotos subidas; excluidas de Git.

Datos actuales: María José Suárez · WhatsApp +598 92 664 891 · paisajismo.mj94@gmail.com.

## Pruebas

`tests/integration.py` ejecuta pruebas HTTP reales de acceso, CSRF, subidas inválidas, borradores, publicación, ocultación, eliminación, recuperación y límite de intentos. Requiere PHP y una **base MySQL/MariaDB vacía y descartable**; rechaza bases con tablas.

```sh
MJ_TEST_DSN='mysql:host=127.0.0.1;dbname=mj_test;charset=utf8mb4' MJ_TEST_USER=mj_test MJ_TEST_PASSWORD='clave-de-prueba' python3 tests/integration.py
```

No usar credenciales de producción. El servidor de prueba usa el puerto 18080 y se detiene al terminar.

Para asignar los nombres editoriales a una importación anterior:

```sh
php bin/name-original-projects.php
```

Solo cambia proyectos originales de una foto que todavía se llaman “Jardín residencial”. Conserva los títulos editados desde el panel.
