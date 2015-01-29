
CREATE TABLE IF NOT EXISTS `ticketsTicketRole` (
    `ticketId` int(11) NOT NULL,
    `roleId` int(11) NOT NULL,
    `read` tinyint(1) NOT NULL DEFAULT '0',
    `edit` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`ticketId`,`roleId`),
	FOREIGN KEY ( `ticketId` ) REFERENCES `ticketsTicket` (`id`) 
		ON DELETE CASCADE 
		ON UPDATE RESTRICT,
	FOREIGN KEY ( `roleId` ) REFERENCES `authRole` (`id`) 
		ON DELETE CASCADE 
		ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;