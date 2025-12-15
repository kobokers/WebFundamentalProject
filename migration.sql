-- =====================================================
-- MASTER MIGRATION SCRIPT (CONSOLIDATED)
-- Includes: Course Images, Enhancements, Features, Performance
-- Run this in phpMyAdmin or MySQL CLI
-- =====================================================

-- ---------------------------------------------------------------------
-- PART 1: Course Image (from migration_course_image.sql)
-- ---------------------------------------------------------------------
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `course_image` VARCHAR(255) DEFAULT NULL;

-- ---------------------------------------------------------------------
-- PART 2: Enhancements (from migration_enhancements.sql)
-- ---------------------------------------------------------------------

-- 1. Course Ratings Table
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
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `category` varchar(50) DEFAULT 'General' AFTER `description`;

-- ---------------------------------------------------------------------
-- PART 3: Features (from migration_features.sql)
-- ---------------------------------------------------------------------

-- 1. Profile Pictures column
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `profile_picture` varchar(255) DEFAULT NULL AFTER `status`;

-- 2. Course Duration column (in hours)
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `duration` int(11) DEFAULT NULL AFTER `category`;

-- 3. Quizzes table
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `passing_score` int(11) DEFAULT 60,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Quiz Questions table
CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `question_order` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Quiz Attempts table
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `passed` tinyint(1) DEFAULT 0,
  `attempted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

