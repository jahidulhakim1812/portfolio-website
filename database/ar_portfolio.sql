-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 12:02 PM
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
-- Database: `ar_portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2026-05-23 21:20:23');

-- --------------------------------------------------------

--
-- Table structure for table `chairman_speech`
--

CREATE TABLE `chairman_speech` (
  `id` int(11) NOT NULL,
  `speech_text` text NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `chairman_name` varchar(255) DEFAULT 'Chairman',
  `title` varchar(255) DEFAULT NULL,
  `signature_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chairman_speech`
--

INSERT INTO `chairman_speech` (`id`, `speech_text`, `image_url`, `chairman_name`, `title`, `signature_url`, `is_active`) VALUES
(1, 'Welcome to AR Tech Solutions. We are dedicated to pushing the boundaries of augmented reality and immersive technologies. Our mission is to empower businesses and individuals with cutting-edge solutions that transform how they interact with the digital world. Together, we are building the future of reality.', 'https://randomuser.me/api/portraits/men/32.jpg', 'John Carter', 'Chairman & Founder', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`, `is_read`) VALUES
(1, 'Md. Jahidul Hakim', 'mdjhk19@gmail.com', '01837090666', 'fghdgfh', '2026-05-26 09:35:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-cube',
  `link_url` varchar(255) DEFAULT '#',
  `image_url` varchar(500) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `total_classes` int(11) DEFAULT 0,
  `total_projects` int(11) DEFAULT 0,
  `instructor_name` varchar(255) DEFAULT NULL,
  `instructor_bio` text DEFAULT NULL,
  `instructor_image` varchar(500) DEFAULT NULL,
  `curriculum` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`curriculum`)),
  `career_outcomes` text DEFAULT NULL,
  `prerequisites` text DEFAULT NULL,
  `software_learned` text DEFAULT NULL,
  `is_popular` tinyint(4) DEFAULT 0,
  `level` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_offline` decimal(10,2) DEFAULT 0.00,
  `enrolled_students` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `order_position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `short_description`, `icon_class`, `link_url`, `image_url`, `duration`, `total_classes`, `total_projects`, `instructor_name`, `instructor_bio`, `instructor_image`, `curriculum`, `career_outcomes`, `prerequisites`, `software_learned`, `is_popular`, `level`, `price`, `price_offline`, `enrolled_students`, `status`, `order_position`, `created_at`) VALUES
