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

// Cartes Team
document.querySelectorAll(".perspective").forEach((container) => {
  const inner = container.querySelector(".card-inner");
  const front = inner.querySelector(".card-front");
  const back = inner.querySelector(".card-back");
  const btnFlip = inner.querySelector(".btn-flip");
  const btnUnflip = inner.querySelector(".btn-unflip");

  // Mesure des hauteurs
  const frontH = front.getBoundingClientRect().height;
  const backH = back.getBoundingClientRect().height;
  inner.style.height = frontH + "px";

  btnFlip.addEventListener("click", () => {
    inner.classList.add("flipped");
    inner.style.height = backH + "px";
  });
  btnUnflip.addEventListener("click", () => {
    inner.classList.remove("flipped");
    inner.style.height = frontH + "px";
  });
});

// Effet sur page login
if (/Mobi|Android/i.test(navigator.userAgent)) {
  console.log("Effet particules désactivé sur mobile");
} else {
  (function () {
    const canvas = document.getElementById("cursor-canvas");
    if (!canvas || !canvas.getContext) return; // fallback si pas supporté
    const ctx = canvas.getContext("2d");
    let cw, ch;

    function resize() {
      cw = canvas.width = window.innerWidth;
      ch = canvas.height = window.innerHeight;
    }
    window.addEventListener("resize", resize);
    resize();

    const particles = [];
    const MAX_PARTICLES = 200;

    class Particle {
      constructor(x, y) {
        this.x = x;
        this.y = y;
        const angle = Math.random() * Math.PI * 2;
        const speed = Math.random() * 1.5 + 0.5;
        this.vx = Math.cos(angle) * speed;
        this.vy = Math.sin(angle) * speed;
        this.size = Math.random() * 4 + 2;
        this.life = 80;
        this.opacity = 1;
      }
      update() {
        this.x += this.vx;
        this.y += this.vy;
        this.life--;
        this.opacity = this.life / 80;
      }
      draw(ctx) {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(75,132,245,${this.opacity})`;
        ctx.shadowBlur = 20;
        ctx.shadowColor = "rgba(75,132,245,0.7)";
        ctx.fill();
      }
    }

    function emit(x, y) {
      for (let i = 0; i < 5; i++) {
        if (particles.length < MAX_PARTICLES) {
          particles.push(new Particle(x, y));
        }
      }
    }

    let lastTime = 0;
    document.addEventListener("mousemove", (e) => {
      const now = performance.now();
      if (now - lastTime > 16) {
        emit(e.clientX, e.clientY);
        lastTime = now;
      }
    });

    function animate() {
      ctx.clearRect(0, 0, cw, ch);
      for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.update();
        p.draw(ctx);
        if (p.life <= 0) particles.splice(i, 1);
      }
      requestAnimationFrame(animate);
    }
    animate();
  })();
}
