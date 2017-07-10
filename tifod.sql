SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+02:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS beta_tifod DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE beta_tifod;

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

DROP TABLE IF EXISTS post_vote;
CREATE TABLE IF NOT EXISTS post_vote (
  post_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  is_upvote tinyint(1) NOT NULL,
  PRIMARY KEY (post_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS project;
CREATE TABLE IF NOT EXISTS project (
  project_id int(11) NOT NULL,
  project_type varchar(100) NOT NULL,
  project_root_post_id int(11) NOT NULL,
  PRIMARY KEY (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS project_role;
CREATE TABLE IF NOT EXISTS project_role (
  user_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  project_role varchar(100) NOT NULL DEFAULT 'none',
  PRIMARY KEY (user_id,project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS token;
CREATE TABLE IF NOT EXISTS token (
  token_id int(11) NOT NULL AUTO_INCREMENT,
  action varchar(100) NOT NULL,
  token_key varchar(100) NOT NULL,
  expiration_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  email varchar(100) NOT NULL,
  PRIMARY KEY (token_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS user;
CREATE TABLE IF NOT EXISTS `user` (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  user_name varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  description text CHARACTER SET utf8mb4 DEFAULT NULL,
  user_password varchar(100) DEFAULT NULL,
  avatar varchar(100) NOT NULL DEFAULT 'default.png',
  email varchar(255) NOT NULL,
  platform_role varchar(100) NOT NULL DEFAULT 'regular_member',
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
