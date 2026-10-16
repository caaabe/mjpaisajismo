CARPETA /assets/
================

Contiene las fotos originales y los logos de la landing.

- portada.jpeg: portada principal (referenciada en styles.css).
- jardin-01.jpeg a jardin-11.jpeg: imágenes originales de la galería.
- jardin-04.jpeg: también se usa en la sección "Sobre mí" de index.php.
- mjLogo.png y logo-full.png: logos disponibles.

El comando php bin/seed.php importa referencias a estas 11 fotos en la
base de datos, sin duplicarlas. Conserva los nombres genéricos existentes
hasta que María José revise y agrupe sus trabajos reales.

Las nuevas fotos se cargan desde /admin/ y se guardan en uploads/projects/.
No es necesario editar HTML, cambiar archivos de esta carpeta ni usar GitHub.
La portada y la sección "Sobre mí" continúan editándose en el código.
