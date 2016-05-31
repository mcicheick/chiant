-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1
-- Généré le: Mar 31 Mai 2016 à 23:17
-- Version du serveur: 5.5.27
-- Version de PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `near2u`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `test_multi_sets`()
    DETERMINISTIC
begin
        select user() as first_col;
        select user() as first_col, now() as second_col;
        select user() as first_col, now() as second_col, now() as third_col;
        end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `chat_interne_equipe`
--
-- Création: Mar 22 Mars 2016 à 19:11
--

CREATE TABLE IF NOT EXISTS `chat_interne_equipe` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CONTENT` text NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `ID_EQUIPE` int(11) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `chat_inter_equipes`
--
-- Création: Sam 02 Avril 2016 à 16:46
--

CREATE TABLE IF NOT EXISTS `chat_inter_equipes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CONTENT` text NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `ID_EQUIPE_USER` int(11) NOT NULL COMMENT 'équipe de celui qui a posté',
  `ID_EQUIPE2` int(11) NOT NULL COMMENT 'Par convention le plus grand des deux indices',
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `coup_coeurs_equipes`
--
-- Création: Mar 22 Mars 2016 à 19:03
--

CREATE TABLE IF NOT EXISTS `coup_coeurs_equipes` (
  `ID_AMOUREUX` int(11) NOT NULL,
  `ID_BOGOSS` int(11) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `invitations_equipe_joueur`
--
-- Création: Mar 22 Mars 2016 à 19:09
--

CREATE TABLE IF NOT EXISTS `invitations_equipe_joueur` (
  `ID_INVITANT` int(11) NOT NULL,
  `ID_INVITE` int(11) NOT NULL,
  `ID_EQUIPE` int(11) NOT NULL,
  PRIMARY KEY (`ID_INVITANT`,`ID_INVITE`,`ID_EQUIPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `invitations_inter_equipes`
--
-- Création: Mar 22 Mars 2016 à 19:15
--

CREATE TABLE IF NOT EXISTS `invitations_inter_equipes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_INVITANT` int(11) NOT NULL,
  `ID_INVITE` int(11) NOT NULL,
  `DATE_RENCONTRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MONTANT` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `lien_team_users`
--
-- Création: Mar 22 Mars 2016 à 18:58
--

CREATE TABLE IF NOT EXISTS `lien_team_users` (
  `ID_TEAM` int(11) NOT NULL,
  `ID_USER` int(11) NOT NULL,
  PRIMARY KEY (`ID_TEAM`,`ID_USER`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `schema_version`
--
-- Création: Jeu 17 Mars 2016 à 11:15
--

CREATE TABLE IF NOT EXISTS `schema_version` (
  `major_version` int(11) NOT NULL,
  `minor_version` int(11) NOT NULL,
  `version_edit` int(11) NOT NULL,
  `custom_major` int(11) NOT NULL DEFAULT '0',
  `custom_minor` int(11) NOT NULL DEFAULT '0',
  `custom_edit` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `teams`
--
-- Création: Mar 22 Mars 2016 à 18:56
--

CREATE TABLE IF NOT EXISTS `teams` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PSEUDO` varchar(255) NOT NULL,
  `PHOTO` varchar(255) DEFAULT NULL COMMENT 'Nom du fichier dans le dossier des images',
  `NB_PLAYED_M` int(11) NOT NULL DEFAULT '0',
  `NB_VICTORIES` int(11) NOT NULL DEFAULT '0',
  `SCORE` int(11) NOT NULL DEFAULT '0',
  `RANK` int(11) DEFAULT NULL COMMENT 'possiblement inutile car redondant avec le score (calculable)',
  `SPORT` int(11) NOT NULL COMMENT 'entier représentant le sport',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--
-- Création: Ven 04 Mars 2016 à 18:33
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRENOM` varchar(255) NOT NULL,
  `NOM` varchar(255) NOT NULL,
  `MAIL` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL COMMENT 'peut etre null si on se connecte par facebook',
  `PSEUDO` varchar(255) NOT NULL,
  `PICTURE_FILE` varchar(255) DEFAULT NULL COMMENT 'nom du fichier image',
  `PREFS_SPORT` int(11) NOT NULL DEFAULT '0' COMMENT 'Préférences sous forme de masque bits',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `MAIL` (`MAIL`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
