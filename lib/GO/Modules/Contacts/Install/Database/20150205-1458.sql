ALTER TABLE `contactsContactRole`
  DROP `readAccess`,
  DROP `uploadAccess`,
  DROP `editAccess`,
  DROP `deleteAccess`;

ALTER TABLE `contactsContactRole` DROP FOREIGN KEY `contactsContactRole_ibfk_2`; ALTER TABLE `contactsContactRole` DROP FOREIGN KEY `contactsContactRole_ibfk_1`; 

ALTER TABLE contactsContactRole DROP PRIMARY KEY;
ALTER TABLE contactsContactRole DROP INDEX roleId;

ALTER TABLE `contactsContactRole` ADD `permissionType` INT NOT NULL ; 
ALTER TABLE `contactsContactRole` ADD PRIMARY KEY( `contactId`, `roleId`, `permissionType`); 

ALTER TABLE `contactsContactRole` ADD FOREIGN KEY (`contactId`) REFERENCES `contactsContact`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; 
ALTER TABLE `contactsContactRole` ADD FOREIGN KEY (`roleId`) REFERENCES `authRole`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; 