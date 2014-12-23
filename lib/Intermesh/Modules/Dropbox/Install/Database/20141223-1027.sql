-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 23 dec 2014 om 10:27
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
-- Tabelstructuur voor tabel `dropboxAccount`
--

CREATE TABLE IF NOT EXISTS `dropboxAccount` (
  `ownerUserId` int(11) NOT NULL,
  `accessToken` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `requestToken` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deltaCursor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dropboxUserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `dropboxAccountFolder`
--

CREATE TABLE IF NOT EXISTS `dropboxAccountFolder` (
  `accountId` int(11) NOT NULL,
  `folderId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `dropboxAccount`
--
ALTER TABLE `dropboxAccount`
 ADD PRIMARY KEY (`ownerUserId`);

--
-- Indexen voor tabel `dropboxAccountFolder`
--
ALTER TABLE `dropboxAccountFolder`
 ADD PRIMARY KEY (`accountId`,`folderId`), ADD KEY `folderId` (`folderId`);

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `dropboxAccount`
--
ALTER TABLE `dropboxAccount`
ADD CONSTRAINT `dropboxAccount_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `dropboxAccountFolder`
--
ALTER TABLE `dropboxAccountFolder`
ADD CONSTRAINT `dropboxAccountFolder_ibfk_1` FOREIGN KEY (`folderId`) REFERENCES `filesFile` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `dropboxAccountFolder_ibfk_2` FOREIGN KEY (`accountId`) REFERENCES `dropboxAccount` (`ownerUserId`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;