-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 11:54 PM
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
(1, 'Welcome to AR Tech Solutions. We are dedicated to pushing the boundaries of augmented reality and immersive technologies. Our mission is to empower businesses and individuals with cutting-edge solutions that transform how they interact with the digital world. Together, we are building the future of reality.', 'uploads/chairman/1779816384_6a15d7c0d6422.png', 'MD.JAHIDUL HAKIM', 'Chairman & Founder', '', 1);

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
(1, 'Md. Jahidul Hakim', 'mdjhk19@gmail.com', '01837090666', 'fghdgfh', '2026-05-26 09:35:53', 1);

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
(6, 'Digital Marketing', 'A comprehensive Graphic Design Course is an immersive program that transforms creative instincts into strategic visual communication skills. Rather than just teaching technical software operations, this course trains you to think like a professional visual problem solver. It explores the psychological principles of how humans perceive visual information, teaching you how to intentionally manipulate typography, color theory, grid systems, and negative space to influence viewer behavior and convey complex concepts effortlessly.\r\n\r\nThe educational journey is built on three core pillars: theory, technical tools, and real-world execution. Students first study design fundamentals before moving to the computer to master industry-standard tools like Adobe Illustrator for scalable vector artwork, Photoshop for raster imaging and digital manipulation, and InDesign for complex layout typography. Modern courses also increasingly blend in user interface and user experience (UI/UX) principles using tools like Figma to prepare designers for the digital-first landscapes of mobile apps and web platforms.\r\n\r\nUltimately, the entire curriculum is geared toward project-based learning. You will move from standalone exercises to engineering complete corporate brand identity systems, marketing campaigns, motion graphics, and product packaging design. The culmination of this course is the development of a professional, curated portfolio—a visual resume showcasing your conceptual thinking and technical execution, which serves as the primary credential needed to secure careers in creative agencies, corporate marketing departments, or as an independent freelance designer.', 'A Graphic Design course is a dynamic program that blends artistic creativity with digital technology to teach you the art of visual communication. In this course, you will master the fundamental principles of design—such as typography, color theory, and layout composition—while gaining hands-on experience with industry-standard software like Adobe Photoshop, Illustrator, and InDesign. Beyond just learning the tools, you will discover how to solve conceptual problems and tell compelling stories through visuals. By working on real-world projects like branding, marketing campaigns, packaging, and digital media, you will build a professional portfolio that prepares you for an exciting career in advertising, web design, publishing, or freelance creative work.', 'fas fa-paint-brush', 'courses.php?id=6', 'uploads/courses/1779917512_6a1762c8c2c58.png', '3 Month', 40, 5, 'MD.Jahidul Hakim', 'Lead Instructor (Visual Communication) & Full-Stack Developer\r\n\r\nMd. Jahidul Hakim is an experienced software engineer, full-stack web developer, and visual designer who operates at the intersection of technology and creative design. With an extensive background in both writing robust code and crafting intuitive user interfaces (UI/UX), he possesses a unique understanding of how to make a design not just visually stunning, but highly functional and user-centric.\r\n\r\nAs an educator, Jahidul firmly believes in project-based learning. He goes beyond teaching the standard mechanics of Adobe Photoshop, Illustrator, or Figma; his curriculum focuses on the underlying why of design—including practical color theory, strategic typography, and brand identity systems. Drawing from his real-world experience building e-commerce platforms and digital branding assets, he guides students to think like creative problem solvers, ensuring they graduate with a high-impact, client-ready portfolio.\r\n\r\nTeaching Philosophy:\r\n\r\n\"Design is not just about combining colors and shapes; it is about creating a visual solution to a real-world problem. Anyone can learn to use a software tool, but a true designer masters the thinking process behind the visual.\"\r\n\r\nCore Areas of Expertise\r\nBrand Identity & Logo Design: Crafting complete visual systems and guidelines for modern businesses and startups.\r\n\r\nUI/UX Layouts: Designing intuitive, responsive, and modern user interfaces for web and mobile applications.\r\n\r\nDigital Marketing Assets: Creating conversion-focused social media graphics, professional product mockups, and advertisements.\r\n\r\nIndustry Tools Mastery: Hands-on training in Adobe Illustrator, Adobe Photoshop, and Figma.', 'uploads/instructors/1779917671_6a1763678cb72.jpg', '{\r\n  \"modules\": [\r\n    {\r\n      \"title\": \"Fundamentals of Graphic Design\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 1,\r\n          \"topic\": \"Introduction to Graphic Design – History & Careers\",\r\n          \"type\": \"Video\",\r\n          \"resource\": \"https://youtu.be/design-history\"\r\n        },\r\n        {\r\n          \"class_number\": 2,\r\n          \"topic\": \"Design Elements: Line, Shape, Texture, Space\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Slides: elements.pdf\"\r\n        },\r\n        {\r\n          \"class_number\": 3,\r\n          \"topic\": \"Design Principles: Balance, Contrast, Hierarchy, Rhythm\",\r\n          \"type\": \"Interactive\",\r\n          \"resource\": \"Online exercise – identify principles\"\r\n        },\r\n        {\r\n          \"class_number\": 4,\r\n          \"topic\": \"Color Theory – RGB, CMYK, Harmonies, Psychology\",\r\n          \"type\": \"Video + Quiz\",\r\n          \"resource\": \"Color wheel tool\"\r\n        }\r\n      ],\r\n      \"projects\": \"Create a mood board using Canva or Pinterest\"\r\n    },\r\n    {\r\n      \"title\": \"Adobe Photoshop Mastery\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 5,\r\n          \"topic\": \"Workspace, Layers, Selections & Masks\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Project files: basic_editing.zip\"\r\n        },\r\n        {\r\n          \"class_number\": 6,\r\n          \"topic\": \"Retouching, Healing Brush, Clone Stamp\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Before/after images\"\r\n        },\r\n        {\r\n          \"class_number\": 7,\r\n          \"topic\": \"Working with Text, Layer Styles, Filters\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Typography effects PSD\"\r\n        },\r\n        {\r\n          \"class_number\": 8,\r\n          \"topic\": \"Photo Manipulation & Compositing\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Stock images pack\"\r\n        }\r\n      ],\r\n      \"projects\": \"Design a movie poster or album cover\"\r\n    },\r\n    {\r\n      \"title\": \"Adobe Illustrator for Vector Graphics\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 9,\r\n          \"topic\": \"Vector vs Raster, Artboards, Shapes & Paths\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Practice file: basic_shapes.ai\"\r\n        },\r\n        {\r\n          \"class_number\": 10,\r\n          \"topic\": \"Pen Tool Mastery, Anchor Points, Curves\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Pen tool game link\"\r\n        },\r\n        {\r\n          \"class_number\": 11,\r\n          \"topic\": \"Typography in Illustrator, Type on a Path\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Font pairing guide\"\r\n        },\r\n        {\r\n          \"class_number\": 12,\r\n          \"topic\": \"Gradients, Patterns, Brushes & Symbols\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Pattern library download\"\r\n        }\r\n      ],\r\n      \"projects\": \"Create a custom logo and business card\"\r\n    },\r\n    {\r\n      \"title\": \"Typography & Layout Design\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 13,\r\n          \"topic\": \"Anatomy of Type, Font Categories, Pairing\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Typography cheat sheet\"\r\n        },\r\n        {\r\n          \"class_number\": 14,\r\n          \"topic\": \"Hierarchy, Alignment, Grid Systems\",\r\n          \"type\": \"Interactive\",\r\n          \"resource\": \"Grid generator tool\"\r\n        },\r\n        {\r\n          \"class_number\": 15,\r\n          \"topic\": \"Editorial Design – Brochures, Magazines\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"InDesign template pack\"\r\n        }\r\n      ],\r\n      \"projects\": \"Design a 4‑page newsletter or brochure\"\r\n    },\r\n    {\r\n      \"title\": \"Branding & Identity Design\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 16,\r\n          \"topic\": \"Brand Strategy, Mood Boards, Mind Mapping\",\r\n          \"type\": \"Case Study\",\r\n          \"resource\": \"Famous rebrand examples\"\r\n        },\r\n        {\r\n          \"class_number\": 17,\r\n          \"topic\": \"Logo Design Process – Sketching to Vector\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Logo design worksheet\"\r\n        },\r\n        {\r\n          \"class_number\": 18,\r\n          \"topic\": \"Color Palette, Typography, Brand Guidelines\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Brand guideline template (PDF)\"\r\n        }\r\n      ],\r\n      \"projects\": \"Develop a complete brand identity for a startup\"\r\n    },\r\n    {\r\n      \"title\": \"Portfolio & Career Preparation\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 19,\r\n          \"topic\": \"Building a Design Portfolio – Do\'s & Don\'ts\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Portfolio checklist\"\r\n        },\r\n        {\r\n          \"class_number\": 20,\r\n          \"topic\": \"Freelancing 101: Contracts, Pricing, Clients\",\r\n          \"type\": \"Video\",\r\n          \"resource\": \"Freelance contract template\"\r\n        },\r\n        {\r\n          \"class_number\": 21,\r\n          \"topic\": \"Job Interview Tips & Design Tests\",\r\n          \"type\": \"Mock Interview\",\r\n          \"resource\": \"Common design interview questions\"\r\n        }\r\n      ],\r\n      \"projects\": \"Publish your portfolio on Behance or personal website\"\r\n    }\r\n  ]\r\n}', '', '', '', 1, 'Advanced', 10000.00, 15000.00, 2, 1, 6, '2026-05-24 09:27:56'),
(7, 'Professional Graphic Design', 'Unlock your creativity and build a successful career in the digital design industry with our Professional Graphic Design Course. This comprehensive course is specially designed for beginners, students, freelancers, entrepreneurs, and creative enthusiasts who want to master modern graphic design skills from basic to advanced level.\r\n\r\nIn today’s digital world, graphic design plays a powerful role in branding, marketing, advertising, social media, and business communication. This course provides practical, industry-focused training that helps students develop professional design skills using the latest creative software and modern design techniques.\r\n\r\nThroughout the course, students will learn how to create eye-catching social media posts, logos, banners, flyers, brochures, posters, business cards, packaging designs, and professional branding materials. The training focuses on both creativity and technical skills to ensure students can confidently work on real-world projects and client requirements.\r\n\r\nThe course includes hands-on training with industry-standard software such as Adobe Photoshop, Adobe Illustrator, Adobe InDesign, Figma, and Canva. Students will gain practical experience through live projects, assignments, and portfolio development sessions that prepare them for freelancing and professional job opportunities.\r\n\r\nThis program also covers branding principles, typography, color theory, layout design, photo editing, vector illustration, print design, and UI design basics. Students will learn how to think creatively, communicate visually, and create professional-quality designs for digital and print media.\r\n\r\nOne of the most valuable parts of this course is the freelancing and career guideline section. Students will receive complete guidance on how to create professional portfolios, set up accounts on Fiverr and Upwork, communicate with clients, create service gigs, and start earning from graphic design skills online.\r\n\r\nBy the end of the course, students will be able to:\r\n\r\nDesign professional marketing and branding materials\r\nCreate modern social media and advertising content\r\nBuild complete brand identities and logo systems\r\nWork confidently with Adobe Creative Suite tools\r\nDevelop a professional portfolio for jobs and freelancing\r\nHandle real client projects professionally\r\nStart a freelancing career in graphic design\r\n\r\nThe course follows a project-based learning approach where students work on practical assignments and real industry-style projects. This helps students gain confidence, improve creativity, and build job-ready skills.\r\n\r\nWhether you want to become a freelance designer, work in a creative agency, grow your business branding, or build a professional design career, this course provides the complete roadmap to achieve your goals.\r\n\r\nCourse Highlights\r\nBeginner to Advanced Training\r\nLive Practical Projects\r\nProfessional Portfolio Development\r\nIndustry Standard Curriculum\r\nFreelancing & Career Support\r\nExpert Instructor Guidance\r\nCertificate After Completion\r\nLifetime Learning Support\r\nPerfect For\r\nStudents\r\nFreelancers\r\nJob Seekers\r\nEntrepreneurs\r\nSocial Media Managers\r\nContent Creators\r\nAnyone Interested in Graphic Design\r\n\r\nStart your creative journey today and become a professional graphic designer with practical skills, modern tools, and real-world experience.', 'Master the art of visual communication with our Professional Graphic Design Course. This course is designed for beginners and aspiring designers who want to build creative skills and start a successful career in graphic design. Learn Adobe Photoshop, Illustrator, InDesign, branding, social media design, print design, and portfolio creation through practical projects and real-world assignments. Get expert guidance, freelancing support, and a professional certificate to boost your career in the creative industry.', 'fas fa-cube', '#', 'uploads/courses/1779901911_6a1725d731819.png', '3 Month', 40, 0, 'MD.Jahidul Hakim', 'Creative Graphic Design Instructor\r\n\r\nA passionate and experienced graphic designer with expertise in Adobe Photoshop, Illustrator, InDesign, branding, social media design, and print media. With years of practical industry experience, the instructor has successfully completed numerous professional projects for businesses and brands.\r\n\r\nSpecialized in:\r\n\r\nLogo & Brand Identity Design\r\nSocial Media Marketing Design\r\nPrint & Packaging Design\r\nUI Design Basics\r\nFreelancing & Client Management\r\n\r\nThe instructor focuses on practical, project-based learning to help students build real-world skills, professional portfolios, and successful freelancing careers. Students receive step-by-step guidance, live support, and industry-standard training throughout the course.', 'uploads/instructors/1779902305_6a172761aff74.jpg', '{\r\n  \"modules\": [\r\n    {\r\n      \"title\": \"Introduction to Graphic Design\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 1,\r\n          \"topic\": \"What is Graphic Design? History & Evolution\",\r\n          \"type\": \"Video\",\r\n          \"resource\": \"https://youtu.be/example1\"\r\n        },\r\n        {\r\n          \"class_number\": 2,\r\n          \"topic\": \"Design Principles: Balance, Contrast, Hierarchy\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Slides PDF\"\r\n        },\r\n        {\r\n          \"class_number\": 3,\r\n          \"topic\": \"Color Theory & Psychology\",\r\n          \"type\": \"Interactive\",\r\n          \"resource\": \"Color wheel exercise\"\r\n        }\r\n      ],\r\n      \"projects\": \"Create a mood board for a brand of your choice\"\r\n    },\r\n    {\r\n      \"title\": \"Software Essentials\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 4,\r\n          \"topic\": \"Adobe Photoshop – Basics & Tools\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Project files download\"\r\n        },\r\n        {\r\n          \"class_number\": 5,\r\n          \"topic\": \"Adobe Illustrator – Vector Graphics\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Practice assets\"\r\n        },\r\n        {\r\n          \"class_number\": 6,\r\n          \"topic\": \"Adobe InDesign – Layout Design\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Brochure template\"\r\n        }\r\n      ],\r\n      \"projects\": \"Design a logo using Illustrator\"\r\n    },\r\n    {\r\n      \"title\": \"Typography\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 7,\r\n          \"topic\": \"Font Families & Pairing\",\r\n          \"type\": \"Video\",\r\n          \"resource\": \"Typography guide\"\r\n        },\r\n        {\r\n          \"class_number\": 8,\r\n          \"topic\": \"Hierarchy & Readability\",\r\n          \"type\": \"Quiz\",\r\n          \"resource\": \"Online quiz link\"\r\n        }\r\n      ],\r\n      \"projects\": \"Create a typographic poster\"\r\n    },\r\n    {\r\n      \"title\": \"Branding & Identity\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 9,\r\n          \"topic\": \"Logo Design Process\",\r\n          \"type\": \"Case Study\",\r\n          \"resource\": \"Famous logos analysis\"\r\n        },\r\n        {\r\n          \"class_number\": 10,\r\n          \"topic\": \"Brand Guidelines & Collateral\",\r\n          \"type\": \"Worksheet\",\r\n          \"resource\": \"Template download\"\r\n        }\r\n      ],\r\n      \"projects\": \"Full brand identity for a startup\"\r\n    },\r\n    {\r\n      \"title\": \"UI/UX for Graphic Designers\",\r\n      \"classes\": [\r\n        {\r\n          \"class_number\": 11,\r\n          \"topic\": \"Wireframing & Prototyping\",\r\n          \"type\": \"Tutorial\",\r\n          \"resource\": \"Figma file\"\r\n        },\r\n        {\r\n          \"class_number\": 12,\r\n          \"topic\": \"Design Systems & Components\",\r\n          \"type\": \"Lecture\",\r\n          \"resource\": \"Slides\"\r\n        }\r\n      ],\r\n      \"projects\": \"Design a mobile app homepage\"\r\n    }\r\n  ]\r\n}', 'Career Outcome\r\n\r\nAfter completing this Professional Graphic Design Course, students will be able to:\r\n\r\nCreate professional graphic design projects confidently\r\nDesign logos, branding, social media posts, flyers, banners, and marketing materials\r\nWork with industry-standard tools like Adobe Photoshop, Illustrator, InDesign, and Figma\r\nBuild a professional portfolio for jobs and freelancing\r\nStart freelancing on Fiverr, Upwork, and other marketplaces\r\nHandle real client projects professionally\r\nDevelop creative thinking and visual communication skills\r\nApply for graphic design jobs in agencies, companies, and digital marketing firms\r\nLaunch a personal design brand or creative business\r\nPossible Career Positions\r\nGraphic Designer\r\nBrand Identity Designer\r\nSocial Media Designer\r\nPrint & Packaging Designer\r\nUI Designer\r\nFreelance Graphic Designer\r\nCreative Content Designer\r\nMarketing Designer\r\nFreelancing Opportunities\r\n\r\nStudents can earn through:\r\n\r\nFiverr\r\nUpwork\r\nFreelancer.com\r\nSocial Media Client Services\r\nLocal & International Design Projects\r\nIndustry Skills Gained\r\nCreative Problem Solving\r\nProfessional Design Workflow\r\nClient Communication\r\nBranding Strategy\r\nPortfolio Presentation\r\nProject Management\r\nDigital Marketing Design Skills', 'No prior graphic design experience is required to join this course. This course is designed for beginners as well as students who want to improve their creative skills.\r\n\r\nBasic Requirements\r\nMinimum basic computer knowledge\r\nInterest in creativity and design\r\nA computer or laptop for practice\r\nInternet connection for online classes\r\nWillingness to practice and complete projects\r\nRecommended Equipment\r\nWindows or Mac computer\r\nMinimum 8 GB RAM\r\nAdobe Creative Cloud software (Photoshop & Illustrator recommended)\r\nWho Can Join?\r\nStudents\r\nFreelancers\r\nJob Seekers\r\nBusiness Owners\r\nContent Creators\r\nSocial Media Managers\r\nAnyone interested in graphic design and freelancing\r\nNo Experience Needed\r\n\r\nStep-by-step guidance will be provided from basic to advanced level, making it easy for beginners to learn professionally.', 'Adobe photoshop,Adobe Illustator', 1, 'Advanced', 10000.00, 15000.00, 2, 1, 0, '2026-05-24 09:33:01');

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
(1, 7, 'JAHID KHAN', 'mdjhk300@gmail.com', '01957288638', 'Dhaka,Gazipur,Boardbazar,National university,\r\nsouth khailkur,38no woard,sohid siddik road, holding no:446', 'demo', 'pending', NULL, 5000.00, '2026-05-24 17:52:40'),
(2, 7, 'JAHID KHAN', 'mdjhk300@gmail.com', '01957288638', 'Dhaka,Gazipur,Boardbazar,National university,\r\nsouth khailkur,38no woard,sohid siddik road, holding no:446', 'demo', 'failed', NULL, 5000.00, '2026-05-26 08:52:56');

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
(3, 'fas fa-chart-line', 'uploads/services/1779892222_6a16fffe9adf5.png', 'Custom Software Development', 'Custom software is a tailor-made application designed, built, and deployed to meet the exact, unique needs of a specific organization or user base. Unlike off-the-shelf software, which offers generic features for a broad market, custom software is engineered from the ground up to fit seamlessly into an organization\'s existing workflows and scale with its growth. By providing complete ownership and flexibility, it allows businesses to automate distinct operational processes, integrate proprietary systems, and gain a competitive edge through specialized functionality.', 'services.php#analytics', 0, 1),
(4, 'fas fa-paint-brush', 'uploads/services/1779890376_6a16f8c82dcc8.png', 'Enterprise Resource Point', 'An Enterprise Resource Planning (ERP) system is a centralized software platform that integrates all the core processes of a business into a single, unified system. By connecting departments like finance, human resources, manufacturing, supply chain, and sales, an ERP eliminates data silos and provides a shared, real-time source of truth. This allows organizations to automate routine tasks, improve collaboration across teams, and make data-driven decisions that increase overall efficiency and lower operational costs.', 'services.php#3d', 1, 1),
(5, 'fas fa-cube', 'uploads/services/1779890999_6a16fb378fe42.png', 'Student Management System', 'A Student Management System (SMS)—sometimes called a Student Information System (SIS)—is a centralized software platform designed to track and manage all student data and academic workflows. It streamlines daily school operations by integrating everything from admissions, attendance, and grading to scheduling, fee collection, and discipline records into a single database. By providing real-time data access, an SMS automates administrative tasks, reduces paperwork, and serves as a secure portal for communication among administrators, teachers, parents, and students.', '#', 2, 1),
(6, 'fas fa-cube', 'uploads/services/1779892418_6a1700c2dfe6e.png', 'Facebook Page Setup', 'Facebook Business Page Setup is a strategic service focused on creating and optimizing a professional Facebook presence to build brand authority and drive customer engagement. This process involves configuring essential business details, including standardizing your profile and cover visuals, writing SEO-optimized bios, setting up custom action buttons, and mapping out the core page categories. By integrating business hours, contact links, and automated messaging triggers, this setup establishes a secure, search-friendly digital storefront that turns casual social media traffic into a structured community and loyal customer base.', '#', 3, 1),
(7, 'fas fa-cube', 'uploads/services/1779893455_6a1704cf93d2a.png', 'Youtube Setup', 'YouTube Channel Setup & Optimization is a specialized service focused on building, branding, and configuring a high-performing video channel to maximize search visibility and audience retention. This setup goes far beyond basic creation, encompassing the design of a cohesive visual identity (channel banner, profile icon, and video watermarks) alongside the precise engineering of backend settings. By mapping out keyword-rich channel metadata, creating default upload configurations, organizing custom playlists, and activating core monetization and verification milestones, this process establishes a structured, professional framework built to turn standard viewers into a loyal, algorithmic-friendly subscriber base.', '#', 0, 1),
(8, 'fas fa-cube', 'uploads/services/1779894218_6a1707caf13fc.png', 'Custom Website Development', 'Custom Website Development is a premium engineering service focused on designing, building, and deploying a completely tailor-made web application or site from the ground up. Moving away from rigid, cookie-cutter templates, this approach uses modern frameworks and robust databases to translate a brand\'s unique logic, workflows, and user experience goals into clean, optimized code. Every layer is engineered with specific business objectives in mind—delivering lightning-fast loading speeds, advanced responsive layouts for all devices, tailored administrative dashboards, and airtight security protocols. The result is a highly scalable, flexible, and search-engine-optimized digital product that offers total ownership and gives your brand a distinct competitive edge on the web.', '#', 0, 1),
(9, 'fas fa-cube', 'uploads/services/1779896961_6a1712815cbcb.png', 'ADS Campaign', 'Paid Ads Campaign Management is a data-driven marketing service focused on designing, launching, and optimizing targeted advertising campaigns across platforms like Google, Meta (Facebook/Instagram), and LinkedIn to maximize return on investment (ROI). Rather than relying on guesswork, this service involves precise audience targeting based on demographics, user behavior, and intent, combined with strategic budget allocation and compelling ad copy. By continuously tracking performance metrics—such as click-through rates (CTR), conversion tracking, and cost per acquisition (CPA)—the campaign is dynamically fine-tuned to cut through ad fatigue, reduce wasted spend, and drive high-quality traffic that converts into measurable sales and leads.', '#', 0, 1);

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
(1, 'We are here for Guide you', 'Transform how your business interacts with the digital world.', 'uploads/sliders/1779815813_6a15d5854acfc.jpg', 'Explore Solutions', 'services.php', 1, 1),
(2, 'Explore the world with us', 'Step into a new dimension with our virtual reality solutions.', 'uploads/sliders/1779918432_6a176660de6a2.png', 'View Portfolio', 'portfolio.php', 1, 2),
(3, 'AR for Enterprise', 'Boost efficiency and engagement with custom AR applications.', 'uploads/sliders/1779918636_6a17672c811fd.png', 'Contact Us', 'contact.php', 1, 3);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
