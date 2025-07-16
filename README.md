# Documentation

### **Spécification Fonctionnelle Détaillée (SFD)**
#### **Projet : Taekwondo Club St Priest**
**Date :** 16 juillet 2025

---

#### **Page 1 : Informations Générales**

**1.1. URL du Site Web**
* **URL du site en ligne :** [LAISSER UN ESPACE POUR L'URL DU SITE]

**1.2. Accès aux Comptes de Test**

* **Compte Utilisateur (Membre) :**
    * **Login :** [LAISSER UN ESPACE POUR LE LOGIN UTILISATEUR]
    * **Mot de passe :** [LAISSER UN ESPACE POUR LE MOT DE PASSE UTILISATEUR]

* **Compte Administrateur :**
    * **Login :** [LAISSER UN ESPACE POUR LE LOGIN ADMINISTRATEUR]
    * **Mot de passe :** [LAISSER UN ESPACE POUR LE MOT DE PASSE ADMINISTRATEUR]

---

#### **Page 2 : Description Générale du Projet**

**2.1. Nom du Site Web**
Taekwondo Club St Priest

**2.2. Objet du Site**
Le site web "Taekwondo Club St Priest" est une plateforme dynamique développée en PHP, conçue pour servir à la fois de vitrine numérique pour le club et d'outil de gestion interne. Son objectif principal est de renforcer la communication avec les membres actuels et potentiels, de promouvoir les activités du club et de faciliter l'administration quotidienne par les responsables. Il vise à offrir une expérience utilisateur intuitive et un environnement d'administration robuste pour la gestion des contenus et des utilisateurs.

**2.3. Fonctionnement Général**

Le site se compose de deux grandes parties distinctes, le "Front-Office" pour les visiteurs et membres, et le "Back-Office" pour les administrateurs du club.

* **Côté "Front-Office" (Utilisateurs et Visiteurs) :**
    * **Navigation Publique :** Les visiteurs peuvent consulter les informations générales du club, y compris la présentation du club, les actualités, l'équipe d'entraîneurs, et les informations de contact.
    * **Espace Membre :** Les utilisateurs peuvent créer un compte et s'authentifier pour accéder à leur espace personnel. Dans cet espace, ils peuvent consulter et modifier leurs informations de profil (nom, prénom, email, mot de passe).
    * **Interactions :** Le site propose des fonctionnalités de filtrage et de tri pour les actualités, et un formulaire de contact pour envoyer des messages au club.

* **Côté "Back-Office" (Administrateurs) :**
    * **Gestion Complète :** Les utilisateurs avec le rôle 'admin' ont accès à des modules de gestion avancés via leur espace profil. Ils peuvent y gérer :
        * **Utilisateurs :** Création, modification des informations (y compris le rôle d'administrateur/membre), et suppression de comptes utilisateurs.
        * **Actualités :** Ajout, modification et suppression des articles d'actualité, y compris leur contenu, auteur et tags.
        * **Cours :** Gestion des offres de cours du club (nom, niveau, prix, description, entraîneur associé).
        * **Équipe (Entraîneurs) :** Ajout, modification et suppression des profils des entraîneurs (prénom, nom, biographie, photo).
        * **Messages :** Consultation des messages envoyés via le formulaire de contact, marquage comme lu et suppression.

Le site est conçu pour être entièrement fonctionnel en ligne, et sa base de données est structurée pour supporter toutes ces fonctionnalités de manière cohérente.

---

#### **Page 3 : Architecture Technique et Organisation des Fichiers**

**3.1. Architecture Générale**
Le projet suit une architecture simple, proche d'un modèle "Front Controller" ou "Micro-Framework", où la logique est centralisée et les responsabilités sont séparées entre les fichiers PHP pour l'affichage, la logique métier, et la configuration.

**3.2. Structure des Répertoires**

* **`/` (Racine du Projet) :** Contient les principaux fichiers PHP d'entrée pour les différentes pages du site (`index.php`, `about.php`, `contact.php`, `news.php`, `news_detail.php`, `login.php`, `logout.php`, `profile.php`, `register.php`, `team.php`, `erreur.php`).
* **`/css/` :** Contient la feuille de style principale (`styles.css`).
* **`/fonction/` :** Centralise toutes les fonctions PHP métier et utilitaires (`fonctions.php`, `test.php` pour les tests).
* **`/img/` :** Stocke toutes les ressources images du site.
* **`/includes/` :** Regroupe les parties réutilisables du code PHP.
    * **`/includes/components/` :** Contient des composants d'interface génériques (ex: `table.php`, `pagination.php`, `toolbar.php`).
    * **`/includes/profile/` :** Contient les modules spécifiques à l'espace membre/admin (`overview.php`, `users.php`, `posts.php`, `classes.php`, `team.php`, `messages.php`).
    * **Racine de `includes/` :** Contient les éléments communs à toutes les pages (ex: `header.php`, `footer.php`, `head.php`).
* **`/js/` :** Contient le fichier JavaScript principal (`main.js`).
* **`/logs/` :** Stocke les fichiers de log des erreurs (`bdd_erreurs.log`).
* **`/parametrage/` :** Contient le fichier de configuration (`param.php`).

**3.3. Base de Données**
Le site utilise une seule base de données relationnelle (`tkd`). Le schéma est structuré en plusieurs tables interconnectées via des clés étrangères pour organiser les données de manière cohérente :
* `users` : Gestion des comptes utilisateurs (membres et administrateurs).
* `team` : Informations sur les entraîneurs du club.
* `classes` : Détails des cours proposés.
* `schedules` : Planning des cours (liens `classes` et jours/heures).
* `posts` : Articles d'actualité.
* `tags` : Mots-clés pour catégoriser les actualités.
* `post_tag` : Table de liaison entre `posts` et `tags`.
* `messages` : Messages envoyés via le formulaire de contact.

---

#### **Page 4 : Fonctions Centrales (`fonction/fonctions.php`)**

Le fichier `fonctions.php` est le cœur logique du projet. Il regroupe l'ensemble des fonctions PHP utilisées pour interagir avec la base de données, valider les données et gérer les aspects métier.

**4.1. Gestion de la Base de Données**
* `logErreur(string $function, string $message, array $context = array())` : Enregistre les erreurs PDO dans un fichier de log dédié.
* `connexionBaseDeDonnees(): PDO` : Établit une connexion unique (singleton) à la base de données via PDO, avec gestion des exceptions.

**4.2. Gestion des Utilisateurs (CRUD et Authentification)**
* `estValideMail(string $email): bool` : Valide le format d'une adresse e-mail.
* `estValideMotdepasse(string $mdp): bool` : Vérifie la robustesse d'un mot de passe (longueur, majuscule, chiffre, caractère spécial).
* `authentification(string $email, string $mdp): bool` : Authentifie un utilisateur en vérifiant les identifiants et le hachage du mot de passe.
* `connexionUtilisateur(string $email): array|null` : Charge les données d'un utilisateur après authentification.
* `isUtilisateur(string $email, ?int $excludeId = null): bool` : Vérifie si un e-mail est déjà utilisé.
* `enregistrerUtilisateur(array $donnees): bool` : Crée un nouvel utilisateur.
* `getListeUtilisateurs(): array` : Récupère la liste de tous les utilisateurs.
* `getUtilisateurParId(int $id): array` : Récupère les données d'un utilisateur par son ID.
* `modifierUtilisateur(int $id, array $donnees): bool` : Met à jour les informations d'un utilisateur.
* `supprimerUtilisateur(int $id): bool` : Supprime un utilisateur.
* `validerDonneesUtilisateur(array $data, ?int $excludeId = null): array` : Valide les données soumises pour un utilisateur.

**4.3. Gestion de l'Équipe (Entraîneurs)**
* `enregistrerEntraineur(array $donnees): bool` : Crée un nouvel entraîneur.
* `validerDonnesEntraineur(array $data): array` : Valide les données d'un entraîneur.
* `getEntraineurParId(int $id): array|null` : Récupère les données d'un entraîneur par son ID.
* `getListeEntraineurs(): array` : Récupère la liste de tous les entraîneurs.
* `modifierEntraineur(int $id, array $donnees): bool` : Met à jour les informations d'un entraîneur.
* `supprimerEntraineur(int $id): bool` : Supprime un entraîneur.

---

#### **Page 5 : Fonctions Centrales (Suite) et Aspects Techniques Complémentaires**

**5.1. Gestion des Cours**
* `getCoursParId(int $id): array` : Récupère les données d'un cours par son ID.
* `getListeCours(): array` : Récupère la liste de tous les cours.
* `getCoursPlanning(): array` : Récupère le planning des cours.
* `validerDonnesCours(array $donnees): array` : Valide les données soumises pour un cours.
* `enregistrerCours(array $donnees): bool` : Crée un nouveau cours.
* `modifierClasse(int $id, array $donnees): bool` : Met à jour les informations d'un cours.
* `supprimerCours(int $id): bool` : Supprime un cours.

**5.2. Gestion des Messages**
* `getListeMessages(): array` : Récupère la liste de tous les messages.
* `getMessageParId(int $id): array` : Récupère les données d'un message par son ID.
* `setMessageLu(int $id): bool` : Marque un message comme lu.
* `enregistrerMessage(array $donnees): bool` : Enregistre un nouveau message de contact.
* `supprimerMessage(int $id): bool` : Supprime un message.

**5.3. Gestion des Actualités et Tags**
* `validerDonneesPost(array $donnees): array` : Valide les données soumises pour un article.
* `getListeAuteurs(): array` : Récupère la liste des auteurs (utilisateurs avec rôle admin).
* `enregistrerPost(array $donnees): int` : Crée un nouvel article et retourne son ID.
* `modifierPost(int $id, array $donnees): bool` : Met à jour les informations d'un article.
* `supprimerPost(int $id): bool` : Supprime un article et ses liaisons de tags.
* `getPostParId(int $id): array` : Récupère les données d'un article par son ID.
* `getListePosts(int $excerptLength = 200, ?string $filterTag = null, string $sort = 'desc'): array` : Récupère la liste des articles avec options de filtrage et tri.
* `syncPostTags(int $postId, string $tagsCsv): void` : Synchronise les tags associés à un article.
* `getTagsPourPost(int $id): array` : Récupère la liste des tags associés à un article.
* `getListeTags()` : Récupère la liste de tous les tags existants.

**5.4. Fonctions Utilitaires Générales**
* `displayFlash(): void` : Affiche les messages flash stockés en session.
* `setFlash(string $type, string $msg): void` : Définit un message flash.
* `requireConnexion(): void` : Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
* `nettoyerDonnees(array $data): array` : Nettoie un tableau de données (trim, strip_tags).
* `paginateArray(array $all, string $param, int $perPage = 10): array` : Gère la pagination en mémoire d'un tableau de données.
* `getTemoignages(): array` : Renvoie une liste statique de témoignages (TODO : à dynamiser).

**5.5. Configuration (`parametrage/param.php`)**
Ce fichier centralise les constantes et paramètres essentiels au fonctionnement de l'application, tels que les identifiants de connexion à la base de données (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`), les contraintes de sécurité pour les mots de passe (`PSSWD_MIN_LEN`) et le chemin du fichier de log (`LOG_PATH`).

**5.6. Technologies Frontend**
Le site s'appuie sur les technologies web standard :
* **HTML5** : Structure des pages web.
* **CSS (Tailwind CSS)** : Utilisation de classes utilitaires pour un style rapide et une approche "mobile-first" pour le design responsif.
* **JavaScript (`js/main.js`)** : Ajoute des interactions dynamiques à l'interface utilisateur, notamment :
    * Gestion du menu de navigation mobile.
    * Comportement de clic sur les lignes du tableau des messages pour afficher les détails.
    * Carrousels pour les témoignages et les cours.
    * Effets visuels (ex: effet de particules sur la page de connexion).
    * Animations de cartes réversibles pour l'équipe.

**5.7. Sécurité et Robustesse**
* **Hachage de mots de passe** : Utilisation de `password_hash()` et `password_verify()` pour stocker et vérifier les mots de passe de manière sécurisée.
* **Validation des entrées** : Validation côté serveur de tous les formulaires (e-mail, mot de passe, champs obligatoires) pour prévenir les données invalides.
* **PDO avec requêtes préparées** : Protection contre les injections SQL grâce à l'utilisation systématique des requêtes préparées via PDO.
* **Gestion des erreurs** : Implémentation d'un mécanisme de log des erreurs PDO pour faciliter la détection et la correction des problèmes en production.

---