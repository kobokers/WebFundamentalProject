-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 02:37 AM
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
-- Database: `olms`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `level` varchar(50) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `fee` double(10,2) DEFAULT NULL,
  `lecturer_id` int(11) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `level`, `language`, `fee`, `lecturer_id`, `description`) VALUES
(1, 'Advanced Javascript', 'Intermediate', 'English', 1000.00, 4, 'Text about javascript'),
(2, 'CS150 Computer Science', 'Advanced', 'English', 0.00, 8, ''),
(3, 'Fundamentals Mathematics', 'Beginner', 'English', 100.00, 8, ''),
(4, 'Deep Learning', 'Advanced', 'English', 10.00, 8, ''),
(5, 'Database Design', 'Intermediate', 'English', 0.00, 4, ''),
(6, 'Web Programming', 'Intermediate', 'English', 100.00, 4, 'Learn more challenge about web programming');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_replies`
--

CREATE TABLE `discussion_replies` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussion_replies`
--

INSERT INTO `discussion_replies` (`id`, `thread_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 4, 'make sure finish by saturday', '2025-11-02 08:11:46'),
(2, 1, 2, 'Ok noted sir', '2025-11-02 08:18:15'),
(3, 1, 9, 'good morning sir ok noted', '2025-11-02 10:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_threads`
--

CREATE TABLE `discussion_threads` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussion_threads`
--

INSERT INTO `discussion_threads` (`id`, `course_id`, `user_id`, `title`, `content`, `created_at`) VALUES
(1, 1, 4, 'Basic javascript task', 'Make a js code where it will show alert to the webpage', '2025-11-02 08:11:26'),
(2, 2, 8, 'TASK: DO A HTML CODE', 'make sure finish on friday', '2025-11-04 20:11:04');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enroll_date` date DEFAULT curdate(),
  `payment_date` datetime DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`id`, `user_id`, `course_id`, `enroll_date`, `payment_date`, `payment_status`) VALUES
(1, 2, 1, '2025-11-01', '2025-11-01 17:59:10', 'paid'),
(2, 9, 2, '2025-11-02', NULL, 'paid'),
(3, 9, 1, '2025-11-02', '2025-11-02 10:02:15', 'paid'),
(4, 9, 3, '2025-11-02', NULL, 'pending'),
(5, 2, 4, '2025-11-04', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `module_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `module_order`) VALUES
(1, 1, 'Introduction to Java Script', 1),
(3, 1, 'Basic Javascripting', 2),
(4, 2, 'Introduction to Computer Science', 1),
(5, 2, 'Basic Computing (HTML)', 2),
(6, 2, 'Basic Computing (CSS)', 3),
(7, 2, 'Basic Computing (JS)', 4),
(8, 2, 'Basic Computing (Frameworks)', 5),
(9, 2, 'Frameworks (Laravel)', 6),
(10, 3, 'Introduction to math', 1),
(11, 3, 'Quadratic 1', 2),
(12, 3, 'Calculus I', 3),
(13, 4, 'Introduction to AI', 1),
(14, 4, 'Matrix I', 2),
(15, 4, 'Python ', 3),
(16, 4, 'Pytorch', 4),
(17, 4, 'C Language', 5),
(18, 4, 'Automated Script', 6);

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `status` enum('started','completed') NOT NULL DEFAULT 'started'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `user_id`, `module_id`, `completion_date`, `status`) VALUES
(1, 2, 1, '2025-11-01 17:59:18', 'completed'),
(2, 2, 3, '2025-11-01 17:59:21', 'completed'),
(3, 9, 4, '2025-11-02 10:03:46', 'completed'),
(4, 9, 5, '2025-11-02 10:03:47', 'completed'),
(5, 9, 1, '2025-11-02 10:03:55', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','admin') NOT NULL DEFAULT 'student',
  `status` enum('pending','active','blocked') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, 'Admin', 'admin@example.com', '123', 'admin', 'active'),
(2, 'akmal', 'akmal@example.com', '123', 'student', 'active'),
(4, 'rizal', 'rizal@lecturer.com', '123', 'lecturer', 'active'),
(8, 'Dr. Tan Wen Cheng', 'tan0103@lecturer.olms.com', '123', 'lecturer', 'active'),
(9, 'aiman', 'aiman@student.olms.com', '123', 'student', 'active'),
(10, 'siti', 'siti@lecturer.olms.com', '$2y$10$yPF8pEiNGlS/7kBL2wy5v.7DbYh16ve8Hs20xqDzC6gZbsW/Wj3p2', 'lecturer', 'pending'),
(11, 'Dr. Hanafi bin Adwin', 'hanafi@lecturer.olms.com', '$2y$10$0Ahbuh4Uk8Ob7vlqPHUioeIjCR.Uw.AeTG5tECMG4SR/6Fd4Bfkmq', 'lecturer', 'pending'),
(12, 'Putra Hazim', 'putput@student.olms.com', '$2y$10$NRJeH3VLZWKmgUD6E.JCKeit7eVioXB.jIo88QOqe20XA7zhi6pCm', 'student', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD CONSTRAINT `discussion_replies_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `discussion_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  ADD CONSTRAINT `discussion_threads_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_threads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
