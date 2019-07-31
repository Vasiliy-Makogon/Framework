-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 17, 2019 at 05:36 PM
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
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `module_key` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Модули системы';

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `module_name`, `module_key`) VALUES
(1, 'Пользователи', 'User'),
(26, 'Группы', 'Group'),
(27, 'Модули и контроллеры', 'Module'),
(30, 'Главная страница', 'Index'),
(40, 'GEO', 'Geo');

-- --------------------------------------------------------

--
-- Table structure for table `module-controller`
--

DROP TABLE IF EXISTS `module-controller`;
CREATE TABLE `module-controller` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `controller_id_module` tinyint(3) UNSIGNED NOT NULL,
  `controller_name` varchar(255) NOT NULL,
  `controller_key` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `module-controller`
--

INSERT INTO `module-controller` (`id`, `controller_id_module`, `controller_name`, `controller_key`) VALUES
(1, 1, 'Административная часть: Список пользователей', 'BackendMain'),
(3, 1, 'Административная часть: Редактирование данных пользователей', 'BackendEdit'),
(4, 1, 'Административная часть: Удаление пользователей', 'BackendDelete'),
(18, 26, 'Административная часть: Список административных групп', 'BackendMain'),
(19, 26, 'Административная часть: Редактирование административных групп', 'BackendEdit'),
(20, 26, 'Административная часть: Удаление административных групп', 'BackendDelete'),
(21, 27, 'Административная часть: Список модулей системы', 'BackendMain'),
(22, 27, 'Административная часть: Редактирование информации о модулях', 'EditModule'),
(23, 27, 'Административная часть: Удаление информации о модулях', 'DeleteModule'),
(24, 27, 'Административная часть: Редактирование информации о контроллерах', 'EditController'),
(25, 27, 'Административная часть: Удаление информации о контроллерах', 'DeleteController'),
(29, 30, 'Административная часть: Главная', 'Index'),
(53, 1, 'Пользовательская часть: Редактирование личных данных', 'FrontendEdit'),
(61, 1, 'Административная часть: Список стран', 'BackendCountryList'),
(62, 1, 'Административная часть: Изменение позиций стран', 'BackendCountryMotion'),
(63, 1, 'Административная часть: Редактирование данных стран', 'BackendCountryEdit'),
(64, 1, 'Административная часть: Изменение позиций регионов', 'BackendRegionMotion'),
(65, 1, 'Административная часть: Список регионов', 'BackendRegionList'),
(66, 1, 'Административная часть: Редактирование данных регионов', 'BackendRegionEdit'),
(68, 1, 'Административная часть: Приглашение анонимных пользователей', 'BackendInviteAnonymousUser'),
(69, 1, 'Административная часть: Редактирование данных городов', 'BackendCityEdit'),
(70, 1, 'Административная часть: Список городов', 'BackendCityList'),
(71, 40, 'Административная часть: Список федеральных округов', 'BackendDistrictList'),
(72, 40, 'Административная часть: Редактирование федеральных округов', 'BackendDistrictEdit'),
(73, 40, 'Административная часть: Список регионов', 'BackendRegionList'),
(74, 40, 'Административная часть: Редактирование регионов', 'BackendRegionEdit'),
(75, 40, 'Административная часть: Список городов', 'BackendCityList'),
(76, 40, 'Административная часть: Редактирование городов', 'BackendCityEdit');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module-controller`
--
ALTER TABLE `module-controller`
  ADD PRIMARY KEY (`id`),
  ADD KEY `controller_id_module` (`controller_id_module`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module`
--
ALTER TABLE `module`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
--
-- AUTO_INCREMENT for table `module-controller`
--
ALTER TABLE `module-controller`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `module-controller`
--
ALTER TABLE `module-controller`
  ADD CONSTRAINT `fk_module` FOREIGN KEY (`controller_id_module`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
