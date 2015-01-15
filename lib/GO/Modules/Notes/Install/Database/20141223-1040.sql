-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 23 dec 2014 om 10:40
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
-- Tabelstructuur voor tabel `notesNote`
--

CREATE TABLE IF NOT EXISTS `notesNote` (
`id` int(11) NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT 'yellow',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notesNoteImage`
--

CREATE TABLE IF NOT EXISTS `notesNoteImage` (
`id` int(11) NOT NULL,
  `noteId` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notesNoteListItem`
--

CREATE TABLE IF NOT EXISTS `notesNoteListItem` (
`id` int(11) NOT NULL,
  `noteId` int(11) NOT NULL,
  `text` text,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `sortOrder` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notesNoteRole`
--

CREATE TABLE IF NOT EXISTS `notesNoteRole` (
  `noteId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `readAccess` tinyint(1) NOT NULL DEFAULT '1',
  `editAccess` tinyint(1) NOT NULL DEFAULT '0',
  `deleteAccess` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `notesNote`
--
ALTER TABLE `notesNote`
 ADD PRIMARY KEY (`id`), ADD KEY `sortOrder` (`sortOrder`), ADD KEY `ownerUserId` (`ownerUserId`), ADD KEY `deleted` (`deleted`);

--
-- Indexen voor tabel `notesNoteImage`
--
ALTER TABLE `notesNoteImage`
 ADD PRIMARY KEY (`id`), ADD KEY `noteId` (`noteId`);

--
-- Indexen voor tabel `notesNoteListItem`
--
ALTER TABLE `notesNoteListItem`
 ADD PRIMARY KEY (`id`), ADD KEY `noteId` (`noteId`);

--
-- Indexen voor tabel `notesNoteRole`
--
ALTER TABLE `notesNoteRole`
 ADD PRIMARY KEY (`noteId`,`roleId`), ADD KEY `roleId` (`roleId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `notesNote`
--
ALTER TABLE `notesNote`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `notesNoteImage`
--
ALTER TABLE `notesNoteImage`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `notesNoteListItem`
--
ALTER TABLE `notesNoteListItem`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `notesNote`
--
ALTER TABLE `notesNote`
ADD CONSTRAINT `notesNote_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `notesNoteImage`
--
ALTER TABLE `notesNoteImage`
ADD CONSTRAINT `notesNoteImage_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `notesNoteListItem`
--
ALTER TABLE `notesNoteListItem`
ADD CONSTRAINT `notesNoteListItem_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `notesNoteRole`
--
ALTER TABLE `notesNoteRole`
ADD CONSTRAINT `notesNoteRole_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `notesNoteRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;