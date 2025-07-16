

// On désactive sur mobile pour les performances
if (/Mobi|Android/i.test(navigator.userAgent)) {
  console.log("Effet particules désactivé sur mobile");
} else {
  (function () {
    const canvas = document.getElementById("cursor-canvas");
    if (!canvas || !canvas.getContext) return;

    const ctx = canvas.getContext("2d");
    let cw, ch;

    // Les couleurs de ta charte
    const colors = ["#005B97", "#7D8CBF", "#DC2626"]; // deep-blue, light-blue et rouge

    function resize() {
      cw = canvas.width = window.innerWidth;
      ch = canvas.height = window.innerHeight;
    }
    window.addEventListener("resize", resize);
    resize();

    const particles = [];
    const MAX_PARTICLES = 300; // On peut en afficher un peu plus
    let lastMousePos = { x: 0, y: 0 };
    let mouseVelocity = { x: 0, y: 0 };

    class Particle {
      constructor(x, y, velocity) {
        this.x = x;
        this.y = y;

        // AMÉLIORATION : Physique avec inertie
        const angle = Math.random() * Math.PI * 0.5 - Math.PI * 0.25; // Cône de direction
        const speed = Math.random() * 2 + 1;

        // La particule hérite de la vélocité de la souris + une petite dispersion
        this.vx = velocity.x * 0.5 + Math.cos(angle) * speed;
        this.vy = velocity.y * 0.5 + Math.sin(angle) * speed;

        // AMÉLIORATION : Forme et couleur
        this.size = Math.random() * 3 + 1.5;
        this.life = Math.random() * 60 + 40; // Durée de vie variable
        this.initialLife = this.life;
        this.color = colors[Math.floor(Math.random() * colors.length)];
      }

      update() {
        this.x += this.vx;
        this.y += this.vy;
        this.vx *= 0.98; // Ralentissement progressif
        this.vy *= 0.98;
        this.life--;
      }

      draw(ctx) {
        ctx.beginPath();
        // AMÉLIORATION : Dessine une forme plus organique qu'un cercle parfait
        const opacity = Math.max(0, this.life / this.initialLife);
        ctx.fillStyle = `${this.color}${Math.round(opacity * 255)
          .toString(16)
          .padStart(2, "0")}`; // Conversion en RGBA hex

        // On dessine une forme légèrement étirée dans la direction du mouvement
        const angle = Math.atan2(this.vy, this.vx);
        ctx.ellipse(
          this.x,
          this.y,
          this.size,
          this.size * 0.7,
          angle,
          0,
          Math.PI * 2
        );

        ctx.fill();
      }
    }

    function emit(x, y, velocity) {
      const count = Math.min(
        3,
        Math.ceil(Math.hypot(velocity.x, velocity.y) / 2)
      ); // Plus on va vite, plus on émet
      for (let i = 0; i < count; i++) {
        if (particles.length < MAX_PARTICLES) {
          particles.push(new Particle(x, y, velocity));
        }
      }
    }

    document.addEventListener("mousemove", (e) => {
      // Calcul de la vélocité de la souris
      mouseVelocity.x = e.clientX - lastMousePos.x;
      mouseVelocity.y = e.clientY - lastMousePos.y;
      lastMousePos = { x: e.clientX, y: e.clientY };

      emit(e.clientX, e.clientY, mouseVelocity);
    });

    function animate() {
      ctx.clearRect(0, 0, cw, ch);

      for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.update();
        p.draw(ctx);
        if (p.life <= 0) {
          particles.splice(i, 1);
        }
      }

      requestAnimationFrame(animate);
    }

    // Ajout d'une petite animation de "reveal" pour le formulaire
    const formContainer = document.querySelector(".max-w-md.bg-white");
    if (formContainer) {
      formContainer.style.opacity = "0";
      formContainer.style.transition = "opacity 1s ease-in-out";
      setTimeout(() => {
        formContainer.style.opacity = "1";
      }, 500);
    }

    animate();
  })();
}
