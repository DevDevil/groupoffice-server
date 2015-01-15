-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 23 dec 2014 om 10:41
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
-- Tabelstructuur voor tabel `timelineItem`
--

CREATE TABLE IF NOT EXISTS `timelineItem` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ownerUserId` int(11) NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `createdAt` datetime NOT NULL,
  `contactId` int(11) NOT NULL,
  `text` text COLLATE utf8_unicode_ci,
  `imapMessageId` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;



--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `timelineItem`
--
ALTER TABLE `timelineItem`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`,`contactId`), ADD KEY `contactId` (`contactId`), ADD KEY `deleted` (`deleted`), ADD KEY `imapMessageId` (`imapMessageId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `timelineItem`
--
ALTER TABLE `timelineItem`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `timelineItem`
--
ALTER TABLE `timelineItem`
ADD CONSTRAINT `timelineItem_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `timelineItem_ibfk_2` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;