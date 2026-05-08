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
      toggle.classList.toggle("is-open", open);
      toggle.setAttribute("aria-expanded", String(open));
      document.body.style.overflow = open ? "hidden" : "";
    });
    links.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", () => {
        links.classList.remove("is-open");
        toggle.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
      });
    });
  }

  /* ---- Reveal on scroll ---- */
  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
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
  form.reset();
  setTimeout(() => { if (hint) hint.textContent = ""; }, 4000);
  return false;
}
