-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 24, 2014 at 10:12 AM
-- Server version: 5.5.40-0ubuntu1
-- PHP Version: 5.5.12-2ubuntu4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `groupoffice_serv`
--

-- --------------------------------------------------------

--
-- Table structure for table `projectsProject`
--

CREATE TABLE IF NOT EXISTS `projectsProject` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `finished` tinyint(1) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `sticky` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `projectsProjectRole`
--

CREATE TABLE IF NOT EXISTS `projectsProjectRole` (
  `projectId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `readAccess` tinyint(1) NOT NULL DEFAULT '1',
  `editAccess` tinyint(1) NOT NULL DEFAULT '0',
  `deleteAccess` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `projectsTask`
--

CREATE TABLE IF NOT EXISTS `projectsTask` (
  `id` int(11) NOT NULL,
  `projectId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `parentId` int(11) DEFAULT NULL,
  `previousId` int(11) DEFAULT NULL,
  `startTime` int(11) DEFAULT NULL,
  `sticky` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `projectsProject`
--
ALTER TABLE `projectsProject`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projectsProjectRole`
--
ALTER TABLE `projectsProjectRole`
 ADD KEY `projectId` (`projectId`), ADD KEY `roleId` (`roleId`), ADD KEY `projectId_2` (`projectId`);

--
-- Indexes for table `projectsTask`
--
ALTER TABLE `projectsTask`
 ADD PRIMARY KEY (`id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projectsProjectRole`
--
ALTER TABLE `projectsProjectRole`
ADD CONSTRAINT `projectsProjectRole_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `projectsProject` (`id`);
