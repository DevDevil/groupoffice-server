CREATE TABLE IF NOT EXISTS `bandsBandRole` (
	`bandId` int(11) NOT NULL,
	`roleId` int(11) NOT NULL,
	`readAccess` tinyint(1) NOT NULL DEFAULT '0',
	`editAccess` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`bandId`,`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `bandsBandRole` ADD FOREIGN KEY ( `bandId` ) REFERENCES `bandsBand` (
`id`
) ON DELETE CASCADE ON UPDATE RESTRICT ;

ALTER TABLE `bandsBandRole` ADD FOREIGN KEY ( `roleId` ) REFERENCES `authRole` (
`id`
) ON DELETE CASCADE ON UPDATE RESTRICT;