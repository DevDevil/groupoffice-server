
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
  `_photoFilePath` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` text COLLATE utf8_unicode_ci,
  `isCompany` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `IBAN` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `registrationNumber` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `companyContactId` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2043 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2587 ;

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

--
-- Indexen voor geëxporteerde tabellen
--

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
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `contactsContact`
--
ALTER TABLE `contactsContact`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2043;
--
-- AUTO_INCREMENT voor een tabel `contactsContactAddress`
--
ALTER TABLE `contactsContactAddress`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2587;
--
-- Beperkingen voor geëxporteerde tabellen
--

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;