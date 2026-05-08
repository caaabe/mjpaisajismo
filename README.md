# MJ Paisajismo

Landing page para **María José Suárez** — Diseñadora de exteriores en Uruguay.

## Cómo abrirla

Doble click en `index.html` y se abre en el navegador. No necesita servidor ni instalaciones.

## Estructura

```
mjpaisajismo/
├── index.html       Página principal
├── styles.css       Estilos
├── script.js        Interacciones (menú móvil, animaciones, formulario)
├── favicon.svg      Ícono de pestaña
├── assets/          Imágenes (ver assets/README.txt)
└── README.md        Este archivo
```

## Datos cargados

- **Nombre:** María José Suárez
- **WhatsApp:** +598 92 664 891
- **Email:** paisajismo.mj94@gmail.com
- **Servicios:** Jardines residenciales · Paisajismo comercial

El formulario de contacto arma un mensaje de WhatsApp con los datos
ingresados (no requiere backend ni servicio de email).

## Reemplazar las fotos

1. Guardá tus fotos en `/assets/` con estos nombres:
   `portrait.jpg`, `proyecto-01.jpg` ... `proyecto-06.jpg`
2. En `index.html`, buscá los `src="https://images.unsplash.com/..."` y
   cambialos por `src="assets/proyecto-01.jpg"` (etc.).

Ver `assets/README.txt` para detalles.

## Editar textos

Todos los textos están en `index.html`. Buscá la sección que querés
cambiar (cada una tiene un comentario `<!-- ============ X ============ -->`)
y editá el texto entre las etiquetas.

## Publicar online (gratis)

Las opciones más simples:

- **Netlify Drop:** entrá a https://app.netlify.com/drop y arrastrá la
  carpeta `mjpaisajismo` entera. Te da una URL al instante.
- **Vercel:** https://vercel.com — conectá un repo de GitHub o subí la carpeta.
- **GitHub Pages:** subí los archivos a un repo público y activá Pages.

Con dominio propio (ej: `mjpaisajismo.com.uy`) cualquiera de las tres
permite apuntarlo gratis.
