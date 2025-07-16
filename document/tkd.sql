-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 16 juil. 2025 à 11:12
-- Version du serveur : 10.6.21-MariaDB
-- Version de PHP : 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nkngtjxqqe_tkd`
--

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `niveau` enum('débutant','intermédiaire','avancé','tous niveaux') NOT NULL,
  `prix` decimal(6,2) NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED NOT NULL COMMENT 'Durée en minutes',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom`, `description`, `team_id`, `niveau`, `prix`, `duration_minutes`, `date_creation`, `updated_at`) VALUES
(9, 'Self-Défense Féminine', 'Atelier pratique de self-défense pour apprendre des techniques simples et efficaces dans un environnement sûr et solidaire.', 4, 'débutant', 75.00, 90, '2025-07-16 13:04:00', '2025-07-16 13:04:00'),
(12, 'Taekwondo Loisir', 'Cours axé sur la pratique ludique et le bien-être, sans objectif de compétition. Idéal pour se défouler et apprendre les bases.', 4, 'tous niveaux', 65.00, 60, '2025-07-16 11:10:09', '2025-07-16 11:10:09'),
(13, 'Préparation Ceinture Noire', 'Entraînement spécifique pour les élèves visant la ceinture noire. Accent mis sur les poomses avancés et le perfectionnement technique.', 5, 'avancé', 95.00, 90, '2025-07-16 11:10:09', '2025-07-16 11:10:09');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(255) DEFAULT NULL,
  `contenu` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `nom`, `email`, `sujet`, `contenu`, `is_read`, `created_at`) VALUES
(2, 'Lucas Durand', 'lucas.durand@mail.com', 'Demande d\'information cours enfants', 'Bonjour, je souhaiterais avoir plus d\'informations sur les cours de Taekwondo pour les enfants de 8 ans. Est-ce possible d\'effectuer une séance d\'essai ?', 0, '2025-07-15 10:30:00'),
(3, 'Fatima Zohra', 'fatima.zohra@mail.com', 'Question sur le planning', 'Bonjour, le planning des cours adultes est-il fixe ou peut-il changer en cours d\'année ? Merci d\'avance pour votre réponse.', 0, '2025-07-15 14:00:00'),
(4, 'Marc Le Grand', 'marc.grand@mail.com', 'Félicitations pour le championnat !', 'Un grand bravo à toute l\'équipe pour les excellents résultats au dernier championnat régional ! Fier de faire partie de ce club.', 1, '2025-07-14 09:15:00'),
(5, 'Juliette Dupont', 'juliette.dupont@mail.com', 'Demande de renseignement', 'Bonjour, je suis intéressée par les cours pour adultes. Pourriez-vous me dire s\'il y a des sessions d\'essai gratuites ? Merci.', 0, '2025-07-16 11:10:09'),
(6, 'Antoine Giraud', 'antoine.giraud@email.net', 'Commentaire sur le site', 'Je trouve le site très clair et facile à naviguer ! Félicitations pour ce travail.', 1, '2025-07-16 11:10:09');

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL COMMENT 'url de la photo',
  `auteur` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`id`, `titre`, `contenu`, `photo`, `auteur`, `created_at`, `updated_at`) VALUES
(13, 'Portes ouvertes du club', 'Venez découvrir nos installations et rencontrer nos entraîneurs lors de nos journées portes ouvertes les 2 et 3 septembre ! Au programme : démonstrations, initiations et inscriptions.', 'black-belt.jpg', 5, '2025-07-16 13:05:00', '2025-07-16 13:05:00'),
(14, 'Nouveau partenariat avec la Mairie', 'Le Taekwondo Club St Priest est fier d\'annoncer son nouveau partenariat avec la Mairie pour le développement du sport en milieu scolaire. Des ateliers seront mis en place dès la rentrée.', NULL, 21, '2025-07-16 13:06:00', '2025-07-16 13:06:00'),
(15, 'Retour en images : notre gala annuel', 'Revivez les meilleurs moments de notre gala annuel en photos et vidéos ! Une soirée riche en émotions et en démonstrations spectaculaires.', 'hero-left.jpg', 6, '2025-07-16 13:07:00', '2025-07-16 13:07:00'),
(16, 'Succès au tournoi inter-clubs !', 'Notre équipe a brillé lors du récent tournoi inter-clubs, remportant plusieurs médailles d\'or et d\'argent. Un grand bravo à tous les participants et à nos entraîneurs !', 'coach_enfant.png', 5, '2025-07-16 11:10:09', '2025-07-16 11:10:09'),
(17, 'Focus sur les poomses : article technique', 'Découvrez l\'importance des poomses dans la pratique du Taekwondo et comment les maîtriser pour améliorer votre technique et votre concentration.', NULL, 6, '2025-07-16 11:10:09', '2025-07-16 11:10:09');

