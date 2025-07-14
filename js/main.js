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

// Carousel testimonials infinite with arrows
document.addEventListener("DOMContentLoaded", () => {
  const wrapper = document.getElementById("carousel-wrapper");
  const items = Array.from(wrapper.children);
  const count = items.length;
  let index = 0;

  // Clone first and last for infinite loop
  const firstClone = items[0].cloneNode(true);
  const lastClone = items[count - 1].cloneNode(true);
  wrapper.append(firstClone);
  wrapper.prepend(lastClone);

  // Set initial offset to show the real first slide
  let slideWidth = wrapper.children[0].getBoundingClientRect().width;
  wrapper.style.transform = `translateX(-${slideWidth}px)`;
  index = 1; // because of prepend

  // Helpers
  const moveTo = (newIndex) => {
    wrapper.style.transition = "transform 0.5s ease-out";
    wrapper.style.transform = `translateX(-${slideWidth * newIndex}px)`;
    index = newIndex;
  };

  const jumpTo = (newIndex) => {
    // instant jump without transition
    wrapper.style.transition = "none";
    wrapper.style.transform = `translateX(-${slideWidth * newIndex}px)`;
    index = newIndex;
  };

  // Next / Prev handlers
  document.getElementById("carousel-next").addEventListener("click", () => {
    moveTo(index + 1);
  });
  document.getElementById("carousel-prev").addEventListener("click", () => {
    moveTo(index - 1);
  });

  // Loop handling
  wrapper.addEventListener("transitionend", () => {
    // if we've moved past the last clone, jump to real first
    if (wrapper.children[index].isSameNode(firstClone)) {
      jumpTo(1);
    }
    // if we've moved before the first clone, jump to real last
    if (wrapper.children[index].isSameNode(lastClone)) {
      jumpTo(count);
    }
  });

  // Auto-rotate every 5s
  setInterval(() => {
    moveTo(index + 1);
  }, 5000);

  // Adjust slideWidth on resize
  window.addEventListener("resize", () => {
    slideWidth = wrapper.children[0].getBoundingClientRect().width;
    jumpTo(index);
  });
});

// Carroussel prix des cours
// main.js

document.addEventListener("DOMContentLoaded", () => {
  const carousel = document.getElementById("courses-carousel");
  const prevBtn = document.getElementById("courses-prev");
  const nextBtn = document.getElementById("courses-next");

  if (!carousel) return;

  // Calcul du scroll : largeur d'une carte + gap
  const getStep = () => {
    const card = carousel.querySelector("article");
    if (!card) return 0;
    const gap = parseInt(getComputedStyle(carousel).gap, 10) || 0;
    return card.offsetWidth + gap;
  };

  const updateArrows = () => {
    const maxScroll = carousel.scrollWidth - carousel.clientWidth;
    prevBtn.style.display = carousel.scrollLeft > 0 ? "flex" : "none";
    nextBtn.style.display = carousel.scrollLeft < maxScroll ? "flex" : "none";
  };

  prevBtn?.addEventListener("click", () => {
    carousel.scrollBy({ left: -getStep(), behavior: "smooth" });
  });
  nextBtn?.addEventListener("click", () => {
    carousel.scrollBy({ left: getStep(), behavior: "smooth" });
  });

  carousel.addEventListener("scroll", updateArrows);
  window.addEventListener("resize", updateArrows);

  updateArrows();
});
