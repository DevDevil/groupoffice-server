CREATE TABLE IF NOT EXISTS `bandsAlbum` (
`id` int(11) NOT NULL,
  `bandId` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `bandsBand` (
`id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ownerUserId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `bandsAlbum`
 ADD PRIMARY KEY (`id`), ADD KEY `bandId` (`bandId`), ADD KEY `ownerUserId` (`ownerUserId`);

ALTER TABLE `bandsBand`
 ADD PRIMARY KEY (`id`), ADD KEY `ownerUserId` (`ownerUserId`);


ALTER TABLE `bandsAlbum`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bandsBand`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bandsAlbum`
ADD CONSTRAINT `bandsAlbum_ibfk_2` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`),
ADD CONSTRAINT `bandsAlbum_ibfk_1` FOREIGN KEY (`bandId`) REFERENCES `bandsBand` (`id`) ON DELETE CASCADE;

ALTER TABLE `bandsBand`
ADD CONSTRAINT `bandsBand_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`);