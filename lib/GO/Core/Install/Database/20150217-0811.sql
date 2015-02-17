CREATE TABLE IF NOT EXISTS `authBrowserToken` (
  `accessToken` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `XSRFToken` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(11) NOT NULL,
  `expiresAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `authBrowserToken`
 ADD PRIMARY KEY (`accessToken`), ADD KEY `userId` (`userId`);


ALTER TABLE `authBrowserToken`
ADD CONSTRAINT `authBrowserToken_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;
