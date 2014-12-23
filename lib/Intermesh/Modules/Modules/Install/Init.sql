-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 23 dec 2014 om 11:34
-- Serverversie: 5.5.40-0ubuntu1
-- PHP-versie: 5.5.12-2ubuntu4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `go7`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `authRole`
--

CREATE TABLE IF NOT EXISTS `authRole` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `autoAdd` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userId` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1554 ;

--
-- Gegevens worden geëxporteerd voor tabel `authRole`
--

INSERT INTO `authRole` (`id`, `deleted`, `autoAdd`, `name`, `userId`) VALUES
(1, 0, 0, 'Admins', 1),
(2, 0, 0, 'Everyone', NULL);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `authToken`
--

CREATE TABLE IF NOT EXISTS `authToken` (
`id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `series` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `authUser`
--

CREATE TABLE IF NOT EXISTS `authUser` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `digest` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `loginCount` int(11) NOT NULL DEFAULT '0',
  `lastLogin` datetime DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1555 ;

--
-- Gegevens worden geëxporteerd voor tabel `authUser`
--

INSERT INTO `authUser` (`id`, `deleted`, `enabled`, `username`, `password`, `digest`, `createdAt`, `modifiedAt`, `loginCount`, `lastLogin`) VALUES
(1, 0, 1, 'admin', 'tJJlUNVIeWo2U', 'a22cb2151fa2cee42f8cd64000c1176b', '2014-07-21 14:01:17', '2014-12-22 11:00:40', 70, '2014-12-22 12:00:40');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `authUserRole`
--

CREATE TABLE IF NOT EXISTS `authUserRole` (
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `authUserRole`
--

INSERT INTO `authUserRole` (`userId`, `roleId`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `coreConfig`
--

CREATE TABLE IF NOT EXISTS `coreConfig` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `_value` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `coreSession`
--

CREATE TABLE IF NOT EXISTS `coreSession` (
  `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `modulesModule`
--

CREATE TABLE IF NOT EXISTS `modulesModule` (
`id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `version` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Gegevens worden geëxporteerd voor tabel `modulesModule`
--

INSERT INTO `modulesModule` (`id`, `name`, `type`, `deleted`, `version`) VALUES
(1, 'Intermesh\\Modules\\Modules\\ModulesModule', 'admin', 0, 0),
(2, 'Intermesh\\Modules\\Auth\\AuthModule', 'admin', 0, 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `modulesModuleRole`
--

CREATE TABLE IF NOT EXISTS `modulesModuleRole` (
  `moduleId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `useAccess` tinyint(1) NOT NULL DEFAULT '0',
  `createAccess` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `modulesModuleRole`
--

INSERT INTO `modulesModuleRole` (`moduleId`, `roleId`, `useAccess`, `createAccess`) VALUES
(1, 1, 1, 1),
(2, 1, 1, 1);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `authRole`
--
ALTER TABLE `authRole`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `userId` (`userId`), ADD UNIQUE KEY `name` (`name`), ADD KEY `autoAdd` (`autoAdd`), ADD KEY `deleted` (`deleted`);

--
-- Indexen voor tabel `authToken`
--
ALTER TABLE `authToken`
 ADD PRIMARY KEY (`id`), ADD KEY `userId` (`userId`,`series`);

--
-- Indexen voor tabel `authUser`
--
ALTER TABLE `authUser`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `username_2` (`username`), ADD KEY `deleted` (`deleted`);

--
-- Indexen voor tabel `authUserRole`
--
ALTER TABLE `authUserRole`
 ADD PRIMARY KEY (`userId`,`roleId`), ADD KEY `roleId` (`roleId`);

--
-- Indexen voor tabel `coreConfig`
--
ALTER TABLE `coreConfig`
 ADD PRIMARY KEY (`name`);

--
-- Indexen voor tabel `coreSession`
--
ALTER TABLE `coreSession`
 ADD PRIMARY KEY (`id`), ADD KEY `userId` (`userId`);

--
-- Indexen voor tabel `modulesModule`
--
ALTER TABLE `modulesModule`
 ADD PRIMARY KEY (`id`), ADD KEY `deleted` (`deleted`);

ALTER TABLE `modulesModule` ADD UNIQUE(`name`);

--
-- Indexen voor tabel `modulesModuleRole`
--
ALTER TABLE `modulesModuleRole`
 ADD PRIMARY KEY (`moduleId`,`roleId`), ADD KEY `roleId` (`roleId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `authRole`
--
ALTER TABLE `authRole`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1554;
--
-- AUTO_INCREMENT voor een tabel `authToken`
--
ALTER TABLE `authToken`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT voor een tabel `authUser`
--
ALTER TABLE `authUser`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1555;
--
-- AUTO_INCREMENT voor een tabel `modulesModule`
--
ALTER TABLE `modulesModule`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `authRole`
--
ALTER TABLE `authRole`
ADD CONSTRAINT `authRole_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `authToken`
--
ALTER TABLE `authToken`
ADD CONSTRAINT `authToken_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `authUserRole`
--
ALTER TABLE `authUserRole`
ADD CONSTRAINT `authUserRole_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `authUserRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `coreSession`
--
ALTER TABLE `coreSession`
ADD CONSTRAINT `coreSession_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `modulesModuleRole`
--
ALTER TABLE `modulesModuleRole`
ADD CONSTRAINT `modulesModuleRole_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `modulesModule` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `modulesModuleRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;