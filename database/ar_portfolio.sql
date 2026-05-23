-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 09:30 PM
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
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolios`
--

CREATE TABLE `portfolios` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `client` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `featured` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolios`
--

INSERT INTO `portfolios` (`id`, `title`, `client`, `category`, `image_url`, `description`, `featured`) VALUES
(1, 'Enterprise AR Maintenance System', 'Industrial Corp', 'Manufacturing', 'https://picsum.photos/id/20/600/400', 'Remote assistance and AR workflow guides that increased efficiency by 40%.', 1),
(2, 'Surgical Guidance Interface', 'MedTech Innovation', 'Healthcare', 'https://picsum.photos/id/107/600/400', 'AR overlay for surgeons reducing operation planning time.', 1),
(3, 'Cultural Heritage AR Experience', 'National Museum', 'Heritage', 'https://picsum.photos/id/96/600/400', 'Interactive exhibits with 3D reconstructions of artifacts.', 1),
(4, 'AR Training Simulator', 'Global Aviation', 'Training', 'https://picsum.photos/id/125/600/400', 'Immersive pilot training modules reducing costs by 30%.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `icon_class` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `link_text` varchar(50) DEFAULT 'Learn More',
  `link_url` varchar(200) DEFAULT '#'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `icon_class`, `title`, `description`, `link_text`, `link_url`) VALUES
(1, 'fas fa-cube', 'Augmented Reality Platforms', 'Custom AR development for enterprise and consumer applications with real-time tracking.', 'Learn More', 'portfolio.php'),
(2, 'fas fa-chart-line', 'Spatial Data Analytics', 'Advanced analytics for 3D user interactions and spatial mapping insights.', 'Learn More', 'contact.php'),
(3, 'fas fa-cloud-upload-alt', 'AR Cloud Infrastructure', 'Scalable cloud-based services for persistent, shared AR experiences.', 'Learn More', 'contact.php'),
(4, 'fas fa-landmark', 'Cultural Heritage Experiences', 'Preserving history through interactive AR, tailored for museums and tourism.', 'Learn More', 'portfolio.php');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(300) DEFAULT NULL,
  `order_position` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `subtitle`, `image_url`, `button_text`, `button_link`, `order_position`, `status`) VALUES
(1, 'Augmenting Your Reality', 'Precision AR solutions for enterprise & creativity', 'https://picsum.photos/id/13/1920/1080', 'Discover Services', '#services', 1, 1),
(2, 'Spatial Intelligence Redefined', 'Real-time 3D mapping & analytics dashboard', 'https://picsum.photos/id/26/1920/1080', 'Explore Platform', 'portfolio.php', 2, 1),
(3, 'Cultural Heritage AR', 'Immersive museum experiences powered by AR Cloud', 'https://picsum.photos/id/104/1920/1080', 'See Projects', 'portfolio.php', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_title` varchar(100) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `testimonial_text` text NOT NULL,
  `rating` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_title`, `company`, `testimonial_text`, `rating`) VALUES
(1, 'Sarah Chen', 'CEO', 'AppVision', 'Uncompromising work environment, AR Tech Solutions creates high energy atmosphere. The precision is unmatched. Highly recommend their team!', 5),
(2, 'John Smith', 'Director of Innovation', 'MedTech Innovation', 'I have been using this platform for over two years and it has made all the difference in surgical training. I highly recommend it!', 5),
(3, 'Emily Rodriguez', 'CTO', 'Heritage Interactive', 'The AR Cloud solution transformed our museum experience. Visitor engagement increased by 200%. Exceptional support.', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
