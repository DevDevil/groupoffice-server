-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 26 nov 2014 om 15:31
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
-- Tabelstructuur voor tabel `announcementsAnnouncement`
--

CREATE TABLE IF NOT EXISTS `announcementsAnnouncement` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `imagePath` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1553 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1554 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `authUserRole`
--

CREATE TABLE IF NOT EXISTS `authUserRole` (
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContact`
--

CREATE TABLE IF NOT EXISTS `contactsContact` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `userId` int(11) DEFAULT NULL,
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `prefixes` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `firstName` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `middleName` varchar(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `lastName` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `suffixes` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gender` enum('M','F') COLLATE utf8_unicode_ci DEFAULT NULL,
  `photoFilePath` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` text COLLATE utf8_unicode_ci,
  `isCompany` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `IBAN` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `registrationNumber` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `companyContactId` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2041 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactAddress`
--

CREATE TABLE IF NOT EXISTS `contactsContactAddress` (
`id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `zipCode` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` char(2) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactCustomFields`
--

CREATE TABLE IF NOT EXISTS `contactsContactCustomFields` (
  `id` int(11) NOT NULL,
  `Speelsterkte dubbel` double DEFAULT '9',
  `Lid sinds` date DEFAULT NULL,
  `zaterdagInvaller` tinyint(1) NOT NULL DEFAULT '0',
  `Speelsterkte enkel` double DEFAULT '9',
  `Bondsnummer` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `zondagInvaller` tinyint(1) NOT NULL DEFAULT '0',
  `test` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'test'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactDate`
--

CREATE TABLE IF NOT EXISTS `contactsContactDate` (
`id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'birthday',
  `date` date NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1994 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactEmailAddress`
--

CREATE TABLE IF NOT EXISTS `contactsContactEmailAddress` (
`id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'work',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1911 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactPhone`
--

CREATE TABLE IF NOT EXISTS `contactsContactPhone` (
`id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'work,voice',
  `number` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2586 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactRole`
--

CREATE TABLE IF NOT EXISTS `contactsContactRole` (
  `contactId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `readAccess` tinyint(1) NOT NULL DEFAULT '1',
  `uploadAccess` tinyint(1) NOT NULL DEFAULT '0',
  `editAccess` tinyint(1) NOT NULL DEFAULT '0',
  `deleteAccess` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `contactsContactTag`
--

CREATE TABLE IF NOT EXISTS `contactsContactTag` (
  `contactId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL
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
-- Tabelstructuur voor tabel `customFieldsField`
--

CREATE TABLE IF NOT EXISTS `customFieldsField` (
`id` int(11) NOT NULL,
  `fieldSetId` int(11) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `databaseName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `placeholder` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `defaultValue` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `_data` text COLLATE utf8_unicode_ci,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `filterable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `customFieldsFieldSet`
--

CREATE TABLE IF NOT EXISTS `customFieldsFieldSet` (
`id` int(11) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `modelName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=63510 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailCcAddress`
--

CREATE TABLE IF NOT EXISTS `emailCcAddress` (
`id` int(11) NOT NULL,
  `messageId` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7840 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailFolder`
--

CREATE TABLE IF NOT EXISTS `emailFolder` (
`id` int(11) NOT NULL,
  `accountId` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `highestSyncedUid` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=210 ;

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
  `fromPersonal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `replyTo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notificationTo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `_body` longtext COLLATE utf8_unicode_ci,
  `_quote` longtext COLLATE utf8_unicode_ci,
  `contentType` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text/html',
  `priority` enum('low','normal','high') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal',
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `answered` tinyint(1) NOT NULL DEFAULT '0',
  `forwarded` tinyint(1) NOT NULL DEFAULT '0',
  `imapUid` int(11) NOT NULL,
  `hasAttachments` tinyint(1) NOT NULL DEFAULT '0',
  `imapDeleted` tinyint(1) NOT NULL DEFAULT '0',
  `folderId` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=38948 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `emailToAddress`
--

CREATE TABLE IF NOT EXISTS `emailToAddress` (
`id` int(11) NOT NULL,
  `messageId` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=51941 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `filesFile`
--

CREATE TABLE IF NOT EXISTS `filesFile` (
`id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `parentId` int(11) DEFAULT NULL,
  `isFolder` tinyint(1) NOT NULL DEFAULT '0',
  `readOnly` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT '0',
  `contentType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modelId` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `imapAccount`
--

CREATE TABLE IF NOT EXISTS `imapAccount` (
`id` int(11) NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '993',
  `encrytion` enum('ssl','tls') COLLATE utf8_unicode_ci DEFAULT 'ssl',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `syncedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `modulesModule`
--

CREATE TABLE IF NOT EXISTS `modulesModule` (
`id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

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

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tagsTag`
--

CREATE TABLE IF NOT EXISTS `tagsTag` (
`id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

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
-- Indexen voor tabel `announcementsAnnouncement`
--
ALTER TABLE `announcementsAnnouncement`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`), ADD KEY `deleted` (`deleted`);

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
-- Indexen voor tabel `contactsContact`
--
ALTER TABLE `contactsContact`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`), ADD KEY `deleted` (`deleted`), ADD KEY `userId` (`userId`), ADD KEY `companyContactId` (`companyContactId`);

--
-- Indexen voor tabel `contactsContactAddress`
--
ALTER TABLE `contactsContactAddress`
 ADD PRIMARY KEY (`id`), ADD KEY `contactId` (`contactId`);

--
-- Indexen voor tabel `contactsContactCustomFields`
--
ALTER TABLE `contactsContactCustomFields`
 ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `contactsContactDate`
--
ALTER TABLE `contactsContactDate`
 ADD PRIMARY KEY (`id`), ADD KEY `contactId` (`contactId`);

--
-- Indexen voor tabel `contactsContactEmailAddress`
--
ALTER TABLE `contactsContactEmailAddress`
 ADD PRIMARY KEY (`id`), ADD KEY `contactId` (`contactId`);

--
-- Indexen voor tabel `contactsContactPhone`
--
ALTER TABLE `contactsContactPhone`
 ADD PRIMARY KEY (`id`), ADD KEY `contactId` (`contactId`);

--
-- Indexen voor tabel `contactsContactRole`
--
ALTER TABLE `contactsContactRole`
 ADD PRIMARY KEY (`contactId`,`roleId`), ADD KEY `roleId` (`roleId`), ADD KEY `read` (`readAccess`,`editAccess`,`deleteAccess`);

--
-- Indexen voor tabel `contactsContactTag`
--
ALTER TABLE `contactsContactTag`
 ADD PRIMARY KEY (`contactId`,`tagId`), ADD KEY `tagId` (`tagId`);

--
-- Indexen voor tabel `coreSession`
--
ALTER TABLE `coreSession`
 ADD PRIMARY KEY (`id`), ADD KEY `userId` (`userId`);

--
-- Indexen voor tabel `customFieldsField`
--
ALTER TABLE `customFieldsField`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `databaseName` (`databaseName`), ADD KEY `fieldSetId` (`fieldSetId`), ADD KEY `deleted` (`deleted`), ADD KEY `sortOrder` (`sortOrder`);

--
-- Indexen voor tabel `customFieldsFieldSet`
--
ALTER TABLE `customFieldsFieldSet`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`), ADD KEY `model` (`modelName`), ADD KEY `deleted` (`deleted`);

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
-- Indexen voor tabel `emailAccount`
--
ALTER TABLE `emailAccount`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`);

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
-- Indexen voor tabel `emailToAddress`
--
ALTER TABLE `emailToAddress`
 ADD PRIMARY KEY (`id`), ADD KEY `messageId` (`messageId`), ADD KEY `email` (`email`);

--
-- Indexen voor tabel `filesFile`
--
ALTER TABLE `filesFile`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `parentId` (`parentId`,`isFolder`,`name`), ADD KEY `ownerUserId` (`ownerUserId`,`parentId`), ADD KEY `folderId` (`parentId`), ADD KEY `isFolder` (`isFolder`), ADD KEY `deleted` (`deleted`);

--
-- Indexen voor tabel `imapAccount`
--
ALTER TABLE `imapAccount`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`);

--
-- Indexen voor tabel `modulesModule`
--
ALTER TABLE `modulesModule`
 ADD PRIMARY KEY (`id`), ADD KEY `deleted` (`deleted`);

--
-- Indexen voor tabel `modulesModuleRole`
--
ALTER TABLE `modulesModuleRole`
 ADD PRIMARY KEY (`moduleId`,`roleId`), ADD KEY `roleId` (`roleId`);

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
-- Indexen voor tabel `tagsTag`
--
ALTER TABLE `tagsTag`
 ADD PRIMARY KEY (`id`), ADD KEY `name` (`name`);

--
-- Indexen voor tabel `timelineItem`
--
ALTER TABLE `timelineItem`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`,`contactId`), ADD KEY `contactId` (`contactId`), ADD KEY `deleted` (`deleted`), ADD KEY `imapMessageId` (`imapMessageId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `announcementsAnnouncement`
--
ALTER TABLE `announcementsAnnouncement`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT voor een tabel `authRole`
--
ALTER TABLE `authRole`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1553;
--
-- AUTO_INCREMENT voor een tabel `authToken`
--
ALTER TABLE `authToken`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `authUser`
--
ALTER TABLE `authUser`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1554;
--
-- AUTO_INCREMENT voor een tabel `contactsContact`
--
ALTER TABLE `contactsContact`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2041;
--
-- AUTO_INCREMENT voor een tabel `contactsContactAddress`
--
ALTER TABLE `contactsContactAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `contactsContactDate`
--
ALTER TABLE `contactsContactDate`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1994;
--
-- AUTO_INCREMENT voor een tabel `contactsContactEmailAddress`
--
ALTER TABLE `contactsContactEmailAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1911;
--
-- AUTO_INCREMENT voor een tabel `contactsContactPhone`
--
ALTER TABLE `contactsContactPhone`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2586;
--
-- AUTO_INCREMENT voor een tabel `customFieldsField`
--
ALTER TABLE `customFieldsField`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=40;
--
-- AUTO_INCREMENT voor een tabel `customFieldsFieldSet`
--
ALTER TABLE `customFieldsFieldSet`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT voor een tabel `emailAccount`
--
ALTER TABLE `emailAccount`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT voor een tabel `emailAttachment`
--
ALTER TABLE `emailAttachment`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=63510;
--
-- AUTO_INCREMENT voor een tabel `emailCcAddress`
--
ALTER TABLE `emailCcAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7840;
--
-- AUTO_INCREMENT voor een tabel `emailFolder`
--
ALTER TABLE `emailFolder`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=210;
--
-- AUTO_INCREMENT voor een tabel `emailMessage`
--
ALTER TABLE `emailMessage`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=38948;
--
-- AUTO_INCREMENT voor een tabel `emailToAddress`
--
ALTER TABLE `emailToAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=51941;
--
-- AUTO_INCREMENT voor een tabel `filesFile`
--
ALTER TABLE `filesFile`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT voor een tabel `imapAccount`
--
ALTER TABLE `imapAccount`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `modulesModule`
--
ALTER TABLE `modulesModule`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
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
-- AUTO_INCREMENT voor een tabel `tagsTag`
--
ALTER TABLE `tagsTag`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT voor een tabel `timelineItem`
--
ALTER TABLE `timelineItem`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `announcementsAnnouncement`
--
ALTER TABLE `announcementsAnnouncement`
ADD CONSTRAINT `announcementsAnnouncement_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`);

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
-- Beperkingen voor tabel `contactsContact`
--
ALTER TABLE `contactsContact`
ADD CONSTRAINT `contactsContact_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `contactsContact_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `contactsContact_ibfk_3` FOREIGN KEY (`companyContactId`) REFERENCES `contactsContact` (`id`) ON DELETE SET NULL;

--
-- Beperkingen voor tabel `contactsContactAddress`
--
ALTER TABLE `contactsContactAddress`
ADD CONSTRAINT `contactsContactAddress_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactCustomFields`
--
ALTER TABLE `contactsContactCustomFields`
ADD CONSTRAINT `contactsContactCustomFields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactDate`
--
ALTER TABLE `contactsContactDate`
ADD CONSTRAINT `contactsContactDate_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactEmailAddress`
--
ALTER TABLE `contactsContactEmailAddress`
ADD CONSTRAINT `contactsContactEmailAddress_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactPhone`
--
ALTER TABLE `contactsContactPhone`
ADD CONSTRAINT `contactsContactPhone_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactRole`
--
ALTER TABLE `contactsContactRole`
ADD CONSTRAINT `contactsContactRole_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `contactsContactRole_ibfk_2` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `contactsContactTag`
--
ALTER TABLE `contactsContactTag`
ADD CONSTRAINT `contactsContactTag_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `contactsContactTag_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tagsTag` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `coreSession`
--
ALTER TABLE `coreSession`
ADD CONSTRAINT `coreSession_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

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
ADD CONSTRAINT `emailMessage_ibfk_2` FOREIGN KEY (`threadId`) REFERENCES `emailMessage` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `emailMessage_ibfk_3` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `emailMessage_ibfk_4` FOREIGN KEY (`folderId`) REFERENCES `emailFolder` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `emailToAddress`
--
ALTER TABLE `emailToAddress`
ADD CONSTRAINT `emailToAddress_ibfk_1` FOREIGN KEY (`messageId`) REFERENCES `emailMessage` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `modulesModuleRole`
--
ALTER TABLE `modulesModuleRole`
ADD CONSTRAINT `modulesModuleRole_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `modulesModule` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `modulesModuleRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE;

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

--
-- Beperkingen voor tabel `timelineItem`
--
ALTER TABLE `timelineItem`
ADD CONSTRAINT `timelineItem_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `timelineItem_ibfk_2` FOREIGN KEY (`contactId`) REFERENCES `contactsContact` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--


INSERT INTO `authUser` (`id`, `deleted`, `enabled`, `username`, `password`, `digest`, `createdAt`, `modifiedAt`) VALUES
(1, 0, 1, 'admin', '$1$DbSzAYcF$oc9bUIm.SBRjCD24ZcKg//', '508fd3bc6f1ecfedaa475586ce0b4f2f', '2014-07-21 14:01:17', '2014-08-05 15:16:05');

INSERT INTO `authRole` (`id`, `deleted`, `autoAdd`, `name`, `userId`) VALUES
(1, 0, 0, 'Admins', 1),
(2, 0, 0, 'Everyone', NULL);

-- --------------------------------------------------------



-- --------------------------------------------------------

-- Gegevens worden geëxporteerd voor tabel `authUserRole`
--

INSERT INTO `authUserRole` (`userId`, `roleId`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

INSERT INTO `modulesModule` (`id`, `name`, `type`, `deleted`) VALUES
(1, 'contacts', 'user', 0),
(4, 'notes', 'user', 0),
(5, 'roles', 'admin', 0),
(6, 'users', 'admin', 0),
(7, 'apibrowser', 'dev', 0),
(8, 'customfields', 'admin', 0),
(9, 'helloworld', 'user', 0),
(10, 'announcements', 'user', 0),
(10, 'email', 'user', 0);

INSERT INTO `modulesModuleRole` (`moduleId`, `roleId`, `useAccess`, `createAccess`) VALUES
(1, 1, 1, 1),
(1, 2, 1, 0),
(4, 1, 1, 1),
(5, 1, 1, 1),
(6, 1, 1, 1),
(7, 1, 1, 1),
(8, 1, 1, 1),
(9, 1, 1, 1),
(10, 1, 1, 1),
(10, 2, 1, 0);
