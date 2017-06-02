-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  ven. 02 juin 2017 à 19:42
-- Version du serveur :  5.6.35-80.0-log
-- Version de PHP :  7.0.16-3+0~20170222101552.24+jessie~1.gbpb3eec3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `tifod`
--

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `content` text,
  `content_type` varchar(20) NOT NULL DEFAULT 'text',
  `parent_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `path` text,
  `vote_plus` int(11) NOT NULL DEFAULT '0',
  `vote_minus` int(11) NOT NULL DEFAULT '0',
  `score_result` int(11) NOT NULL DEFAULT '0',
  `score_percent` int(11) NOT NULL DEFAULT '0',
  `has_pin` tinyint(1) NOT NULL DEFAULT '0',
  `posted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `is_remake` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `post`
--

INSERT INTO `post` (`id`, `content`, `content_type`, `parent_id`, `project_id`, `path`, `vote_plus`, `vote_minus`, `score_result`, `score_percent`, `has_pin`, `posted_on`, `author_id`, `is_remake`) VALUES
(189, '$$$', 'text', 0, 1, '/189/', 0, 0, 0, 0, 0, '2017-05-24 04:22:14', 1, 0),
(190, 'WMRJqt-190.png', 'text', 189, 1, '/189/190/', 0, 0, 0, 0, 0, '2017-05-24 04:22:36', 1, 0),
(191, 'SLHoD3-191.png', 'text', 190, 1, '/189/190/191/', 0, 0, 0, 0, 1, '2017-05-24 04:24:31', 1, 1),
(197, 'xUhhjE-197.png', 'text', 190, 1, '/189/190/197/', 0, 0, 0, 0, 0, '2017-05-25 22:37:26', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `user_name`) VALUES
(1, 'Jean');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;
--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
