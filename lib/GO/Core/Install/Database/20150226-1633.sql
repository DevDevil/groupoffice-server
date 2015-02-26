
CREATE TABLE IF NOT EXISTS `emailSmtpAccount` (
`id` int(11) NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `hostname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '25',
  `encryption` enum('ssl','tls') COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fromEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;