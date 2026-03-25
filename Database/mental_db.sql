-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 08:50 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `counselling_sessions`
--

CREATE TABLE `counselling_sessions` (
  `id` int(11) NOT NULL,
  `counsellor_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `meet_link` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','disapproved') NOT NULL DEFAULT 'pending',
  `status` enum('scheduled','completed','archived') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselling_sessions`
--

INSERT INTO `counselling_sessions` (`id`, `counsellor_id`, `title`, `session_date`, `start_time`, `end_time`, `meet_link`, `approval_status`, `status`, `created_at`) VALUES
(1, 1, 'Stress Management Workshop', '2026-02-12', '10:00:00', '11:00:00', 'https://meet.google.com/stress1', 'disapproved', 'scheduled', '2026-02-09 06:25:07'),
(2, 1, 'Anxiety Coping Strategies', '2026-02-13', '11:30:00', '12:30:00', 'https://meet.google.com/anxiety2', 'disapproved', 'scheduled', '2026-02-09 06:25:07'),
(3, 1, 'Time Management for Students', '2026-02-14', '09:00:00', '10:00:00', 'https://meet.google.com/time3', 'disapproved', 'scheduled', '2026-02-09 06:25:07'),
(4, 1, 'Mindfulness Meditation', '2026-02-15', '14:00:00', '15:00:00', 'https://meet.google.com/mindful4', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(5, 1, 'Career Guidance Session', '2026-02-16', '13:00:00', '14:00:00', 'https://meet.google.com/career5', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(6, 1, 'Emotional Intelligence', '2026-02-17', '10:30:00', '11:30:00', 'https://meet.google.com/emotion6', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(7, 1, 'Conflict Resolution', '2026-02-18', '12:00:00', '13:00:00', 'https://meet.google.com/conflict7', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(8, 1, 'Building Self-Confidence', '2026-02-19', '15:00:00', '16:00:00', 'https://meet.google.com/confidence8', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(9, 1, 'Effective Communication', '2026-02-20', '09:30:00', '10:30:00', 'https://meet.google.com/communication9', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(10, 1, 'Work-Life Balance Tips', '2026-02-21', '11:00:00', '12:00:00', 'https://meet.google.com/worklife10', 'approved', 'scheduled', '2026-02-09 06:25:07'),
(11, 3, 'Prototype Session', '2026-02-23', '21:28:00', '22:28:00', 'http://localhost/fall25/prototypes/mental/counsellor/add_session.php', 'approved', 'scheduled', '2026-02-21 16:28:39'),
(12, 1, 'dds', '2026-03-25', '12:55:00', '12:55:00', 'http://localhost/fall25/mental/counsellor/add_session.php', 'approved', 'scheduled', '2026-03-25 07:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `mood_checkins`
--

CREATE TABLE `mood_checkins` (
  `id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `counsellor_id` int(11) NOT NULL,
  `mood` enum('very_low','low','neutral','good','excellent') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mood_checkins`
--

INSERT INTO `mood_checkins` (`id`, `participant_id`, `counsellor_id`, `mood`, `notes`, `created_at`) VALUES
(1, 2, 1, 'good', 'Just Test', '2026-02-09 06:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `session_requests`
--

CREATE TABLE `session_requests` (
  `id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `counsellor_id` int(11) NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_requests`
--

INSERT INTO `session_requests` (`id`, `participant_id`, `counsellor_id`, `preferred_date`, `preferred_time`, `reason`, `status`, `created_at`) VALUES
(1, 2, 1, '2026-02-11', '00:05:00', 'fsad', 'approved', '2026-02-09 07:05:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('participant','counsellor') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `preferred_counsellor_id` int(11) DEFAULT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `full_name`, `email`, `password`, `phone`, `status`, `preferred_counsellor_id`, `organization`, `age`, `gender`, `specialization`, `qualification`, `experience_years`, `bio`, `created_at`) VALUES
(1, 'counsellor', 'Counsellor Ali', 'Counsellor@gmail.com', '$2y$10$kpXrYW/VrAb8kKSnzvKkm.x05aO6qa.8yMHOA3QwY78DlEAvh9liC', '03176526827', 'active', NULL, NULL, 23, 'male', 'fdsa', 'MBBS, FCPS', 2, '23fdsa', '2026-02-09 05:58:01'),
(2, 'participant', 'Participant one', 'participant@gmail.com', '$2y$10$xkx3o1p2pKsUFx8603nEYOhcvIDfHMoNctmgyn1EkDuTaRSu/ChuO', '+923176526823', 'active', 1, 'fdsa', 33, 'female', NULL, NULL, NULL, NULL, '2026-02-09 06:26:56'),
(3, 'counsellor', 'Con One', 'con@gmail.com', '$2y$10$gLyM1v.MJuQg4g3dl8OwHOuGXq9R8vd0915eOUGcdV82G0A7o6Nqm', '03266029059', 'active', NULL, NULL, 23, 'male', NULL, NULL, NULL, NULL, '2026-02-21 16:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `wellness_resources`
--

CREATE TABLE `wellness_resources` (
  `id` int(11) NOT NULL,
  `counsellor_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `resource_type` enum('article','blog','video','tool') NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wellness_resources`
--

INSERT INTO `wellness_resources` (`id`, `counsellor_id`, `title`, `content`, `resource_type`, `file`, `created_at`) VALUES
(1, 1, 'Managing Stress Effectively', 'Learn techniques to manage stress and improve mental well-being.', 'article', NULL, '2026-02-09 07:19:03'),
(2, 1, 'Healthy Morning Routine', 'A guide to starting your day with positive energy and habits.', 'blog', NULL, '2026-02-09 07:19:03'),
(3, 1, 'Mindfulness Meditation Video', 'A short video guiding you through mindfulness meditation.', 'video', NULL, '2026-02-09 07:19:03'),
(4, 1, 'Time Management Tool', 'A downloadable tool to help you organize your daily tasks.', 'tool', NULL, '2026-02-09 07:19:03'),
(5, 1, 'Overcoming Anxiety', 'Tips and strategies to cope with anxiety in daily life.', 'article', NULL, '2026-02-09 07:19:03'),
(6, 1, 'Boost Your Productivity', 'A blog post on productivity hacks and techniques.', 'blog', NULL, '2026-02-09 07:19:03'),
(7, 1, 'Yoga for Beginners', 'Video tutorial for beginner-friendly yoga exercises.', 'video', NULL, '2026-02-09 07:19:03'),
(8, 1, 'Daily Mood Tracker', 'Tool to track your moods daily and identify patterns.', 'tool', NULL, '2026-02-09 07:19:03'),
(9, 1, 'Work-Life Balance', 'Article discussing ways to balance personal and professional life.', 'article', NULL, '2026-02-09 07:19:03'),
(10, 1, 'Gratitude Journal', 'Blog explaining how maintaining a gratitude journal can improve mental health.', 'blog', NULL, '2026-02-09 07:19:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `counselling_sessions`
--
ALTER TABLE `counselling_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `counsellor_id` (`counsellor_id`);

--
-- Indexes for table `mood_checkins`
--
ALTER TABLE `mood_checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_id` (`participant_id`),
  ADD KEY `counsellor_id` (`counsellor_id`);

--
-- Indexes for table `session_requests`
--
ALTER TABLE `session_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_id` (`participant_id`),
  ADD KEY `counsellor_id` (`counsellor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wellness_resources`
--
ALTER TABLE `wellness_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `counsellor_id` (`counsellor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `counselling_sessions`
--
ALTER TABLE `counselling_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mood_checkins`
--
ALTER TABLE `mood_checkins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `session_requests`
--
ALTER TABLE `session_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wellness_resources`
--
ALTER TABLE `wellness_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `counselling_sessions`
--
ALTER TABLE `counselling_sessions`
  ADD CONSTRAINT `counselling_sessions_ibfk_1` FOREIGN KEY (`counsellor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mood_checkins`
--
ALTER TABLE `mood_checkins`
  ADD CONSTRAINT `mood_checkins_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mood_checkins_ibfk_2` FOREIGN KEY (`counsellor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `session_requests`
--
ALTER TABLE `session_requests`
  ADD CONSTRAINT `session_requests_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `session_requests_ibfk_2` FOREIGN KEY (`counsellor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wellness_resources`
--
ALTER TABLE `wellness_resources`
  ADD CONSTRAINT `wellness_resources_ibfk_1` FOREIGN KEY (`counsellor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
