CREATE TABLE IF NOT EXISTS `bandsBandRole` (
  `bandId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `permissionType` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `bandsBandRole`
 ADD PRIMARY KEY (`bandId`,`roleId`,`permissionType`), ADD KEY `roleId` (`roleId`);

ALTER TABLE `bandsBandRole`
ADD CONSTRAINT `bandsBandRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `bandsBandRole_ibfk_1` FOREIGN KEY (`bandId`) REFERENCES `bandsBand` (`id`) ON DELETE CASCADE;

