
CREATE TABLE IF NOT EXISTS `authOauth2AccessToken` (
  `accessToken` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `clientId` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `userId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `authOauth2AccessToken`
--
ALTER TABLE `authOauth2AccessToken`
 ADD PRIMARY KEY (`accessToken`);
