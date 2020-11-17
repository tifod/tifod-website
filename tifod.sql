-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 10 déc. 2019 à 13:35
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS beta_tifod DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE beta_tifod;

DROP TABLE IF EXISTS platform_data;
CREATE TABLE `platform_data` (
  `data_name` varchar(100) NOT NULL,
  `data_value` text NOT NULL,
  PRIMARY KEY (`data_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `platform_data` (`data_name`, `data_value`) VALUES ('version', '1.0.0');

DROP TABLE IF EXISTS post;
CREATE TABLE IF NOT EXISTS post (
  id int(11) NOT NULL AUTO_INCREMENT,
  content text CHARACTER SET utf8mb4 DEFAULT NULL,
  content_type varchar(50) NOT NULL,
  parent_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  path text DEFAULT NULL,
  vote_plus int(11) NOT NULL DEFAULT 0,
  vote_minus int(11) NOT NULL DEFAULT 0,
  score_result int(11) NOT NULL DEFAULT 0,
  score_percent int(11) NOT NULL DEFAULT 0,
  user_id_pin int(11) NOT NULL DEFAULT 0,
  edit_id int(11) NOT NULL DEFAULT 0,
  is_an_edit tinyint(1) NOT NULL DEFAULT 0,
  auto_pin_edits tinyint(1) NOT NULL DEFAULT 0,
  posted_on timestamp NOT NULL DEFAULT current_timestamp(),
  author_id int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `post_vote`
--

CREATE TABLE `post_vote` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_upvote` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_type` varchar(100) NOT NULL,
  `project_root_post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `project_role`
--

CREATE TABLE `project_role` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `project_role` varchar(100) NOT NULL DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `token`
--

CREATE TABLE `token` (
  `token_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `token_key` varchar(100) NOT NULL,
  `expiration_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `user_password` varchar(100) DEFAULT NULL,
  `avatar` varchar(100) NOT NULL DEFAULT 'default.png',
  `email` varchar(255) NOT NULL,
  `platform_role` varchar(100) NOT NULL DEFAULT 'regular_member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `platform_data`
--
ALTER TABLE `platform_data`
  ADD PRIMARY KEY (`data_name`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `post_vote`
--
ALTER TABLE `post_vote`
  ADD PRIMARY KEY (`post_id`,`user_id`);

--
-- Index pour la table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`);

--
-- Index pour la table `project_role`
--
ALTER TABLE `project_role`
  ADD PRIMARY KEY (`user_id`,`project_id`);

--
-- Index pour la table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`token_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `token`
--
ALTER TABLE `token`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
