CREATE TABLE IF NOT EXISTS `ticketsAgent` (
  `userId` INT NOT NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  PRIMARY KEY (`userId`))
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `ticketsTicket` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `number` VARCHAR(45) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `agentUserId` INT NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ticketsTicket_agentUserId` (`agentUserId` ASC),
  CONSTRAINT `fk_ticketsTicket_ticketsAgent`
    FOREIGN KEY (`agentUserId`)
    REFERENCES `ticketsAgent` (`userId`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;

