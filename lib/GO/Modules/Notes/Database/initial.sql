-- --------------------------------------------------------

--
-- Table structure for table `notesNote`
--

CREATE TABLE IF NOT EXISTS `notesNote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerUserId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT 'yellow',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sortOrder` (`sortOrder`),
  KEY `ownerUserId` (`ownerUserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notesNoteListItem`
--

CREATE TABLE IF NOT EXISTS `notesNoteListItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noteId` int(11) NOT NULL,
  `text` text,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `noteId` (`noteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notesNoteRole`
--

CREATE TABLE IF NOT EXISTS `notesNoteRole` (
  `noteId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `readAccess` tinyint(1) NOT NULL DEFAULT '1',
  `editAccess` tinyint(1) NOT NULL DEFAULT '0',
  `deleteAccess` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`noteId`,`roleId`),
  KEY `roleId` (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notesNoteImage`
--

CREATE TABLE IF NOT EXISTS `notesNoteImage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noteId` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `noteId` (`noteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for table `notesNote`
--
ALTER TABLE `notesNote`
  ADD CONSTRAINT `notesNote_ibfk_1` FOREIGN KEY (`ownerUserId`) REFERENCES `authUser` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notesNoteListItem`
--
ALTER TABLE `notesNoteListItem`
  ADD CONSTRAINT `notesNoteListItem_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notesNoteRole`
--
ALTER TABLE `notesNoteRole`
  ADD CONSTRAINT `notesNoteRole_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notesNoteRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `authRole` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notesNoteImage`
--
ALTER TABLE `notesNoteImage`
  ADD CONSTRAINT `notesNoteImage_ibfk_1` FOREIGN KEY (`noteId`) REFERENCES `notesNote` (`id`) ON DELETE CASCADE;