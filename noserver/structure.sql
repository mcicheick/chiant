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

DROP DATABASE IF EXISTS `near2u`;
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
  `DESCRIPTION` TEXT NOT NULL,
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
  `SPORT` int(11) NOT NULL COMMENT 'entier représentant le sport',
  `LATITUDE` float(10) NOT NULL COMMENT 'float représentant la latitude',
  `LONGITUDE` float(10) NOT NULL COMMENT 'float représentant la longitude',
  `LAST_CONNEXION` DATE NOT NULL COMMENT 'date représentant la date de la dernière connexion'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE VIEW  `view_teams_rank` (
ID,
RANG
) AS SELECT ID, (
(SELECT COUNT(*)+1 FROM teams AS L   WHERE L.SCORE > T.SCORE    ))
FROM  `teams` T;

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
  `PICTURE_FILE` varchar(255) DEFAULT NULL COMMENT 'nom du fichier image',
  `TELEPHONE` varchar(255) DEFAULT NULL,
  `PREFS_SPORT` int(11) NOT NULL DEFAULT '0' COMMENT 'Préférences sous forme de masque bits',
  `LATITUDE` float(10) NOT NULL COMMENT 'float représentant la latitude',
  `LONGITUDE` float(10) NOT NULL COMMENT 'float représentant la longitude',
  `DATE_INSCRIPTION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CLE` varchar(255) DEFAULT NULL COMMENT 'le couplet(email,cle) permet l update du password par methode email+get si oubli',
  `CITY` varchar(255) DEFAULT NULL COMMENT 'Ville',
  `COUNTRY` varchar(255) DEFAULT NULL COMMENT 'Pays'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `users_inactif` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `PRENOM` varchar(255) NOT NULL,
  `NOM` varchar(255) NOT NULL,
  `MAIL` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL ,
  `TELEPHONE` varchar(255) DEFAULT NULL,
  `LATITUDE` float(10) NOT NULL COMMENT 'float représentant la latitude',
  `LONGITUDE` float(10) NOT NULL COMMENT 'float représentant la longitude',
  `DATE_INSCRIPTION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CLE` varchar(255) DEFAULT NULL COMMENT 'le couplet(email,cle) permet d activer le compte par une methode email+get',
  `CITY` varchar(255) DEFAULT NULL COMMENT 'Ville',
  `COUNTRY` varchar(255) DEFAULT NULL COMMENT 'Pays'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `near2u`.`matches` (
`ID` INT NOT NULL AUTO_INCREMENT,
`DATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`ID_TEAM1` INT NOT NULL ,
`ID_TEAM2` INT NOT NULL ,
`VICTOIRE` tinyint(2) NOT NULL COMMENT  ' 1 : vicoire de 1; 2 : victoire de 2; 0 : matchnul (possiblement redondant avec r�sultat)',
`RESULTAT` INT NOT NULL ,
`VALIDE` BOOLEAN DEFAULT NULL COMMENT 'vrai si lequipe adverse (idteam2) a valide le score ',
`AVIS1` TEXT NULL DEFAULT NULL COMMENT 'Avis de l''équipe 2 sur équipe 1',
`FAIRPLAY1` TINYINT NULL DEFAULT NULL COMMENT 'Fairplay de 1 selon 2'  ,
`AVIS2` TEXT NULL DEFAULT NULL COMMENT 'Avis de l''équipe 1 sur équipe 2' ,
`FAIRPLAY2` TINYINT NULL DEFAULT NULL COMMENT 'Fairplay de 2 selon 1',
PRIMARY KEY `PKEYID`(`ID`))
ENGINE = InnoDB;


CREATE TABLE `near2u`.`signals_teams` ( 
`ID_TEAM1` INT NOT NULL COMMENT 'équipe qui signale l''autre' ,
  `ID_TEAM2` INT NOT NULL COMMENT 'équipe signalé' ,  
`DATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP )
 ENGINE = InnoDB COMMENT = 'une équipe peut en signaler une autre';

 
 
CREATE TABLE `near2u`.`ref_sports` (
  `ID` int(11) NOT NULL,
  `NOM` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='table de références : idsport/nom du sport (non modifiable)';

ALTER TABLE  `near2u`.`ref_sports` ADD UNIQUE (
`NOM`
);

INSERT INTO `near2u`.`ref_sports` VALUES(0, 'football');
INSERT INTO `near2u`.`ref_sports` VALUES(1, 'basketball');

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

ALTER TABLE `signals_teams`
  ADD PRIMARY KEY (`ID_TEAM1`,`ID_TEAM2`);


--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
