-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 18 déc. 2024 à 16:19
-- Version du serveur : 10.4.27-MariaDB
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `event_access`
--

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `categorie` varchar(150) NOT NULL,
  `date` datetime NOT NULL,
  `ville` varchar(50) NOT NULL,
  `code_postal` int(11) NOT NULL,
  `adresse` varchar(150) NOT NULL,
  `prix` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  `image` varchar(150) DEFAULT NULL,
  `organisateur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `telephone` varchar(15) NOT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `code_postal` varchar(5) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `role` enum('utilisateur','organisateur','admin') DEFAULT 'utilisateur',
  `avatar` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE participants_evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('confirmé', 'annulé') DEFAULT 'confirmé',
    FOREIGN KEY (evenement_id) REFERENCES evenement(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (evenement_id, utilisateur_id)
);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organisateur_id` (`organisateur_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (`organisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Création de la table administrateurs
CREATE TABLE IF NOT EXISTS administrateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME DEFAULT NULL,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajout des index pour optimiser les performances
ALTER TABLE administrateurs
ADD INDEX idx_email (email),
ADD INDEX idx_date_creation (date_creation);

-- Ajout des contraintes de validation
ALTER TABLE administrateurs
ADD CONSTRAINT chk_email CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
ADD CONSTRAINT chk_password_length CHECK (LENGTH(mot_de_passe) >= 60);
