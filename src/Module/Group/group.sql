-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 17, 2019 at 05:38 PM
-- Server version: 5.6.37
-- PHP Version: 7.1.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `basic`
--

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` tinyint(1) UNSIGNED NOT NULL,
  `group_name` varchar(30) NOT NULL,
  `group_active` tinyint(1) UNSIGNED NOT NULL,
  `group_alias` varchar(30) NOT NULL,
  `group_access` mediumtext NOT NULL COMMENT 'сериализованные доступы группы к модулям системы'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Административные группы';

--
-- Dumping data for table `group`
--

INSERT INTO `group` (`id`, `group_name`, `group_active`, `group_alias`, `group_access`) VALUES
(1, 'Администраторы', 1, 'administrator', 'a:7:{s:4:\"User\";a:13:{s:11:\"BackendMain\";s:1:\"1\";s:11:\"BackendEdit\";s:1:\"1\";s:13:\"BackendDelete\";s:1:\"1\";s:12:\"FrontendEdit\";s:1:\"1\";s:18:\"BackendCountryList\";s:1:\"1\";s:20:\"BackendCountryMotion\";s:1:\"1\";s:18:\"BackendCountryEdit\";s:1:\"1\";s:19:\"BackendRegionMotion\";s:1:\"1\";s:17:\"BackendRegionList\";s:1:\"1\";s:17:\"BackendRegionEdit\";s:1:\"1\";s:26:\"BackendInviteAnonymousUser\";s:1:\"1\";s:15:\"BackendCityEdit\";s:1:\"1\";s:15:\"BackendCityList\";s:1:\"1\";}s:5:\"Group\";a:3:{s:11:\"BackendMain\";s:1:\"1\";s:11:\"BackendEdit\";s:1:\"1\";s:13:\"BackendDelete\";s:1:\"1\";}s:6:\"Module\";a:5:{s:11:\"BackendMain\";s:1:\"1\";s:10:\"EditModule\";s:1:\"1\";s:12:\"DeleteModule\";s:1:\"1\";s:14:\"EditController\";s:1:\"1\";s:16:\"DeleteController\";s:1:\"1\";}s:5:\"Index\";a:1:{s:5:\"Index\";s:1:\"1\";}s:8:\"Category\";a:5:{s:11:\"BackendMain\";s:1:\"1\";s:13:\"BackendMotion\";s:1:\"1\";s:11:\"BackendEdit\";s:1:\"1\";s:13:\"BackendDelete\";s:1:\"1\";s:14:\"BackendAddList\";s:1:\"1\";}s:3:\"Geo\";a:6:{s:19:\"BackendDistrictList\";s:1:\"1\";s:19:\"BackendDistrictEdit\";s:1:\"1\";s:17:\"BackendRegionList\";s:1:\"1\";s:17:\"BackendRegionEdit\";s:1:\"1\";s:15:\"BackendCityList\";s:1:\"1\";s:15:\"BackendCityEdit\";s:1:\"1\";}s:4:\"test\";a:1:{s:5:\"test2\";s:1:\"1\";}}'),
(2, 'Пользователи', 1, 'user', 'a:6:{s:4:\"User\";a:11:{s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:12:\"FrontendEdit\";s:1:\"1\";s:18:\"BackendCountryList\";s:1:\"0\";s:20:\"BackendCountryMotion\";s:1:\"0\";s:18:\"BackendCountryEdit\";s:1:\"0\";s:19:\"BackendRegionMotion\";s:1:\"0\";s:17:\"BackendRegionList\";s:1:\"0\";s:17:\"BackendRegionEdit\";s:1:\"0\";s:26:\"BackendInviteAnonymousUser\";s:1:\"0\";}s:5:\"Group\";a:3:{s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";}s:6:\"Module\";a:5:{s:11:\"BackendMain\";s:1:\"0\";s:10:\"EditModule\";s:1:\"0\";s:12:\"DeleteModule\";s:1:\"0\";s:14:\"EditController\";s:1:\"0\";s:16:\"DeleteController\";s:1:\"0\";}s:5:\"Index\";a:1:{s:5:\"Index\";s:1:\"1\";}s:6:\"Advert\";a:9:{s:18:\"FrontendEditAdvert\";s:1:\"1\";s:23:\"FrontendUserAdvertsList\";s:1:\"1\";s:20:\"FrontendDeleteAdvert\";s:1:\"1\";s:16:\"FrontendUpAdvert\";s:1:\"1\";s:20:\"FrontendActiveAdvert\";s:1:\"1\";s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:17:\"BackendSetActions\";s:1:\"0\";}s:8:\"Category\";a:5:{s:11:\"BackendMain\";s:1:\"0\";s:13:\"BackendMotion\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:14:\"BackendAddList\";s:1:\"0\";}}'),
(3, 'Гости', 1, 'guest', 'a:6:{s:4:\"User\";a:11:{s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:12:\"FrontendEdit\";s:1:\"0\";s:18:\"BackendCountryList\";s:1:\"0\";s:20:\"BackendCountryMotion\";s:1:\"0\";s:18:\"BackendCountryEdit\";s:1:\"0\";s:19:\"BackendRegionMotion\";s:1:\"0\";s:17:\"BackendRegionList\";s:1:\"0\";s:17:\"BackendRegionEdit\";s:1:\"0\";s:26:\"BackendInviteAnonymousUser\";s:1:\"0\";}s:5:\"Group\";a:3:{s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";}s:6:\"Module\";a:5:{s:11:\"BackendMain\";s:1:\"0\";s:10:\"EditModule\";s:1:\"0\";s:12:\"DeleteModule\";s:1:\"0\";s:14:\"EditController\";s:1:\"0\";s:16:\"DeleteController\";s:1:\"0\";}s:5:\"Index\";a:1:{s:5:\"Index\";s:1:\"0\";}s:6:\"Advert\";a:9:{s:18:\"FrontendEditAdvert\";s:1:\"1\";s:23:\"FrontendUserAdvertsList\";s:1:\"0\";s:20:\"FrontendDeleteAdvert\";s:1:\"0\";s:16:\"FrontendUpAdvert\";s:1:\"0\";s:20:\"FrontendActiveAdvert\";s:1:\"0\";s:11:\"BackendMain\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:17:\"BackendSetActions\";s:1:\"0\";}s:8:\"Category\";a:5:{s:11:\"BackendMain\";s:1:\"0\";s:13:\"BackendMotion\";s:1:\"0\";s:11:\"BackendEdit\";s:1:\"0\";s:13:\"BackendDelete\";s:1:\"0\";s:14:\"BackendAddList\";s:1:\"0\";}}');

-- --------------------------------------------------------

--
-- Table structure for table `group-access`
--

DROP TABLE IF EXISTS `group-access`;
CREATE TABLE `group-access` (
  `id_group` tinyint(5) UNSIGNED NOT NULL,
  `id_controller` smallint(5) UNSIGNED NOT NULL,
  `access` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `group-access`
--

INSERT INTO `group-access` (`id_group`, `id_controller`, `access`) VALUES
(1, 1, 1),
(1, 3, 1),
(1, 4, 1),
(1, 18, 1),
(1, 19, 1),
(1, 20, 1),
(1, 21, 1),
(1, 22, 1),
(1, 23, 1),
(1, 24, 1),
(1, 25, 1),
(1, 29, 1),
(1, 53, 1),
(1, 61, 1),
(1, 62, 1),
(1, 63, 1),
(1, 64, 1),
(1, 65, 1),
(1, 66, 1),
(1, 68, 1),
(1, 69, 1),
(1, 70, 1),
(1, 71, 1),
(1, 72, 1),
(1, 73, 1),
(1, 74, 1),
(1, 75, 1),
(1, 76, 1),
(2, 1, 0),
(2, 3, 0),
(2, 4, 0),
(2, 18, 0),
(2, 19, 0),
(2, 20, 0),
(2, 21, 0),
(2, 22, 0),
(2, 23, 0),
(2, 24, 0),
(2, 25, 0),
(2, 29, 1),
(2, 53, 1),
(2, 61, 0),
(2, 62, 0),
(2, 63, 0),
(2, 64, 0),
(2, 65, 0),
(2, 66, 0),
(2, 68, 0),
(3, 1, 0),
(3, 3, 0),
(3, 4, 0),
(3, 18, 0),
(3, 19, 0),
(3, 20, 0),
(3, 21, 0),
(3, 22, 0),
(3, 23, 0),
(3, 24, 0),
(3, 25, 0),
(3, 29, 0),
(3, 53, 0),
(3, 61, 0),
(3, 62, 0),
(3, 63, 0),
(3, 64, 0),
(3, 65, 0),
(3, 66, 0),
(3, 68, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `group`
--
ALTER TABLE `group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_alias` (`group_alias`);

--
-- Indexes for table `group-access`
--
ALTER TABLE `group-access`
  ADD UNIQUE KEY `ga_unique` (`id_group`,`id_controller`),
  ADD KEY `fk_controller` (`id_controller`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
  MODIFY `id` tinyint(1) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `group-access`
--
ALTER TABLE `group-access`
  ADD CONSTRAINT `fk_controller` FOREIGN KEY (`id_controller`) REFERENCES `module-controller` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_group` FOREIGN KEY (`id_group`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
