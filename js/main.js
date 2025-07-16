/// ============================================================================
// == Fichier JavaScript Principal (main.js)
// ============================================================================
// Auteur : Chokri Kadoussi
// Description : Gère toutes les interactions JavaScript du site.
// Chaque fonctionnalité est encapsulée dans un module qui ne s'exécute
// que si les éléments correspondants sont présents sur la page.
// ----------------------------------------------------------------------------

/**
 * Fonction utilitaire pour limiter la fréquence d'appel d'une fonction.
 * Principalement utilisée pour les événements 'scroll' et 'resize' pour optimiser la performance.
 * @param {Function} func La fonction à exécuter.
 * @param {number} wait Le délai d'attente en millisecondes.
 */
function debounce(func, wait = 20) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

/**
 * Point d'entrée principal.
 * Le code à l'intérieur de cet écouteur ne s'exécutera que lorsque
 * l'ensemble de la page HTML sera complètement chargé et prêt.
 */
document.addEventListener("DOMContentLoaded", () => {
  /**
   * MODULE : Menu Mobile (Global)
   * Gère l'ouverture et la fermeture du volet de navigation sur tous les appareils mobiles.
   */

  const initMobileMenu = () => {
    console.group("Initialisation du Menu Mobile"); // Groupe les logs pour la clarté

    const navToggle = document.getElementById("nav-toggle");
    const navClose = document.getElementById("nav-close");
    const mobileMenu = document.getElementById("mobile-menu");

    // On vérifie chaque élément et on l'affiche dans la console
    console.log(
      "Bouton 'nav-toggle' trouvé :",
      navToggle ? "✅ Oui" : "❌ Non (null)"
    );
    console.log(
      "Bouton 'nav-close' trouvé :",
      navClose ? "✅ Oui" : "❌ Non (null)"
    );
    console.log(
      "Menu 'mobile-menu' trouvé :",
      mobileMenu ? "✅ Oui" : "❌ Non (null)"
    );

    if (navToggle && mobileMenu && navClose) {
      console.log(
        "Statut : SUCCÈS. Les 3 éléments sont trouvés. Le menu est fonctionnel."
      );
      const toggleMenu = () => {
        mobileMenu.classList.toggle("-translate-x-full");
        document.body.classList.toggle("overflow-hidden");
        navToggle.classList.toggle("hidden");
      };
      navToggle.addEventListener("click", toggleMenu);
      navClose.addEventListener("click", toggleMenu);
    } else {
      console.error(
        "Statut : ÉCHEC. Un ou plusieurs éléments sont manquants. Le menu ne fonctionnera pas sur cette page."
      );
    }

    console.groupEnd(); // Fin du groupe de logs
  };

  /**
   * MODULE : Filtres de la page Actualités
   * Soumet le formulaire de filtre automatiquement lors d'un changement.
   */
  const initNewsFilters = () => {
    const filtersForm = document.getElementById("filters-form");
    if (!filtersForm) return; // Ne s'exécute que si le formulaire existe

    filtersForm
      .querySelector("#tag-select")
      ?.addEventListener("change", () => filtersForm.submit());
    filtersForm
      .querySelector("#sort-select")
      ?.addEventListener("change", () => filtersForm.submit());
  };

  /**
   * MODULE : Cartes qui se retournent (Page Équipe)
   * Gère l'animation de flip et ajuste la hauteur de la carte pour le contenu arrière.
   */
  const initTeamCards = () => {
    const cards = document.querySelectorAll(".perspective");
    if (cards.length === 0) return; // Ne s'exécute que si des cartes existent

    cards.forEach((container) => {
      const inner = container.querySelector(".card-inner");
      const front = inner.querySelector(".card-front");
      const back = inner.querySelector(".card-back");
      const btnFlip = inner.querySelector(".btn-flip");
      const btnUnflip = inner.querySelector(".btn-unflip");

      if (!inner || !front || !back || !btnFlip || !btnUnflip) return;

      let frontH, backH;
      const updateHeights = () => {
        frontH = front.getBoundingClientRect().height;
        backH = back.getBoundingClientRect().height;
        if (!inner.classList.contains("flipped")) {
          inner.style.height = frontH + "px";
        }
      };

      updateHeights(); // Calcul initial

      btnFlip.addEventListener("click", () => {
        inner.classList.add("flipped");
        inner.style.height = backH + "px";
      });
      btnUnflip.addEventListener("click", () => {
        inner.classList.remove("flipped");
        inner.style.height = frontH + "px";
      });

      window.addEventListener("resize", debounce(updateHeights, 100));
    });
  };

  /**
   * MODULE : Carrousel des Cours (Page "À propos")
   * Gère un carrousel simple avec des flèches qui se désactivent aux extrémités.
   */
  const initCoursesCarousel = () => {
    const carousel = document.getElementById("courses-carousel");
    if (!carousel) return;

    const prevBtn = document.getElementById("courses-prev");
    const nextBtn = document.getElementById("courses-next");

    const getStep = () => {
      const card = carousel.querySelector("article");
      if (!card) return 0;
      const gap = parseInt(getComputedStyle(carousel).gap, 10) || 0;
      return card.offsetWidth + gap;
    };

    const updateArrows = () => {
      const maxScroll = carousel.scrollWidth - carousel.clientWidth - 1;
      prevBtn.disabled = carousel.scrollLeft <= 0;
      nextBtn.disabled = carousel.scrollLeft >= maxScroll;
    };

    prevBtn?.addEventListener("click", () =>
      carousel.scrollBy({ left: -getStep(), behavior: "smooth" })
    );
    nextBtn?.addEventListener("click", () =>
      carousel.scrollBy({ left: getStep(), behavior: "smooth" })
    );

    carousel.addEventListener("scroll", debounce(updateArrows, 50));
    window.addEventListener("resize", debounce(updateArrows, 50));
    updateArrows();
  };

  /**
   * MODULE : Carrousel des Témoignages (Page d'accueil)
   * Gère un carrousel infini avec clonage et rotation automatique.
   */
  const initTestimonialsCarousel = () => {
    const track = document.getElementById("carousel-track");
    if (!track) return;

    // C'est ton code original, juste encapsulé dans cette fonction
    // pour ne s'exécuter que sur la page d'accueil.
    const items = Array.from(track.children);
    const count = items.length;
    if (count === 0) return;

    let index = 1;

    const firstClone = items[0].cloneNode(true);
    const lastClone = items[count - 1].cloneNode(true);
    track.append(firstClone);
    track.prepend(lastClone);

    let slideWidth = items[0].offsetWidth;

    const updatePosition = (withTransition = true) => {
      track.style.transition = withTransition
        ? "transform 0.5s ease-out"
        : "none";
      track.style.transform = `translateX(-${slideWidth * index}px)`;
    };

    updatePosition(false);

    const nextSlide = () => {
      if (index >= count + 1) return;
      index++;
      updatePosition();
    };

    const prevSlide = () => {
      if (index <= 0) return;
      index--;
      updatePosition();
    };

    document
      .getElementById("carousel-next")
      ?.addEventListener("click", nextSlide);
    document
      .getElementById("carousel-prev")
      ?.addEventListener("click", prevSlide);

    track.addEventListener("transitionend", () => {
      if (index >= count + 1) {
        index = 1;
        updatePosition(false);
      }
      if (index <= 0) {
        index = count;
        updatePosition(false);
      }
    });

    setInterval(nextSlide, 5000);

    window.addEventListener(
      "resize",
      debounce(() => {
        slideWidth = items[0].offsetWidth;
        updatePosition(false);
      }, 100)
    );
  };

  /**
   * MODULE : Lignes de la table des Messages Cliquables
   * Rend chaque ligne de la table des messages cliquable pour voir le détail.
   * Ne se déclenche pas si on clique sur un bouton d'action (ex: supprimer).
   */
  const initMessagesTable = () => {
    // On ne lance le script que si la table des messages est présente sur la page
    const msgTable = document.getElementById("msg-table");
    if (!msgTable) return;

    // On sélectionne toutes les lignes qui ont un attribut 'data-id'
    msgTable.querySelectorAll("tbody tr[data-id]").forEach((row) => {
      row.addEventListener("click", (e) => {
        // Point clé : on ne redirige pas si le clic a eu lieu sur un bouton
        if (e.target.closest("button")) {
          return;
        }

        // On récupère l'ID du message depuis l'attribut data-id
        const id = row.dataset.id;
        // On redirige l'utilisateur vers la page de détail du message
        window.location.href = `profile.php?page=messages&view=${id}`;
      });
    });
  };

  // --- APPEL DE TOUS LES MODULES ---
  // Chaque fonction est appelée, mais ne s'exécutera que si elle trouve
  // les éléments dont elle a besoin sur la page courante.
  initMobileMenu();
  initNewsFilters();
  initTeamCards();
  initCoursesCarousel();
  initTestimonialsCarousel();
  initMessagesTable();
});
