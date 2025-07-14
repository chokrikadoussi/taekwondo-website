// public/js/main.js

(() => {
  // --- Mobile menu toggle ---
  const toggle = document.getElementById("nav-toggle");
  const mobile = document.getElementById("mobile-menu");
  if (toggle && mobile) {
    toggle.addEventListener("click", () => {
      const isOpen = !mobile.classList.toggle("hidden");
      toggle.setAttribute("aria-expanded", isOpen);
    });
  }

  // --- Click sur une ligne de message pour voir le détail ---
  document.querySelectorAll("#msg-table tbody tr[data-id]").forEach((row) => {
    row.addEventListener("click", (e) => {
      if (e.target.closest("button")) return;
      const id = row.dataset.id;
      window.location.href = `profile.php?page=messages&view=${id}`;
    });
  });

  // --- Scroll-spy pour la nav d’ancres (about.php) ---
  const sections = document.querySelectorAll("main section[id]");
  const navLinks = document.querySelectorAll("#page-nav a, #page-nav-mobile a");
  function onScroll() {
    let currentId = "";
    sections.forEach((sec) => {
      if (sec.getBoundingClientRect().top <= 80) {
        currentId = sec.id;
      }
    });
    navLinks.forEach((a) => {
      const match = a.getAttribute("href") === `#${currentId}`;
      // on évite de passer un token avec des espaces, on gère classe par classe
      if (match) {
        a.classList.add("text-blue-600", "border-b-2", "border-blue-600");
      } else {
        a.classList.remove("text-blue-600", "border-b-2", "border-blue-600");
      }
    });
  }
  if (sections.length && navLinks.length) {
    document.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }
})();
