-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 23 dec 2014 om 10:37
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
-- Tabelstructuur voor tabel `emailAccount`
--

CREATE TABLE IF NOT EXISTS `emailAccount` (
`id` int(11) NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '993',
  `encrytion` enum('ssl','tls') COLLATE utf8_unicode_ci DEFAULT 'ssl',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fromName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fromEmail` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `syncedUntil` datetime DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailAddress`
--

CREATE TABLE IF NOT EXISTS `emailAddress` (
`id` int(11) NOT NULL,
  `messageId` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=142573 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailAttachment`
--

CREATE TABLE IF NOT EXISTS `emailAttachment` (
`id` int(11) NOT NULL,
  `messageId` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contentId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inline` tinyint(1) NOT NULL DEFAULT '0',
  `size` bigint(11) DEFAULT NULL,
  `imapPartNumber` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `foundInBody` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=191588 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailCcAddress`
--

CREATE TABLE IF NOT EXISTS `emailCcAddress` (
`id` int(11) NOT NULL,
  `messageId` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailFolder`
--

CREATE TABLE IF NOT EXISTS `emailFolder` (
`id` int(11) NOT NULL,
  `accountId` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uidValidity` int(11) DEFAULT NULL,
  `highestModSeq` int(11) DEFAULT NULL,
  `sortOrder` tinyint(4) NOT NULL DEFAULT '10'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=682 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailMessage`
--

CREATE TABLE IF NOT EXISTS `emailMessage` (
`id` int(11) NOT NULL,
  `accountId` int(11) NOT NULL,
  `threadId` int(11) DEFAULT NULL,
  `threadDisplay` tinyint(1) NOT NULL DEFAULT '0',
  `ownerUserId` int(11) NOT NULL,
  `messageId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inReplyTo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `references` text COLLATE utf8_unicode_ci,
  `date` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `replyTo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notificationTo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `_body` longtext COLLATE utf8_unicode_ci,
  `_quote` longtext COLLATE utf8_unicode_ci,
  `priority` enum('low','normal','high') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal',
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `answered` tinyint(1) NOT NULL DEFAULT '0',
  `forwarded` tinyint(1) NOT NULL DEFAULT '0',
  `flagged` tinyint(1) NOT NULL DEFAULT '0',
  `imapUid` int(11) DEFAULT NULL,
  `hasAttachments` tinyint(1) NOT NULL DEFAULT '0',
  `imapDeleted` tinyint(1) NOT NULL DEFAULT '0',
  `folderId` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=117588 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailThread`
--

CREATE TABLE IF NOT EXISTS `emailThread` (
`id` int(11) NOT NULL,
  `accountId` int(11) NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `answered` tinyint(1) NOT NULL DEFAULT '0',
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `flagged` tinyint(1) NOT NULL DEFAULT '0',
  `hasAttachments` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `excerpt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `emailAccount`
--
ALTER TABLE `emailAccount`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`);

--
-- Indexen voor tabel `emailAddress`
--
ALTER TABLE `emailAddress`
 ADD PRIMARY KEY (`id`), ADD KEY `messageId` (`messageId`), ADD KEY `email` (`email`);

--
-- Indexen voor tabel `emailAttachment`
--
ALTER TABLE `emailAttachment`
 ADD PRIMARY KEY (`id`), ADD KEY `messageId` (`messageId`);

--
-- Indexen voor tabel `emailCcAddress`
--
ALTER TABLE `emailCcAddress`
 ADD PRIMARY KEY (`id`), ADD KEY `messageId` (`messageId`);

--
-- Indexen voor tabel `emailFolder`
--
ALTER TABLE `emailFolder`
 ADD PRIMARY KEY (`id`), ADD KEY `accountId` (`accountId`);

--
-- Indexen voor tabel `emailMessage`
--
ALTER TABLE `emailMessage`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `imapUid` (`imapUid`,`folderId`), ADD KEY `owner` (`ownerUserId`), ADD KEY `threadId` (`threadId`), ADD KEY `accountId` (`accountId`), ADD KEY `imapDeleted` (`imapDeleted`), ADD KEY `folderId` (`folderId`), ADD KEY `messageId` (`messageId`), ADD KEY `threadDisplay` (`threadDisplay`);

--
-- Indexen voor tabel `emailThread`
--
ALTER TABLE `emailThread`
 ADD PRIMARY KEY (`id`), ADD KEY `accountId` (`accountId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `emailAccount`
--
ALTER TABLE `emailAccount`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT voor een tabel `emailAddress`
--
ALTER TABLE `emailAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=142573;
--
-- AUTO_INCREMENT voor een tabel `emailAttachment`
--
ALTER TABLE `emailAttachment`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=191588;
--
-- AUTO_INCREMENT voor een tabel `emailCcAddress`
--
ALTER TABLE `emailCcAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `emailFolder`
--
ALTER TABLE `emailFolder`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=682;
--
-- AUTO_INCREMENT voor een tabel `emailMessage`
--
ALTER TABLE `emailMessage`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=117588;
--
-- AUTO_INCREMENT voor een tabel `emailThread`
--
ALTER TABLE `emailThread`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `emailAddress`
--
ALTER TABLE `emailAddress`
ADD CONSTRAINT `emailAddress_ibfk_1` FOREIGN KEY (`messageId`) REFERENCES `emailMessage` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `emailAttachment`
--
ALTER TABLE `emailAttachment`
ADD CONSTRAINT `emailAttachment_ibfk_1` FOREIGN KEY (`messageId`) REFERENCES `emailMessage` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `emailCcAddress`
--
ALTER TABLE `emailCcAddress`
ADD CONSTRAINT `emailCcAddress_ibfk_1` FOREIGN KEY (`messageId`) REFERENCES `emailMessage` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `emailMessage`
--
ALTER TABLE `emailMessage`
ADD CONSTRAINT `emailMessage_ibfk_1` FOREIGN KEY (`accountId`) REFERENCES `emailAccount` (`id`),
ADD CONSTRAINT `emailMessage_ibfk_2` FOREIGN KEY (`threadId`) REFERENCES `emailThread` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `emailMessage_ibfk_3` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `emailMessage_ibfk_4` FOREIGN KEY (`folderId`) REFERENCES `emailFolder` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;