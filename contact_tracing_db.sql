-- DB initialisation
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `contact_tracing_db` DEFAULT CHARACTER SET utf8mb4;
USE `contact_tracing_db`;

DROP TABLE IF EXISTS `visitors`;
DROP TABLE IF EXISTS `check_ins`;

CREATE TABLE `visitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_number` varchar(255)  NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255)  NULL,
  `last_name` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_id_number` (`id_number`),
  INDEX `idx_first_name` (`first_name`),
  INDEX `idx_last_name` (`last_name`),
  INDEX `idx_barangay` (`barangay`),
  INDEX `idx_city` (`city`),
  INDEX `idx_province` (`province`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `check_ins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visitor_id` int NOT NULL,
  `check_in_time` datetime NOT NULL,
  `check_out_time` datetime  NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_check_in_time` (`check_in_time`),
  INDEX `idx_check_out_time` (`check_out_time`),
  FOREIGN KEY (`visitor_id`) REFERENCES `visitors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
