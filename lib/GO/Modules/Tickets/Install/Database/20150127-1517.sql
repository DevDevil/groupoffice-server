
CREATE TABLE IF NOT EXISTS `ticketsTicketCustomFields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ticketsTicketCustomFields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ticketsTicket` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;