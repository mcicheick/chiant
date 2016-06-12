-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 08, 2016 at 10:55 
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `near2u`
--
CREATE DATABASE IF NOT EXISTS `near2u` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `near2u`;

-- --------------------------------------------------------

--
-- Table structure for table `chat_interne_equipe`
--

CREATE TABLE `chat_interne_equipe` (
  `ID` int(11) NOT NULL,
  `CONTENT` text NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `ID_EQUIPE` int(11) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `chat_interne_equipe`:
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_inter_equipes`
--

CREATE TABLE `chat_inter_equipes` (
  `ID` int(11) NOT NULL,
  `CONTENT` text NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `ID_EQUIPE_USER` int(11) NOT NULL COMMENT 'équipe de celui qui a posté',
  `ID_EQUIPE2` int(11) NOT NULL COMMENT 'Par convention le plus grand des deux indices',
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `chat_inter_equipes`:
--

-- --------------------------------------------------------

--
-- Table structure for table `coup_coeurs_equipes`
--

CREATE TABLE `coup_coeurs_equipes` (
  `ID_AMOUREUX` int(11) NOT NULL,
  `ID_BOGOSS` int(11) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `coup_coeurs_equipes`:
--

-- --------------------------------------------------------

--
-- Table structure for table `invitations_equipe_joueur`
--

CREATE TABLE `invitations_equipe_joueur` (
  `ID_INVITANT` int(11) NOT NULL,
  `ID_INVITE` int(11) NOT NULL,
  `ID_EQUIPE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `invitations_equipe_joueur`:
--

-- --------------------------------------------------------

--
-- Table structure for table `invitations_inter_equipes`
--

CREATE TABLE `invitations_inter_equipes` (
  `ID` int(11) NOT NULL,
  `ID_INVITANT` int(11) NOT NULL,
  `ID_INVITE` int(11) NOT NULL,
  `DATE_RENCONTRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MONTANT` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `invitations_inter_equipes`:
--

-- --------------------------------------------------------

--
-- Table structure for table `lien_team_users`
--

CREATE TABLE `lien_team_users` (
  `ID_TEAM` int(11) NOT NULL,
  `ID_USER` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `lien_team_users`:
--

-- --------------------------------------------------------

--
-- Table structure for table `offre_team_users`
--

CREATE TABLE `offre_team_users` (
  `ID_TEAM` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) NOT NULL,
  `NB` int(11) NOT NULL COMMENT 'nombre de joueurs recherchés',
  `FREQUENCE` int(11) NOT NULL COMMENT 'fréquence de jeu attendu',
  `NIVEAU` int(11) NOT NULL COMMENT 'niveau de jeu attendu'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Annonces d''offres pour les équipes recherchant des joueur.';

--
-- RELATIONS FOR TABLE `offre_team_users`:
--

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `ID` int(11) NOT NULL,
  `PSEUDO` varchar(255) NOT NULL,
  `PHOTO` varchar(255) DEFAULT NULL COMMENT 'Nom du fichier dans le dossier des images',
  `NB_PLAYED_M` int(11) NOT NULL DEFAULT '0',
  `NB_VICTORIES` int(11) NOT NULL DEFAULT '0',
  `SCORE` int(11) NOT NULL DEFAULT '0',
  `RANK` int(11) DEFAULT NULL COMMENT 'possiblement inutile car redondant avec le score (calculable)',
  `SPORT` int(11) NOT NULL COMMENT 'entier représentant le sport'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `teams`:
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `PRENOM` varchar(255) NOT NULL,
  `NOM` varchar(255) NOT NULL,
  `MAIL` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL COMMENT 'peut etre null si on se connecte par facebook',
  `PSEUDO` varchar(255) NOT NULL,
  `PICTURE_FILE` varchar(255) DEFAULT NULL COMMENT 'nom du fichier image',
  `PREFS_SPORT` int(11) NOT NULL DEFAULT '0' COMMENT 'Préférences sous forme de masque bits'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `near2u`.`matches` (
`ID` INT NOT NULL AUTO_INCREMENT ,
`DATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`ID_TEAM1` INT NOT NULL ,
`ID_TEAM2` INT NOT NULL ,
`RESULTAT` INT NOT NULL ,
PRIMARY KEY `PKEYID`(`ID`))
ENGINE = InnoDB;


--
-- RELATIONS FOR TABLE `users`:
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_interne_equipe`
--
ALTER TABLE `chat_interne_equipe`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `chat_inter_equipes`
--
ALTER TABLE `chat_inter_equipes`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `coup_coeurs_equipes`
--
ALTER TABLE `coup_coeurs_equipes`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `invitations_equipe_joueur`
--
ALTER TABLE `invitations_equipe_joueur`
  ADD PRIMARY KEY (`ID_INVITANT`,`ID_INVITE`,`ID_EQUIPE`);

--
-- Indexes for table `invitations_inter_equipes`
--
ALTER TABLE `invitations_inter_equipes`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `lien_team_users`
--
ALTER TABLE `lien_team_users`
  ADD PRIMARY KEY (`ID_TEAM`,`ID_USER`);

--
-- Indexes for table `offre_team_users`
--
ALTER TABLE `offre_team_users`
  ADD PRIMARY KEY (`ID_TEAM`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `MAIL` (`MAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_interne_equipe`
--
ALTER TABLE `chat_interne_equipe`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `chat_inter_equipes`
--
ALTER TABLE `chat_inter_equipes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `coup_coeurs_equipes`
--
ALTER TABLE `coup_coeurs_equipes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `invitations_inter_equipes`
--
ALTER TABLE `invitations_inter_equipes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