-- --------------------------------------------------------

--
-- Structure de la table `post_tag`
--

CREATE TABLE `post_tag` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `post_tag`
--

INSERT INTO `post_tag` (`post_id`, `tag_id`) VALUES
(15, 20),
(16, 18),
(16, 19),
(17, 21),
(17, 22);

-- --------------------------------------------------------

--
-- Structure de la table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `jour` tinyint(3) UNSIGNED NOT NULL COMMENT '0=dimanche…6=samedi',
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `schedules`
--

INSERT INTO `schedules` (`id`, `class_id`, `jour`, `heure_debut`, `heure_fin`) VALUES
(10, 9, 3, '19:00:00', '21:00:00'),
(11, 13, 4, '10:00:00', '11:15:00'),
(12, 12, 2, '18:00:00', '19:00:00'),
(13, 9, 5, '17:00:00', '18:30:00');

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(21, 'entraînement'),
(18, 'événement'),
(20, 'partenariat'),
(22, 'poomse'),
(19, 'stage');

-- --------------------------------------------------------

--
-- Structure de la table `team`
--

CREATE TABLE `team` (
  `id` int(10) UNSIGNED NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `team`
--

INSERT INTO `team` (`id`, `prenom`, `nom`, `bio`, `photo`, `created_at`, `updated_at`) VALUES
(4, 'Marie', 'Dubois', 'Marie Dubois est une ancienne championne nationale de Taekwondo. Sa passion pour l\'enseignement l\'a conduite à devenir coach. Elle se spécialise dans la préparation physique et mentale de jeunes athlètes.', 'coach-f-1.jpg', '2025-07-16 13:00:00', '2025-07-16 13:00:00'),
(5, 'Pierre', 'Leroy', 'Pierre Leroy, 7ème Dan, est un maître respecté dans la communauté Taekwondo. Il apporte une richesse d\'expérience et une approche traditionnelle de l\'art martial à ses élèves, jeunes et moins jeunes.', 'coach-m-1.jpg', '2025-07-16 13:01:00', '2025-07-16 13:01:00');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp_securise` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `role` enum('admin','membre') NOT NULL DEFAULT 'membre',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `mdp_securise`, `prenom`, `nom`, `role`, `created_at`, `updated_at`) VALUES
(1, 'elodie.martin@example.com', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Élodie', 'Martin', 'membre', '2025-07-06 15:50:08', '2025-07-15 21:30:00'),
(2, 'thomas.dubois@example.com', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Thomas', 'Dubois', 'membre', '2025-07-06 15:51:00', '2025-07-15 21:30:00'),
(3, 'sophie.legrand@example.com', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Sophie', 'Legrand', 'membre', '2025-07-06 15:52:00', '2025-07-15 21:30:00'),
(4, 'coach.principal@tkd-st-priest.fr', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Marc', 'Durand', 'admin', '2025-07-06 15:53:00', '2025-07-15 21:30:00'),
(5, 'secretaire@tkd-st-priest.fr', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Julie', 'Perrin', 'admin', '2025-07-06 15:54:00', '2025-07-15 21:30:00'),
(6, 'admin@tkd-st-priest.fr', '$2y$10$mS4gWme0I9.9efxzWnL6nOjz7YzhFQDHP0E8hXhH02zdlghRjInL.', 'Benoît', 'Lefevre', 'admin', '2025-07-06 15:55:00', '2025-07-15 21:30:00'),
(21, 'admin@admin.fr', '$2y$10$MSAy.mZkkEkZB8fYTv48Q.IoO9suUMEMj/e6SRJtRi2xExyiVbRce', 'Olivier', 'Labonne', 'admin', '2025-07-15 19:46:30', '2025-07-16 11:05:55'),
(22, 'membre@membre.com', '$2y$10$fbU94wkHlEv25SgKEHruXuVsQpD1BgetaE0NHYU1F84.5BHv2p58q', 'Anssoumane', 'Sissokho', 'membre', '2025-07-16 11:03:51', '2025-07-16 11:03:51');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_classes_team` (`team_id`) USING BTREE;

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_auteur` (`auteur`);

--
-- Index pour la table `post_tag`
--
ALTER TABLE `post_tag`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `fk_pt_tag` (`tag_id`);

--
-- Index pour la table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedules_class` (`class_id`);

--
-- Index pour la table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_classes_instructor` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_auteur` FOREIGN KEY (`auteur`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `post_tag`
--
ALTER TABLE `post_tag`
  ADD CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
