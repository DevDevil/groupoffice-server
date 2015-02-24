-- -----------------------------------------------------
-- Table `projectsProposal`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `projectsProposal` (
  `id` INT NOT NULL,
  `createdBy` INT NOT NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `projectsProject`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `projectsProject` (
  `id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT '													',
  `contactId` INT NULL,
  `contactName` VARCHAR(255) NULL,
  `contactEmail` VARCHAR(255) NULL,
  `companyId` INT NULL,
  `companyName` VARCHAR(255) NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  INDEX `fk_projectsProject_projectsProposal_idx` (`id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_projectsProject_projectsProposal`
    FOREIGN KEY (`id`)
    REFERENCES `projectsProposal` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `projectsProposalItem`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `projectsProposalItem` (
  `id` INT NOT NULL,
  `revision` INT NOT NULL DEFAULT 0,
  `proposalId` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NULL,
  `agreedAt` DATETIME NULL,
  `sortOrder` INT NOT NULL DEFAULT 0,
  `minHours` DOUBLE NULL,
  `maxHours` DOUBLE NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_projectsProposalItem_projectsProposal1_idx` (`proposalId` ASC),
  CONSTRAINT `fk_projectsProposalItem_projectsProposal1`
    FOREIGN KEY (`proposalId`)
    REFERENCES `projectsProposal` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;