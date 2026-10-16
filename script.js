/* ======================================
   MJ Paisajismo — JavaScript
   - Nav comportamiento (scroll + mobile)
   - Reveal on scroll
   - Año dinámico en footer
   - Submit del formulario (abre WhatsApp)
   ====================================== */

(function () {
  "use strict";

  /* ---- Nav: shadow on scroll ---- */
  const nav = document.getElementById("nav");
  const onScroll = () => {
    if (window.scrollY > 24) nav.classList.add("is-scrolled");
    else nav.classList.remove("is-scrolled");
  };
  document.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---- Nav: mobile toggle ---- */
  const toggle = document.getElementById("navToggle");
  const links = document.querySelector(".nav__links");
  if (toggle && links) {
    toggle.addEventListener("click", () => {
      const open = links.classList.toggle("is-open");
      toggle.setAttribute("aria-label", open ? "Cerrar menú" : "Abrir menú");
      toggle.classList.toggle("is-open", open);
      toggle.setAttribute("aria-expanded", String(open));
      document.body.style.overflow = open ? "hidden" : "";
    });
    links.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", () => {
        links.classList.remove("is-open");
        toggle.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Abrir menú");
        document.body.style.overflow = "";
      });
    });
  }

  /* ---- Reveal on scroll ---- */
  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    document.documentElement.classList.add("has-observer");
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("is-visible");
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("is-visible"));
  }

  /* ---- Año dinámico ---- */
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();
})();

/* ---- Form submit: arma un WhatsApp con los datos ---- */
function handleSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);
  const nombre = (data.get("nombre") || "").toString().trim();
  const email = (data.get("email") || "").toString().trim();
  const telefono = (data.get("telefono") || "").toString().trim();
  const mensaje = (data.get("mensaje") || "").toString().trim();

  const lines = [
    `Hola María José, soy ${nombre}.`,
    mensaje,
    "",
    `Email: ${email}`,
  ];
  if (telefono) lines.push(`Tel: ${telefono}`);

  const text = encodeURIComponent(lines.join("\n"));
  const url = `https://wa.me/59892664891?text=${text}`;

  const hint = document.getElementById("formHint");
  if (hint) hint.textContent = "Te estamos redirigiendo a WhatsApp…";

  window.open(url, "_blank", "noopener");
  // Se conserva el mensaje por si el usuario cancela el envío en WhatsApp.
  setTimeout(() => { if (hint) hint.textContent = ""; }, 4000);
  return false;
}

/* Proyectos: galería accesible con navegación por teclado. */
(() => {
  const data = document.getElementById('projectData');
  const dialog = document.getElementById('projectDialog');
  if (!data || !dialog || !dialog.showModal) return;
  const projects = JSON.parse(data.textContent);
  let selected = 0, photo = 0;
  const image = document.getElementById('projectImage');
  function render() {
    const project = projects[selected], current = project.photos[photo];
    image.src = current.path; image.alt = current.alt || project.title;
    document.getElementById('projectTitle').textContent = project.title;
    document.getElementById('projectMeta').textContent = [project.location, project.year].filter(Boolean).join(' · ');
    document.getElementById('projectDescription').textContent = project.description;
    document.getElementById('projectCounter').textContent = `${photo + 1} de ${project.photos.length}`;
    dialog.querySelectorAll('.dialog-prev, .dialog-next').forEach(button => { button.hidden = project.photos.length < 2; });
  }
  function move(delta) { photo = (photo + delta + projects[selected].photos.length) % projects[selected].photos.length; render(); }
  document.querySelectorAll('[data-project]').forEach(button => button.addEventListener('click', () => {
    selected = Number(button.dataset.project); photo = 0; render(); dialog.showModal(); document.body.style.overflow = 'hidden';
  }));
  dialog.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
  dialog.querySelector('.dialog-prev').addEventListener('click', () => move(-1));
  dialog.querySelector('.dialog-next').addEventListener('click', () => move(1));
  dialog.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft') { event.preventDefault(); move(-1); }
    if (event.key === 'ArrowRight') { event.preventDefault(); move(1); }
  });
  dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => { document.body.style.overflow = ''; image.removeAttribute('src'); });
})();
document.getElementById('contactForm')?.addEventListener('submit', handleSubmit);

/* Cerrar el menú con Escape y evitar enlaces ocultos en la navegación por teclado. */
(() => {
  const toggle = document.getElementById('navToggle');
  const links = document.getElementById('navLinks');
  if (!toggle || !links) return;
  const mobile = window.matchMedia('(max-width: 720px)');
  const sync = () => { links.inert = mobile.matches && !links.classList.contains('is-open'); };
  toggle.addEventListener('click', sync);
  links.addEventListener('click', sync);
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && links.classList.contains('is-open')) { toggle.click(); toggle.focus(); }
  });
  mobile.addEventListener('change', () => {
    if (!mobile.matches && links.classList.contains('is-open')) toggle.click();
    sync();
  });
  sync();
})();
