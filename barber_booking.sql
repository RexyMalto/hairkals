-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 10:28 AM
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
-- Database: `barber_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `barbers`
--

CREATE TABLE `barbers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barbers`
--

INSERT INTO `barbers` (`id`, `name`, `bio`, `image`) VALUES
(1, 'Sam Rascals', 'With over 10 years behind the chair, Sam Rascals has shaped styles and confidence across all ages and hair types. From clean fades to creative cuts, Sam’s precision and easygoing vibe keep clients coming back. Whether it’s your first trim or a full transformation, Sam brings skill, laughter, and sharp scissors every time.', 'barber_685d83e0efd2e1.47533384.png'),
(2, 'Ahmed Alsanawi', 'From local talent to global name, Ahmed Alsanawi is the man behind the iconic cuts of Premier League stars and A-list celebs. Founder of A Star Barbers, Ahmed turned his passion into a precision craft — blending style, speed, and swagger in every cut. With clients like Paul Pogba and Eden Hazard, he’s not just a barber — he’s a brand.', 'barber_685d84a65e31f0.53841002.png'),
(3, 'Vic Blends', 'More than a barber — Vic Blends is a voice, a vibe, and a visionary. Known for his clean fades and powerful street conversations, Vic turned a pair of clippers into a tool for connection. With millions following his journey online, he’s cutting hair and dropping wisdom, one conversation at a time. The chair isn’t just for fades — it’s for stories.', 'barber_685d8543bbabf3.93667086.png');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `barber_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `booking_number` varchar(50) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `barber_id`, `date`, `time`, `booking_number`, `status`, `notes`, `created_at`) VALUES
(5, 2, 1, 1, '2025-06-23', '15:00:00', 'BB202506231E5F', 'confirmed', '', '2025-06-23 07:39:18'),
(6, 2, 2, 1, '2025-06-24', '15:00:00', 'BB20250624935B', 'pending', '', '2025-06-23 09:50:38'),
(7, 2, 1, 1, '2025-06-24', '17:00:00', 'BB2025062493CC', 'pending', '', '2025-06-23 15:16:57'),
(8, 2, 2, 1, '2025-06-25', '19:00:00', 'BB20250625C0A0', 'cancelled', '', '2025-06-23 15:24:58'),
(9, 2, 2, 1, '2025-06-23', '18:00:00', 'BB202506237BE3', 'pending', '', '2025-06-23 15:25:36'),
(10, 5, 1, 2, '2025-06-25', '19:00:00', 'BB202506256BE3', 'completed', '', '2025-06-23 17:44:33'),
(11, 5, 1, 2, '2025-06-25', '15:00:00', 'BB202506252B6B', 'pending', '', '2025-06-23 17:44:58'),
(12, 5, 2, 3, '2025-06-30', '17:00:00', 'BB202506306A90', 'confirmed', '', '2025-06-26 18:32:47'),
(13, 5, 2, 2, '2025-06-28', '15:00:00', 'BB202506284CD4', 'pending', '', '2025-06-26 18:42:31');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `sent_at`, `status`) VALUES
(1, 'zulhazim', 'zulhazim87@gmail.com', 'test', 'test test test test t4et', '2025-06-23 10:05:31', 'read'),
(2, 'Muhammad Nur Harris', 'nurxharris@gmail.com', 'd', 'dddddddddddddd', '2025-06-26 18:47:08', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`) VALUES
(1, 'Haircut', 'Your hairline isn’t hiding, it’s just waiting for a proper fade. Slide in for a cut – we’ll fix you up like a legend.', 25.00),
(2, 'Hair Colouring', 'Slim Shady dyed his hair blond… what’s stopping you? Go bold, go bright – let your hair do the talking. Book your colour session now!', 40.00),
(3, 'Shave', 'Your ex said you never finish what you start — prove them wrong with a clean shave from us.', 30.00),
(4, 'Kids Haircut', 'Messy hair, don’t care? Not on picture day. Bring your little champ in for a fresh cut – cool look, zero tantrums!', 20.00),
(5, 'Hair Wash', 'Hair so oily it could fry an egg? Dandruff doing the snow dance? Come get that fresh head feeling at Hair.Kal’s Barbershop.', 10.00),
(6, 'Hair Treatment', 'Is your scalp flakier than your ex? Hair feeling like hay? Swing by Hair.Kal’s Barbershop and let us bring your head back to life.', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`, `profile_image`) VALUES
(1, 'Admin User', 'admin@example.com', '123-456-7890', '$2y$10$.RNJ.SZ1cyoM9bfXLHYUe.tj4iy7W4BwA9rY6uUVY2HqrAt9PtI6C', 'admin', '2025-06-22 10:37:36', NULL),
(2, 'zulhazim', 'zulhazim87@gmail.com', '019232990', '$2y$10$4hcgD7IgKfg4tvbmr9DwkuU.VKistlx3yqocI14.Pmi0apJeOed2q', 'user', '2025-06-22 10:53:11', NULL),
(4, 'zack', 'eiipehnoyx46@hotmail.com', '990809uu98', '$2y$10$7qKeEzHVfz7vHwCK1M0bSeYSeiyovMYs3mz3FMRzjk8PpxRvKE33i', 'user', '2025-06-22 13:42:46', NULL),
(5, 'Harris', 'harris1234@gmail.com', '013-1234567', '$2y$10$j8uc4eGuMyCEgxudAFluG.uri70uwVIWbFzVxB3UCq/LjsuL4gb1y', 'user', '2025-06-23 17:43:35', 'user_5_1750864907.png'),
(6, 'Ahmad', 'ahmadkassim123@gmail.com', '019-4756895', '$2y$10$jjlTt57aeOKSlJRUvENN7eoM3PFrmO.Na2BokMEPRkAYbnR0i.zai', 'user', '2025-06-24 16:57:38', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barbers`
--
ALTER TABLE `barbers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `barber_id` (`barber_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `barbers`
--
ALTER TABLE `barbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
