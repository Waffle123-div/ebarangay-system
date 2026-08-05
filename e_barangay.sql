-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2026 at 01:42 PM
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
-- Database: `e_barangay`
--

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `id` int(11) NOT NULL,
  `tracking_number` varchar(30) NOT NULL,
  `resident_name` varchar(100) NOT NULL,
  `resident_email` varchar(100) DEFAULT NULL,
  `document_type` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `is_generated` tinyint(1) NOT NULL DEFAULT 0,
  `priority` varchar(20) DEFAULT 'Medium',
  `assigned_staff` varchar(100) DEFAULT 'Unassigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `age` int(11) DEFAULT NULL,
  `purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_requests`
--

INSERT INTO `document_requests` (`id`, `tracking_number`, `resident_name`, `resident_email`, `document_type`, `status`, `is_generated`, `priority`, `assigned_staff`, `created_at`, `updated_at`, `age`, `purpose`) VALUES
(11, 'REQ-2026-40632', 'adriangwapo', 'adrian@gmail.com', 'Business Permit', 'Under Review', 0, 'Urgent', 'Unassigned', '2026-04-26 05:54:04', '2026-06-25 05:09:55', 21, 'wehooooo'),
(12, 'REQ-2026-32367', 'bobords', 'adrian@gmail.com', 'Barangay Clearance', 'Under Review', 0, NULL, 'Unassigned', '2026-04-26 08:09:55', '2026-06-25 04:28:37', 21, 'wa ra bords ay'),
(27, 'REQ-2026-00789', 'Adrian Gabriel P. Sios-e', 'sioseadrian12@gmail.com', 'Certificate of Indigency', 'Completed', 1, 'Normal', 'Unassigned', '2026-06-29 01:45:05', '2026-06-29 01:48:14', 22, 'For Educational Assistance'),
(28, 'REQ-2026-06011', 'axdasd', 'sioseadrian12@gmail.com', 'Barangay Clearance', 'Pending', 0, 'Normal', 'Unassigned', '2026-06-29 01:50:10', '2026-06-29 01:50:10', 22, 'weho'),
(29, 'REQ-2026-72369', 'Adrian', 'sioseadrian12@gmail.com', 'Certificate of Indigency', 'Pending', 0, 'Urgent', 'Unassigned', '2026-07-01 03:48:13', '2026-07-01 03:48:13', 22, 'aray mo pakak');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_06_10_054825_add_email_verified_at_to_users_table', 1),
(2, '2026_06_11_065415_add_google_id_to_users_table', 2),
(3, '2026_06_12_060414_add_missing_registration_fields_to_users_table', 3),
(4, '2026_06_13_162017_add_id_type_to_users_table', 4),
(5, '2026_06_15_055020_create_password_reset_tokens_table', 5),
(6, '2026_06_17_034606_add_phone_number_to_users_table', 6),
(7, '2026_06_21_084503_add_pin_to_users_table', 7),
(8, '2026_06_25_153654_add_is_generated_to_document_requests_table', 8),
(9, '2026_06_29_083939_create_notifications_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('30d97526-eaf7-4c5d-9ced-ec8d074a20f9', 'App\\Notifications\\DocumentReadyNotification', 'App\\Models\\User', 95, '{\"tracking_number\":\"REQ-2026-00789\",\"document_type\":\"Certificate of Indigency\",\"message\":\"Your Certificate of Indigency is ready for pick-up!\"}', '2026-06-29 01:47:20', '2026-06-29 01:46:57', '2026-06-29 01:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('09511360631', '42732', '2026-06-17 06:11:43'),
('45645655656555', '16013', '2026-07-02 16:45:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `security_pin` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'Resident',
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `birthdate` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) DEFAULT NULL,
  `proof_of_residency` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `name`, `email`, `phone_number`, `google_id`, `email_verified_at`, `contact_number`, `password`, `security_pin`, `role`, `status`, `created_at`, `updated_at`, `birthdate`, `gender`, `id_type`, `proof_of_residency`) VALUES
(7, '', 'Adrian Sios-e', 'adrianadmin@gmail.com', NULL, NULL, '2026-06-12 09:55:00', NULL, '$2y$12$k//KMNTfbAoX7lCyaTvv6.Fb2SHyI/9ivCEWpUPGTduTybLn12Zyu', NULL, 'Admin', 'Active', '2026-04-17 04:55:39', '2026-06-12 09:55:46', NULL, NULL, NULL, NULL),
(101, 'USR-34475', 'Adrian Sios-e', 'sioseadrian12@gmail.com', NULL, '101588627257290576389', '2026-07-06 23:22:13', NULL, '$2y$12$oPFp07lBAtE7R2Q249/Lyu/qixeVAoicud/7QqW45TbPZg6Q8ykPS', '$2y$12$Xfs3DNjsE.2I0m9dUDxSz.El6dAC.bww.tc/X0yWDwyl5BT14JNWS', 'Resident', 'active', '2026-07-06 23:21:29', '2026-07-06 23:25:05', '2026-07-09', 'Man', 'National ID', 'residency_proofs/s6LguqMlOqh5PR5hiR8evtmiiqyhvRvEkEupCkLX.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `users_phone_number_unique` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
