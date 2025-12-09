-- =====================================================
-- LMS Enhancement Migration Script
-- Run this in phpMyAdmin or MySQL CLI
-- =====================================================

-- 1. Course Ratings Table
-- Allows students to rate courses (1-5 stars) with optional review
CREATE TABLE IF NOT EXISTS `course_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`course_id`, `user_id`),
  KEY `course_id` (`course_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `course_ratings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Discussion Upvotes Table
-- Tracks which users upvoted which replies (prevents duplicates)
CREATE TABLE IF NOT EXISTS `discussion_upvotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reply_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_upvote` (`reply_id`, `user_id`),
  KEY `reply_id` (`reply_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `discussion_upvotes_ibfk_1` FOREIGN KEY (`reply_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discussion_upvotes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Password Reset Tokens Table
-- For secure password recovery functionality
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Add category column to courses table
-- Check if column exists before adding (MySQL 8.0+ syntax alternative below)
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `category` varchar(50) DEFAULT 'General' AFTER `description`;

-- Note: If "IF NOT EXISTS" fails on older MySQL, use:
-- ALTER TABLE `courses` ADD COLUMN `category` varchar(50) DEFAULT 'General' AFTER `description`;

-- =====================================================
-- End of Migration
-- =====================================================
