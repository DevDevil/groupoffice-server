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



--
-- Indexen voor geëxporteerde tabellen
--

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
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

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