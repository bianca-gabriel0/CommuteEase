-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 04:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

--
-- Database: `commute_ease`
--
CREATE DATABASE IF NOT EXISTS `commute_ease` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `commute_ease`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `schedule`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `day` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `destination` varchar(40) NOT NULL,
  `departure_time` time NOT NULL,
  `estimated_arrival` time NOT NULL,
  `frequency` varchar(40) NOT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Optional sample data (for quick testing)
-- --------------------------------------------------------
INSERT INTO `users` (`username`, `email`, `password`) VALUES
('john_doe', 'john@example.com', '$2y$10$examplehash1234567890abcdef'),
('jane_smith', 'jane@example.com', '$2y$10$examplehash0987654321fedcba');

INSERT INTO `schedule` (`day`, `location`, `type`, `destination`, `departure_time`, `estimated_arrival`, `frequency`) VALUES
('Monday', 'Manila', 'Bus', 'Quezon City', '08:00:00', '08:45:00', 'Every 30 mins'),
('Tuesday', 'Makati', 'Jeepney', 'Taguig', '07:30:00', '08:10:00', 'Every 15 mins');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
