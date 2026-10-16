<?php
require __DIR__ . '/app/bootstrap.php';
$projects = null;
$galleryUnavailable = false;
try { $projects = public_projects(); }
catch (Throwable $e) {
    error_log('No se pudo cargar la galería: ' . $e->getMessage());
    if (is_file(ROOT . '/app/config.local.php') || getenv('MJ_CONFIG')) { $projects = []; $galleryUnavailable = true; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="MJ Paisajismo — Diseño de exteriores en Uruguay. María José Suárez, diseñadora de jardines residenciales y proyectos comerciales." />
  <meta name="author" content="María José Suárez" />
  <meta property="og:title" content="MJ Paisajismo · María José Suárez" />
  <meta property="og:description" content="Diseño de exteriores y paisajismo en Uruguay" />
  <meta property="og:type" content="website" />
  <title>MJ Paisajismo · Diseño de exteriores en Uruguay</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />

  <link rel="icon" type="image/svg+xml" href="favicon.svg" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <!-- ============ NAV ============ -->
  <header class="nav" id="nav">
    <div class="nav__inner container">
      <a href="#top" class="nav__brand" aria-label="MJ Paisajismo">
        <span class="nav__brand-mark">MJ</span>
        <span class="nav__brand-text">Paisajismo</span>
      </a>
      <nav id="navLinks" class="nav__links" aria-label="Menú principal">
        <a href="#sobre">Sobre mí</a>
        <a href="#servicios">Servicios</a>
        <a href="#proyectos">Proyectos</a>
        <a href="#proceso">Proceso</a>
        <a href="#contacto">Contacto</a>
      </nav>
      <a href="https://wa.me/59892664891" class="nav__cta" target="_blank" rel="noopener">
        WhatsApp
      </a>
      <button class="nav__toggle" id="navToggle" aria-controls="navLinks" aria-label="Abrir menú" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- ============ HERO ============ -->
  <section class="hero" id="top">
    <div class="hero__media" aria-hidden="true"></div>
    <div class="hero__overlay" aria-hidden="true"></div>
    <div class="hero__content container">
      <p class="hero__eyebrow">Diseño de exteriores · Uruguay</p>
      <h1 class="hero__title">
        Jardines pensados<br/>
        para vivirlos.
      </h1>
      <p class="hero__lead">
        Espacios exteriores únicos, diseñados con criterio paisajístico
        y atención al detalle. Por María José Suárez.
      </p>
      <div class="hero__actions">
        <a href="#proyectos" class="btn btn--primary">Ver proyectos</a>
        <a href="#contacto" class="btn btn--ghost">Conversemos</a>
      </div>
    </div>
    <a href="#sobre" class="hero__scroll" aria-label="Bajar a la siguiente sección">
      <span></span>
    </a>
  </section>

  <!-- ============ SOBRE MÍ ============ -->
  <section class="section section--alt" id="sobre">
    <div class="container grid-2">
      <div class="reveal">
        <p class="eyebrow">Sobre mí</p>
        <h2 class="h2">María José Suárez</h2>
        <p class="lead">
          Diseñadora de exteriores radicada en Uruguay. Trabajo cada proyecto
          como una pieza única, escuchando el lugar, el clima y la forma de
          vivir de quien lo habita.
        </p>
        <p class="body">
          Mi enfoque combina diseño contemporáneo con vegetación nativa y
          adaptable, buscando jardines bellos en cada estación y de bajo
          impacto en su mantenimiento. Acompaño todo el proceso, desde la
          primera idea hasta la última planta.
        </p>
        <ul class="bullets">
          <li>Diseño funcional y estético</li>
          <li>Selección de especies adaptadas al clima local</li>
          <li>Acompañamiento integral del proyecto</li>
        </ul>
      </div>
      <div class="reveal">
        <figure class="portrait">
          <!-- Reemplazá por: assets/portrait.jpg (foto real de María José) -->
          <img src="assets/jardin-04.jpeg" alt="Detalle de un jardín diseñado por MJ Paisajismo" loading="lazy" />
        </figure>
      </div>
    </div>
  </section>

  <!-- ============ SERVICIOS ============ -->
  <section class="section" id="servicios">
    <div class="container">
      <div class="section__head reveal">
        <p class="eyebrow">Servicios</p>
        <h2 class="h2">Lo que diseño</h2>
        <p class="lead lead--narrow">
          Cada espacio merece un proyecto pensado a medida. Estos son los
          dos grandes ejes de trabajo.
        </p>
      </div>

      <div class="services">
        <article class="service reveal">
          <div class="service__num">01</div>
          <h3 class="h3">Jardines residenciales</h3>
          <p class="body">
            Diseño integral de jardines, patios, terrazas y entornos de
            piscina para casas y residencias particulares. Proyectos
            personalizados que reflejan la identidad de quienes los habitan.
          </p>
          <ul class="bullets bullets--small">
            <li>Diseño paisajístico completo</li>
            <li>Plano de plantación y materiales</li>
            <li>Selección de especies y mobiliario</li>
            <li>Coordinación de obra</li>
          </ul>
        </article>

        <article class="service reveal">
          <div class="service__num">02</div>
          <h3 class="h3">Paisajismo comercial</h3>
          <p class="body">
            Espacios verdes para hoteles, edificios, oficinas, locales
            gastronómicos y desarrollos inmobiliarios. Soluciones que
            potencian la marca y el valor del inmueble.
          </p>
          <ul class="bullets bullets--small">
            <li>Proyectos corporativos y hoteleros</li>
            <li>Áreas comunes de edificios</li>
            <li>Fachadas verdes y accesos</li>
            <li>Asesoramiento técnico</li>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ PROYECTOS ============ -->
  <section class="section section--alt" id="proyectos">
    <div class="container">
      <div class="section__head reveal">
        <p class="eyebrow">Proyectos</p>
        <h2 class="h2">Trabajos recientes</h2>
        <p class="lead lead--narrow">
          Una selección de proyectos realizados.
        </p>
      </div>

      <?php if ($projects === null): require ROOT . '/app/gallery-fallback.php';
      elseif (!$projects): ?>
      <p class="gallery-empty"><?= $galleryUnavailable ? 'No pudimos cargar los proyectos. Podés volver a intentar o consultarnos por WhatsApp.' : 'Pronto compartiremos nuevos trabajos.' ?></p>
      <?php else: ?>
      <div class="gallery">
      <?php foreach ($projects as $i => $project): $cover = $project['photos'][0]; ?>
        <figure class="gallery__item <?= $i % 7 === 0 ? 'gallery__item--tall' : '' ?> reveal">
          <button type="button" class="gallery__open" data-project="<?= $i ?>" aria-label="<?= h('Ver fotos de ' . $project['title']) ?>">
            <img src="<?= h($cover['path']) ?>" alt="<?= h($cover['alt'] ?: $project['title']) ?>" loading="lazy" decoding="async" />
            <span class="gallery__caption"><span><?= count($project['photos']) ?> fotos</span><strong><?= h($project['title']) ?></strong><span><?= h(implode(' · ', array_filter([$project['location'], $project['year']]))) ?></span></span>
          </button>
        </figure>
      <?php endforeach ?>
      </div>
      <dialog id="projectDialog" class="project-dialog" aria-labelledby="projectTitle">
        <button type="button" class="dialog-close" aria-label="Cerrar proyecto">✕</button>
        <div class="project-dialog__image"><button type="button" class="dialog-prev" aria-label="Foto anterior">←</button><img id="projectImage" alt=""><button type="button" class="dialog-next" aria-label="Foto siguiente">→</button></div>
        <div class="project-dialog__info"><p id="projectCounter" aria-live="polite"></p><h2 id="projectTitle" class="h3"></h2><p id="projectMeta"></p><p id="projectDescription"></p></div>
      </dialog>
      <script type="application/json" id="projectData"><?= json_encode($projects, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
      <?php endif ?>
    </div>
  </section>

  <!-- ============ PROCESO ============ -->
  <section class="section" id="proceso">
    <div class="container">
      <div class="section__head reveal">
        <p class="eyebrow">Proceso</p>
        <h2 class="h2">Cómo trabajamos juntos</h2>
      </div>

      <ol class="process">
        <li class="process__step reveal">
          <div class="process__num">01</div>
          <h3 class="h4">Visita y escucha</h3>
          <p>Conozco el lugar, entiendo la forma en que se vive el espacio y los deseos de quien lo habita.</p>
        </li>
        <li class="process__step reveal">
          <div class="process__num">02</div>
          <h3 class="h4">Anteproyecto</h3>
          <p>Presento la idea: zonificación, materiales, vegetación y un primer estimado de inversión.</p>
        </li>
        <li class="process__step reveal">
          <div class="process__num">03</div>
          <h3 class="h4">Proyecto ejecutivo</h3>
          <p>Planos de obra y de plantación, paleta vegetal, mobiliario y especificaciones técnicas.</p>
        </li>
        <li class="process__step reveal">
          <div class="process__num">04</div>
          <h3 class="h4">Obra y entrega</h3>
          <p>Coordino los equipos, superviso la ejecución y entrego el jardín listo para vivirlo.</p>
        </li>
      </ol>
    </div>
  </section>

  <!-- ============ CONTACTO ============ -->
  <section class="section section--dark" id="contacto">
    <div class="container grid-2">
      <div class="reveal">
        <p class="eyebrow eyebrow--light">Contacto</p>
        <h2 class="h2 h2--light">¿Empezamos tu proyecto?</h2>
        <p class="lead lead--light">
          Contame sobre tu espacio y lo que imaginás. Respondo personalmente
          a cada consulta.
        </p>

        <ul class="contact-list">
          <li>
            <span class="contact-list__label">WhatsApp</span>
            <a href="https://wa.me/59892664891" target="_blank" rel="noopener">+598 92 664 891</a>
          </li>
          <li>
            <span class="contact-list__label">Email</span>
            <a href="mailto:paisajismo.mj94@gmail.com">paisajismo.mj94@gmail.com</a>
          </li>
          <li>
            <span class="contact-list__label">Ubicación</span>
            <span>Uruguay</span>
          </li>
        </ul>

        <div class="contact-actions">
          <a href="https://wa.me/59892664891?text=Hola%20Mar%C3%ADa%20Jos%C3%A9%2C%20me%20gustar%C3%ADa%20consultarte%20por%20un%20proyecto%20de%20paisajismo." class="btn btn--primary" target="_blank" rel="noopener">
            Escribir por WhatsApp
          </a>
          <a href="mailto:paisajismo.mj94@gmail.com" class="btn btn--ghost btn--ghost-light">
            Enviar email
          </a>
        </div>
      </div>

      <div class="reveal">
        <form class="form" id="contactForm">
          <label class="form__field">
            <span>Nombre</span>
            <input type="text" name="nombre" required autocomplete="name" />
          </label>
          <label class="form__field">
            <span>Email</span>
            <input type="email" name="email" required autocomplete="email" />
          </label>
          <label class="form__field">
            <span>Teléfono (opcional)</span>
            <input type="tel" name="telefono" autocomplete="tel" />
          </label>
          <label class="form__field">
            <span>Contame sobre tu proyecto</span>
            <textarea name="mensaje" rows="4" required></textarea>
          </label>
          <button type="submit" class="btn btn--primary btn--full">Continuar en WhatsApp</button>
          <p class="form__hint" id="formHint" aria-live="polite"></p>
        </form>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <footer class="footer">
    <div class="container footer__inner">
      <div class="footer__brand">
        <span class="nav__brand-mark">MJ</span>
        <span>Paisajismo</span>
      </div>
      <p class="footer__copy">
        © <span id="year"></span> María José Suárez · Diseño de exteriores · Uruguay
      </p>
      <a href="#top" class="footer__top">Ir arriba ↑</a>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
