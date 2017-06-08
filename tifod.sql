-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Client :  localhost
-- Généré le :  Jeu 08 Juin 2017 à 07:56
-- Version du serveur :  10.1.19-MariaDB
-- Version de PHP :  7.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `content_type` varchar(50) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `path` text,
  `vote_plus` int(11) NOT NULL DEFAULT '0',
  `vote_minus` int(11) NOT NULL DEFAULT '0',
  `score_result` int(11) NOT NULL DEFAULT '0',
  `score_percent` int(11) NOT NULL DEFAULT '0',
  `user_id_pin` int(11) NOT NULL DEFAULT '0',
  `posted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `post_vote`
--

CREATE TABLE `post_vote` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_upvote` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_password` varchar(100) NOT NULL,
  `avatar` varchar(100) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_password`, `avatar`) VALUES
(1, 'Jean', '$2y$11$J21le9nLsqQivIU7qzbLJOg7NasJ6HzbS82heiZQwqqkwU1WISoSa', 'default.png');
INSERT INTO `user` (`user_name`, `user_password`, `avatar`) VALUES
('Guest', '$2y$11$PcJnv6tavBaOs4nSGSXHq.btaT/XRdsAvRgeOivbiEvqWFXTATU8e', 'default.png');

--
-- Index pour les tables exportées
--

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
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;
--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
