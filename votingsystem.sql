-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2025 at 05:21 AM
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
-- Database: `votingsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `created_at`) VALUES
(1, 'admin', 'mayank111', 'Super Admin', 'admin@gmail.com', '2025-07-19 19:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `username`, `action`, `details`, `timestamp`) VALUES
(1, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-30 08:48:24'),
(2, 1, 'admin', 'CREATE_ELECTION', '{\"election_id\":1,\"name\":\"2025 Manager\",\"start_date\":\"2025-09-15T08:49\",\"end_date\":\"2025-10-31T08:49\",\"status\":\"active\"}', '2025-09-30 08:49:58'),
(74, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-09-24 18:34:46'),
(75, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-24 18:34:59'),
(76, 43, 'VTR2025FF1', 'VOTE_CAST', '{\"election_name\":\"2025 Student Council\",\"election_id\":2,\"position_id\":2}', '2025-09-24 18:36:21'),
(77, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-24 18:43:49'),
(78, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-24 18:45:48'),
(79, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-27 17:45:23'),
(80, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":75,\"candidate_name\":\"Rohit Patel\",\"election_id\":1,\"election_name\":\"2025 Manager\",\"position\":\"President\",\"has_photo\":true}', '2025-09-27 18:21:19'),
(81, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":76,\"candidate_name\":\"Arjun Reddy\",\"election_id\":1,\"election_name\":\"2025 Manager\",\"position\":\"Secretary\",\"has_photo\":true}', '2025-09-27 18:22:28'),
(82, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":77,\"candidate_name\":\"Anjali Singh\",\"election_id\":1,\"election_name\":\"2025 Manager\",\"position\":\"President\",\"has_photo\":true}', '2025-09-27 18:23:06'),
(83, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":78,\"candidate_name\":\"Raghav Mehta\",\"election_id\":7,\"election_name\":\"2025 CEO\",\"position\":\"President\",\"has_photo\":true}', '2025-09-27 18:25:03'),
(84, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":79,\"candidate_name\":\"Ananya Sharma\",\"election_id\":7,\"election_name\":\"2025 CEO\",\"position\":\"Mayor\",\"has_photo\":true}', '2025-09-27 18:25:59'),
(85, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":80,\"candidate_name\":\"Karan Malhotra\",\"election_id\":7,\"election_name\":\"2025 CEO\",\"position\":\"Secretary\",\"has_photo\":true}', '2025-09-27 18:26:53'),
(86, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":81,\"candidate_name\":\"Rohan Nair\",\"election_id\":2,\"election_name\":\"2025 Student Council\",\"position\":\"President\",\"has_photo\":true}', '2025-09-27 18:28:18'),
(87, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":82,\"candidate_name\":\"Meera Joshi\",\"election_id\":2,\"election_name\":\"2025 Student Council\",\"position\":\"Mayor\",\"has_photo\":true}', '2025-09-27 18:29:01'),
(88, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":83,\"candidate_name\":\"Vikram Bhatia\",\"election_id\":4,\"election_name\":\"2025 City Mayor Election\",\"position\":\"Mayor\",\"has_photo\":true}', '2025-09-27 20:01:19'),
(89, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":84,\"candidate_name\":\"Anjali Deshmukh\",\"election_id\":4,\"election_name\":\"2025 City Mayor Election\",\"position\":\"moniter\",\"has_photo\":true}', '2025-09-27 20:02:14'),
(90, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":85,\"candidate_name\":\"Rajesh Khanna\",\"election_id\":4,\"election_name\":\"2025 City Mayor Election\",\"position\":\"Secretary\",\"has_photo\":true}', '2025-09-27 20:02:48'),
(91, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":86,\"candidate_name\":\"Arjun Mehta\",\"election_id\":5,\"election_name\":\"2025 Prime Minister\",\"position\":\"President\",\"has_photo\":true}', '2025-09-27 20:05:48'),
(92, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":87,\"candidate_name\":\"Rohit Desai\",\"election_id\":5,\"election_name\":\"2025 Prime Minister\",\"position\":\"Secretary\",\"has_photo\":true}', '2025-09-27 20:06:33'),
(93, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":88,\"candidate_name\":\"Priya Rangan\",\"election_id\":5,\"election_name\":\"2025 Prime Minister\",\"position\":\"moniter\",\"has_photo\":true}', '2025-09-27 20:07:19'),
(94, 1, 'admin', 'ADD_CANDIDATE', '{\"candidate_id\":89,\"candidate_name\":\"Sneha Iyer\",\"election_id\":5,\"election_name\":\"2025 Prime Minister\",\"position\":\"moniter\",\"has_photo\":true}', '2025-09-27 20:08:15'),
(95, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-29 22:49:54'),
(96, 1, 'admin', 'DELETE_CANDIDATE', '{\"candidate_id\":77,\"candidate_name\":\"Anjali Singh\",\"election_id\":1,\"election_name\":\"2025 Manager\"}', '2025-09-29 22:50:07'),
(97, 44, 'VTR2025201', 'VOTE_CAST', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"position_id\":1}', '2025-09-29 22:54:36'),
(98, 38, 'VTR202583A', 'VOTE_CAST', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"position_id\":1}', '2025-09-29 23:05:32'),
(99, 38, 'VTR202583A', 'VOTE_CAST', '{\"election_name\":\"2025 CEO\",\"election_id\":7,\"position_id\":1}', '2025-09-29 23:05:38'),
(100, 38, 'VTR202583A', 'VOTE_CAST', '{\"election_name\":\"2025 Student Council\",\"election_id\":2,\"position_id\":3}', '2025-09-29 23:05:46'),
(101, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-29 23:30:12'),
(102, 18, '1326', 'VOTE_CAST', '{\"election_name\":\"2025 City Mayor Election\",\"election_id\":4,\"position_id\":3}', '2025-09-29 23:32:28'),
(103, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-29 23:32:42'),
(104, 1, 'VTR2025258', 'VOTE_CAST', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"position_id\":1}', '2025-09-30 08:57:42'),
(105, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-30 08:58:26'),
(106, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-09-30 09:34:08'),
(107, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"election_status\":\"active\"}', '2025-09-30 09:34:51'),
(108, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 City Mayor Election\",\"election_id\":4,\"election_status\":\"closed\"}', '2025-09-30 09:34:54'),
(109, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Prime Minister\",\"election_id\":5,\"election_status\":\"active\"}', '2025-09-30 09:34:59'),
(110, 45, 'VTR20251D9', 'VOTE_CAST', '{\"election_name\":\"2025 CEO\",\"election_id\":7,\"position_id\":3}', '2025-10-03 08:26:23'),
(111, 45, 'VTR20251D9', 'VOTE_CAST', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"position_id\":1}', '2025-10-03 08:26:32'),
(112, 45, 'VTR20251D9', 'VOTE_CAST', '{\"election_name\":\"2025 Student Council\",\"election_id\":2,\"position_id\":2}', '2025-10-03 08:26:48'),
(113, 45, 'VTR20251D9', 'VOTE_CAST', '{\"election_name\":\"2025 Prime Minister\",\"election_id\":5,\"position_id\":7}', '2025-10-03 08:26:57'),
(114, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-10-03 08:27:54'),
(115, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"election_status\":\"active\"}', '2025-10-03 08:28:20'),
(116, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"election_status\":\"active\"}', '2025-10-03 08:28:52'),
(117, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 CEO\",\"election_id\":7,\"election_status\":\"active\"}', '2025-10-03 08:28:57'),
(118, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Student Council\",\"election_id\":2,\"election_status\":\"active\"}', '2025-10-03 08:29:00'),
(119, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Prime Minister\",\"election_id\":5,\"election_status\":\"active\"}', '2025-10-03 08:29:03'),
(120, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"election_status\":\"closed\"}', '2025-10-03 08:30:08'),
(121, 1, 'admin', 'VIEW_RESULTS', '{\"election_name\":\"2025 Manager\",\"election_id\":1,\"election_status\":\"closed\"}', '2025-10-03 08:30:41'),
(122, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:31:14'),
(123, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:36:26'),
(124, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:38:06'),
(125, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:40:23'),
(126, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:40:24'),
(127, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:40:24'),
(128, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:41:35'),
(129, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:41:35'),
(130, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:41:36'),
(131, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:41:36'),
(132, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:41:44'),
(133, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:42:02'),
(134, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:43:04'),
(135, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:43:04'),
(136, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:44:22'),
(137, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:45:40'),
(138, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:45:40'),
(139, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:45:41'),
(140, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:45:41'),
(141, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:45:41'),
(142, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:46:06'),
(143, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:46:24'),
(144, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:46:25'),
(145, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:46:25'),
(146, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:47:36'),
(147, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:48:16'),
(148, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:48:32'),
(149, NULL, '1', 'VOTE_CAST', '{\"election_name\":null,\"election_id\":null,\"position_id\":null}', '2025-10-03 08:48:32'),
(150, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-10-03 08:48:44'),
(151, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:48:45'),
(152, NULL, '1', 'VOTE_CAST', '{\"election_name\":null,\"election_id\":null,\"position_id\":null}', '2025-10-03 08:48:45'),
(153, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-10-03 08:48:52'),
(154, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:49:29'),
(155, NULL, '1', 'VOTE_CAST', '{\"election_name\":null,\"election_id\":null,\"position_id\":null}', '2025-10-03 08:49:29'),
(156, 1, 'admin', 'ADMIN_LOGIN', '{\"status\":\"success\",\"method\":\"password\"}', '2025-10-03 08:49:41'),
(157, 1, 'admin', 'ADMIN_LOGOUT', '{\"status\":\"success\"}', '2025-10-03 08:49:52'),
(158, NULL, '1', 'VOTE_CAST', '{\"election_name\":null,\"election_id\":null,\"position_id\":null}', '2025-10-03 08:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position_id` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `update_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `position_id`, `photo`, `bio`, `created_at`, `update_at`, `election_id`) VALUES
(60, 'Alice Johnson', 1, 'candidate_68864e7b05d763.51926920.png', 'Passionate about student welfare and academic excellence.', '2025-07-27 10:36:19', '2025-07-27 10:36:19', 2),
(61, 'Bob Smith', 2, 'candidate_68864f31167bc9.28338632.jfif', 'Experienced organizer eager to improve campus life.', '2025-07-27 10:39:21', '2025-07-27 10:39:21', 2),
(62, 'Clara Lee', 3, 'candidate_68864f60e62f12.45001702.png', 'Dedicated to transparent communication and inclusiveness.', '2025-07-27 10:40:08', '2025-07-27 10:40:08', 2),
(63, 'Emilio Gonzalez', 3, 'candidate_68864fee6760a8.94653093.png', 'Former city manager with extensive administrative experience.', '2025-07-27 10:42:30', '2025-07-27 10:42:30', 1),
(64, 'Alex Morgan', 1, 'candidate_68865010376051.49320710.jfif', 'Experienced leader focused on urban development and efficiency.', '2025-07-27 10:43:04', '2025-07-27 10:43:04', 1),
(65, 'Narendra Modi', 1, 'candidate_688664120421c4.30970328.gif', 'Experienced leader with a focus on development and governance.', '2025-07-27 11:39:26', '2025-07-27 12:08:26', 4),
(67, 'Rahul Sharma', 2, 'candidate_68865dab1221a9.79799878.png', 'Youth representative focusing on innovation and technology.', '2025-07-27 11:41:07', '2025-07-27 11:41:07', 4),
(69, 'Rahul Gandhi', 1, 'candidate_68865e31cbd062.27811474.png', 'Youth representative focusing on innovation and technology.', '2025-07-27 11:43:21', '2025-07-27 11:43:21', 5),
(73, 'Vijay', 1, 'candidate_688b83d1f16220.31434761.gif', 'Give Me Vote!', '2025-07-27 11:48:48', '2025-07-31 09:25:13', 7),
(74, 'Yash', 7, 'candidate_68865fc5c70bb0.80100851.png', 'Hello! I am Yash', '2025-07-27 11:50:05', '2025-07-27 11:50:05', 7),
(75, 'Rohit Patel', 1, 'candidate_68d7ddc7a42d45.24626157.jpg', 'Progressive Students Forum', '2025-09-27 07:21:19', '2025-09-27 07:21:19', 1),
(76, 'Arjun Reddy', 3, 'candidate_68d7de0c930fa0.39326887.png', 'Believes in boosting employee welfare and collaboration.', '2025-09-27 07:22:28', '2025-09-27 07:22:28', 1),
(78, 'Raghav Mehta', 1, 'candidate_68d7dea7d23dc3.36612189.jpg', 'Committed to making the company a global leader in technology and sustainability.', '2025-09-27 07:25:03', '2025-09-27 07:25:03', 7),
(79, 'Ananya Sharma', 2, 'candidate_68d7dedf8fe261.48380326.png', 'Focused on innovation, employee welfare, and long-term business growth.', '2025-09-27 07:25:59', '2025-09-27 07:25:59', 7),
(80, 'Karan Malhotra', 3, 'candidate_68d7df15569ab3.86551083.jpg', 'Wants to bring fresh strategies to drive market expansion and digital transformation.', '2025-09-27 07:26:53', '2025-09-27 07:26:53', 7),
(81, 'Rohan Nair', 1, 'candidate_68d7df6a5a60f3.89452155.jpg', 'Aims to strengthen placement support and introduce mentorship initiatives.', '2025-09-27 07:28:18', '2025-09-27 07:28:18', 2),
(82, 'Meera Joshi', 2, 'candidate_68d7df950f4507.64347819.png', 'Believes in promoting sports, arts, and student mental health programs.', '2025-09-27 07:29:01', '2025-09-27 07:29:01', 2),
(83, 'Vikram Bhatia', 2, 'candidate_68d7f537ca7d37.02017038.jpg', 'Advocates for better roads, green parks, and digital city initiatives.', '2025-09-27 09:01:19', '2025-09-27 09:01:19', 4),
(84, 'Anjali Deshmukh', 7, 'candidate_68d7f56e870f73.07615350.png', 'Wants to enhance employment opportunities and transparent governance.', '2025-09-27 09:02:14', '2025-09-27 09:02:14', 4),
(85, 'Rajesh Khanna', 3, 'candidate_68d7f590ae69d5.32380776.jpg', 'Promises improved public transport, clean water, and modern healthcare facilities.', '2025-09-27 09:02:48', '2025-09-27 09:02:48', 4),
(86, 'Arjun Mehta', 1, 'candidate_68d7f644ac8784.72109870.jpg', 'Focused on economic growth, education reform, and digital infrastructure.', '2025-09-27 09:05:48', '2025-09-27 09:05:48', 5),
(87, 'Rohit Desai', 3, 'candidate_68d7f671b7b049.49372766.png', 'Aims to strengthen technology innovation, transparency, and foreign policy.', '2025-09-27 09:06:33', '2025-09-27 09:06:33', 5),
(88, 'Priya Rangan', 7, 'candidate_68d7f69f9ef944.42673399.jpg', 'Advocates for women empowerment, healthcare improvement, and sustainable development.', '2025-09-27 09:07:19', '2025-09-27 09:07:19', 5),
(89, 'Sneha Iyer', 7, 'candidate_68d7f6d7dd88f3.54968735.jpg', 'Believes in inclusive governance, youth participation, and rural development.', '2025-09-27 09:08:15', '2025-09-27 09:08:15', 5);

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('pending','active','closed') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `name`, `description`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, '2025 Manager', NULL, '2025-09-15 08:49:00', '2025-09-30 08:49:00', 'closed', '2025-09-30 08:49:58', '2025-10-03 08:30:04'),
(2, '2025 Student Council', 'Annual student council election.', '2025-09-01 17:00:00', '2025-10-30 17:00:00', 'active', '2025-07-04 08:20:25', '2025-09-24 20:33:46'),
(4, '2025 City Mayor Election', 'Election for the city mayor.', '2025-09-01 00:00:00', '2025-10-31 00:00:00', 'active', '2025-07-09 20:46:55', '2025-10-03 08:29:30'),
(5, '2025 Prime Minister', 'Election for the PM.', '2025-09-01 00:00:00', '2025-10-31 17:00:00', 'active', '2025-07-09 20:55:35', '2025-09-27 18:17:39'),
(7, '2025 CEO', NULL, '2025-09-01 23:44:00', '2025-10-31 12:00:00', 'active', '2025-07-20 23:45:07', '2025-09-27 17:46:32');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `discription` text DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `discription`, `create_at`, `updated_at`, `election_id`) VALUES
(1, 'President', 'Leads the executive branch and represents the nation.', '2025-07-03 07:33:56', '2025-07-24 12:28:47', 1),
(2, 'Mayor', 'Oversees the administration of the city.', '2025-07-03 07:33:56', '2025-07-24 12:28:47', 1),
(3, 'Secretary', 'Manages records and correspondence.', '2025-07-03 07:33:56', '2025-07-24 12:28:47', 1),
(7, 'moniter', 'Leads the class.', '2025-07-24 11:38:54', '2025-07-24 11:38:54', 2);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `voter_id` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `registered_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `name`, `voter_id`, `dob`, `email`, `password`, `has_voted`, `registered_at`, `profile_photo`) VALUES
(27, 'mayank', 'VTR2025A341AF', '2025-07-16', 'mayankpatadiya28@gmail.com', '$2y$10$/izspX1iEzzlc4LX5lSJmOvRD3.VeRl0TAqehR26r8ByLRFUt/uuC', 0, '2025-07-29 02:02:19', NULL),
(28, 'Jack yoo', 'VTR202526CC', '2007-02-28', 'Jack28@gmail.com', '$2y$10$Q.SU2/aWEKcrqngKhgcl.ethcEVOfrjwFwt1juv0xUkOB/VmzN8/O', 0, '2025-07-29 02:04:22', NULL),
(29, 'Yug Solanki', 'VTR2025268', '2006-07-09', '1223@gmail.com', '$2y$10$UZkvA5BRUZDk.2J1saMBCuoUfZ7kjKT8Sfuvf.FYBodFyza5IK0qO', 0, '2025-07-29 02:06:28', NULL),
(30, 'Tomy', 'VTR2025CE0', '2005-08-25', 'Tomy@gmail.com', '$2y$10$BwW3CRBPBDwYrrfaMKemTuakCiNtSkw5c9Qc.aKEFHeOiwLGdU.YC', 0, '2025-07-29 02:08:03', NULL),
(33, 'mayank', 'VTR2025EFD', '2005-10-28', 'mayankpatadiya108@gmail.com', '$2y$10$pgdB4gUz1sWj9hRRi3wZ7e0JNlW.VMV6zlzC8E/gptA.m6A17IfUu', 0, '2025-07-29 06:11:26', NULL),
(34, 'Priyank1', 'VTR2025CC6', '2006-02-14', 'priyank@gmail.com', '$2y$10$yzG5xZ277I0qMXkBZ4OOIuya.XRk.Ya.jQlwkoL7hpy8bXO8TUoUG', 0, '2025-07-29 06:17:10', NULL),
(36, 'daino', 'VTR2025381', '2006-05-04', 'daino12@gmail.com', '$2y$10$4bPbDi3EJCt3.7CpT4dyfu8w7gEX/PuaRQD7aOi8x3AQ0KJFhM3zu', 0, '2025-07-29 06:42:20', NULL),
(38, 'rajesh', 'VTR202583A', '2005-10-28', 'rajesh@gmail.com', '$2y$10$2f5u4G/dvBftoDImSjjpaeTUWayNb8k.pewZKadmGaCrxKsOQrahi', 0, '2025-08-03 12:09:42', 'uploads/profile_photos/profile_VTR202583A_1754242990.gif'),
(39, 'Vikram', 'VTR202586E', '2004-08-06', 'vikram@gmail.com', '$2y$10$w2c94OPHkEWrVr/BhCd2Wea5XmcjKm/eiB6nTuTKRuBQJMEvjbKv6', 0, '2025-08-06 07:55:14', NULL),
(40, 'tushar', 'VTR202538B', '2006-10-06', 'rushar@gmail.com', '$2y$10$goRWY1t/GEQIQxOBRqTkHuAjBRbrwUCDBlp2sDXamAEgXP.0HbI.2', 0, '2025-08-06 08:17:36', NULL),
(41, 'virat', 'VTR20259B7', '2006-02-08', 'virat@gmail.com', '$2y$10$KUqZ5YB79tdocR4BthzuY.akZnFOt2WxGUOqjKwHq.Z3c6eBN91/C', 0, '2025-08-06 08:24:01', NULL),
(42, 'Sarukh Khan', 'VTR2025A2D', '2025-09-03', 'khan@gmail.com', '$2y$10$v.ifmk0QZXtoqmeSKph/NO.g3upqLtd.rc7ixZ/KMI0LujTYqSjfe', 0, '2025-09-11 08:58:05', NULL),
(43, 'yuvraj', 'VTR2025FF1', '2006-08-22', 'yuvraj@gmail.com', '$2y$10$hmBpvUrGSyQIrw7QwnhdZOLU9Vra1IbY5TafiGN6RsQp1NsS/SVZq', 0, '2025-09-24 07:09:46', 'uploads/profile_photos/profile_VTR2025FF1_1758726452.gif'),
(44, 'update', 'VTR2025201', '2006-10-29', 'update@gmail.com', '$2y$10$s1qUA.ExhOZEYh3m6Q9MnODi5mBXXA11J5ckE0eHZktPPwb1Vrv.K', 0, '2025-09-27 06:44:21', 'uploads/profile_photos/profile_VTR2025201_1759165754.gif'),
(45, 'Ram', 'VTR20251D9', '2025-09-26', 'ram123@gmail.com', '$2y$10$1RRN/McmufjN712qejGbcu2AMsO6XiLuSbeedhm2leuBz6/v5iGbW', 0, '2025-10-03 02:36:42', NULL),
(46, 'pratik', 'VTR2025CD2', '2025-10-25', 'ram3@gmail.com', '$2y$10$BGWOhtQCXRCnEXjIk7GlYObRJl/pZqxYzNsw01hLuILwxcdgghEKa', 0, '2025-10-03 02:51:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `voted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `voter_id`, `election_id`, `candidate_id`, `position_id`, `voted_at`) VALUES
(168, 38, 5, 69, 1, '2025-08-03 23:12:22'),
(169, 42, 1, 64, 1, '2025-09-11 20:18:52'),
(170, 43, 2, 61, 2, '2025-09-24 18:36:21'),
(171, 44, 1, 64, 1, '2025-09-29 22:54:36'),
(172, 38, 1, 64, 1, '2025-09-29 23:05:31'),
(173, 38, 7, 73, 1, '2025-09-29 23:05:38'),
(174, 38, 2, 62, 3, '2025-09-29 23:05:46'),
(177, 45, 7, 80, 3, '2025-10-03 08:26:23'),
(178, 45, 1, 75, 1, '2025-10-03 08:26:32'),
(179, 45, 2, 61, 2, '2025-10-03 08:26:48'),
(180, 45, 5, 89, 7, '2025-10-03 08:26:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_candidates_position` (`position_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_id` (`voter_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `position_id` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
