ALTER TABLE `modulesModuleRole`
  DROP `useAccess`,
  DROP `createAccess`;

ALTER TABLE `modulesModuleRole` ADD `permissionType` INT NOT NULL ; 

ALTER TABLE `modulesModuleRole` DROP FOREIGN KEY `modulesModuleRole_ibfk_1`; ALTER TABLE `modulesModuleRole` DROP FOREIGN KEY `modulesModuleRole_ibfk_2`; 

ALTER TABLE modulesModuleRole DROP PRIMARY KEY;

ALTER TABLE `modulesModuleRole` ADD PRIMARY KEY( `moduleId`, `roleId`, `permissionType`); 

ALTER TABLE `modulesModuleRole` ADD FOREIGN KEY (`moduleId`) REFERENCES `modulesModule`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; 
ALTER TABLE `modulesModuleRole` ADD FOREIGN KEY (`roleId`) REFERENCES `authRole`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; 