(6, '3D Content Creation', 'Master Blender, Maya, and real-time 3D assets for immersive applications.', NULL, 'fas fa-paint-brush', 'courses.php?id=6', NULL, '8 Weeks', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Intermediate', 349.00, 0.00, 24, 1, 6, '2026-05-24 09:27:56'),
(7, 'ifjfjsjfjf', 'hdfhdhdfhdfhdfhdfhfdhdfhdfhfdhdfhfdhfdhfdhfdhdrhdhdhdhdhdhdhdrhdhdhfdhfdhdfhdrhdhdhdhdhdhdhdhdhdhfhdfhdfhfhf', NULL, 'fas fa-cube', '#', 'uploads/courses/1779615180_6a12c5cc03e0b.png', '6 ,m', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Advanced', 5000.00, 0.00, 2, 1, 0, '2026-05-24 09:33:01');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `order_position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_name`, `logo_url`, `country`, `district`, `order_position`, `is_active`, `created_at`) VALUES
(1, 'gsdgsdgs', 'uploads/logos/1779743700_6a14bbd44080b.jpg', 'USA', '', 0, 1, '2026-05-25 21:15:01'),
(2, 'efwef', '', 'United States', 'florida', 0, 1, '2026-05-25 21:20:37'),
(3, 'dfs', '', 'Bangladesh', 'Bagerhat', 0, 1, '2026-05-25 21:21:00'),
(4, 'Md. Jahidul Hakim', '', 'Bangladesh', 'Rajshahi', 1, 1, '2026-05-25 21:46:08'),
(5, 'JAHID KHAN', '', 'Bangladesh', 'Bagerhat', 5, 1, '2026-05-25 21:46:37');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_email` varchar(255) NOT NULL,
  `student_phone` varchar(50) NOT NULL,
  `student_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'sslcommerz',
  `payment_status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `course_id`, `student_name`, `student_email`, `student_phone`, `student_address`, `payment_method`, `payment_status`, `transaction_id`, `amount`, `enrollment_date`) VALUES
(1, 7, 'JAHID KHAN', 'mdjhk300@gmail.com', '01957288638', 'Dhaka,Gazipur,Boardbazar,National university,\r\nsouth khailkur,38no woard,sohid siddik road, holding no:446', 'demo', 'completed', NULL, 5000.00, '2026-05-24 17:52:40'),
(2, 7, 'JAHID KHAN', 'mdjhk300@gmail.com', '01957288638', 'Dhaka,Gazipur,Boardbazar,National university,\r\nsouth khailkur,38no woard,sohid siddik road, holding no:446', 'demo', 'completed', NULL, 5000.00, '2026-05-26 08:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `portfolios`
--

CREATE TABLE `portfolios` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `order_position` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolios`
--

INSERT INTO `portfolios` (`id`, `title`, `client`, `description`, `image_url`, `featured`, `order_position`, `status`, `created_at`) VALUES
(1, 'Virtual Showroom for AutoWorld', 'AutoWorld Motors', 'Developed an interactive VR showroom allowing customers to explore car models in 360°.', 'https://picsum.photos/id/111/800/600', 1, 0, 1, '2026-05-23 20:50:52'),
(2, 'AR Maintenance Guide for Heavy Machinery', 'IndustrialTech Ltd.', 'Mobile AR app that overlays repair steps on physical equipment, reducing downtime by 40%.', 'https://picsum.photos/id/48/800/600', 1, 0, 1, '2026-05-23 20:50:52'),
(3, 'Interactive Museum Experience', 'National Heritage Trust', 'Created AR triggers that bring historical artifacts to life on visitors\' phones.', 'https://picsum.photos/id/96/800/600', 0, 0, 1, '2026-05-23 20:50:52'),
(4, 'wsqed', 'wqed', 'fasfa', 'uploads/portfolios/1779741672_6a14b3e81f910.png', 1, 0, 1, '2026-05-25 20:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `icon_class` varchar(100) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link_url` varchar(255) DEFAULT '#',
  `order_position` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `icon_class`, `image_url`, `title`, `description`, `link_url`, `order_position`, `status`) VALUES
(3, 'fas fa-chart-line', NULL, 'Spatial Analytics', 'Data visualization and spatial computing for smart environments.', 'services.php#analytics', 0, 1),
(4, 'fas fa-paint-brush', NULL, '3D Content Creation', 'High-quality 3D modeling and animation for immersive experiences.', 'services.php#3d', 0, 1),
(5, 'fas fa-cube', 'uploads/services/1779739839_6a14acbf7633c.jpg', 'fhbdfhdfhd', 'hgdhdfhdhdh', '#', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `order_position` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `subtitle`, `image_url`, `button_text`, `button_link`, `status`, `order_position`) VALUES
(1, 'Immersive Augmented Reality', 'Transform how your business interacts with the digital world.', 'https://picsum.photos/id/13/1920/1080', 'Explore Solutions', 'services.php', 1, 1),
(2, 'Next-Gen VR Experiences', 'Step into a new dimension with our virtual reality solutions.', 'https://picsum.photos/id/26/1920/1080', 'View Portfolio', 'portfolio.php', 1, 2),
(3, 'AR for Enterprise', 'Boost efficiency and engagement with custom AR applications.', 'https://picsum.photos/id/42/1920/1080', 'Contact Us', 'contact.php', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_linkedin` varchar(255) DEFAULT NULL,
  `order_position` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `bio`, `image_url`, `social_facebook`, `social_twitter`, `social_linkedin`, `order_position`, `status`, `is_active`) VALUES
(2, 'Dr. Sarah Chen', 'Head of AR Research', 'PhD in Computer Vision, leading our innovation lab.', 'https://randomuser.me/api/portraits/women/68.jpg', '#', '#', '#', 2, 1, 1),
(3, 'Michael Rodriguez', 'Lead VR Engineer', 'Expert in Unity and Unreal Engine for enterprise VR.', 'https://randomuser.me/api/portraits/men/45.jpg', '#', '#', '#', 3, 1, 1),
(4, 'Emma Watson', 'Creative Director', 'Award-winning 3D designer and spatial storyteller.', 'https://randomuser.me/api/portraits/women/89.jpg', '#', '#', '#', 4, 1, 1),
(5, 'John Carter', 'CEO & Founder', 'Visionary leader with 20+ years in immersive tech.', 'https://randomuser.me/api/portraits/men/32.jpg', '#', '#', '#', 1, 1, 1),
(6, 'Dr. Sarah Chen', 'Head of AR Research', 'PhD in Computer Vision, leading our innovation lab.', 'https://randomuser.me/api/portraits/women/68.jpg', '#', '#', '#', 2, 1, 1),
(7, 'Michael Rodriguez', 'Lead VR Engineer', 'Expert in Unity and Unreal Engine for enterprise VR.', 'https://randomuser.me/api/portraits/men/45.jpg', '#', '#', '#', 3, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `client_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `testimonial_text` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `rating` int(1) DEFAULT 5,
  `order_position` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `email`, `provider_id`, `client_title`, `company`, `testimonial_text`, `image_url`, `rating`, `order_position`, `status`, `is_verified`) VALUES
(1, 'Sarah Johnson', NULL, NULL, 'CTO', 'AutoWorld Motors', 'The VR showroom increased our customer engagement by over 200%. The AR Tech team delivered beyond expectations.', NULL, 5, 0, 1, 0),
(2, 'Mohammed Rahman', NULL, NULL, 'Operations Director', 'IndustrialTech Ltd.', 'Their AR maintenance solution saved us countless hours in training and reduced errors significantly. Highly recommended.', NULL, 5, 0, 1, 0),
(3, 'Elena Martinez', NULL, NULL, 'Museum Curator', 'National Heritage Trust', 'Visitors love the interactive experience. Professional, creative, and technically flawless execution.', NULL, 5, 0, 1, 0),
(4, 'thfgh', 'mdjhk19@gmail.com', NULL, NULL, NULL, 'ghfghfhfhfthfthfghfhfh', '', 5, 0, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `chairman_speech`
--
ALTER TABLE `chairman_speech`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `portfolios`
--
ALTER TABLE `portfolios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chairman_speech`
--
ALTER TABLE `chairman_speech`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
