-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 23, 2022 at 04:35 PM
-- Server version: 5.7.21
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `fullspectrumdating`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(255) NOT NULL,
  `email` varchar(1000) NOT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `signupEmail_id` int(255) DEFAULT NULL,
  `password_hash` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dimensions`
--

CREATE TABLE `dimensions` (
  `id` int(255) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `category` int(100) NOT NULL,
  `default_order` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dimensions`
--

INSERT INTO `dimensions` (`id`, `name`, `category`, `default_order`) VALUES
(1, 'Cis', 1, 1),
(2, 'Trans', 1, 2),
(3, 'Non-binary', 1, 3),
(4, 'Man', 1, 4),
(5, 'Woman', 1, 5),
(6, 'Gender fluid', 1, 6),
(7, 'Agender', 1, 7),
(8, 'Butch', 1, 8),
(9, 'Feminine', 1, 9),
(10, 'Masculine', 1, 10),
(11, 'Androgynous', 1, 11),
(12, 'Boi', 1, 12),
(13, 'Gurl', 1, 13),
(14, 'Still figuring it out', 1, 14),
(15, 'Two spirit', 1, 15),
(16, 'Drag Queen', 1, 16),
(17, 'Polyamorous', 2, 1),
(18, 'Monogamous', 2, 2),
(19, 'Monogamish', 2, 3),
(20, 'Romantic', 2, 4),
(21, 'Single', 2, 5),
(22, 'Primarily partnered', 2, 6),
(23, 'Seeking primary', 2, 7),
(24, 'Long term relationship oriented', 2, 8),
(25, 'Sugar mama/daddy', 2, 9),
(26, 'Sugar baby', 2, 10),
(27, 'Open to hookups', 2, 11),
(28, 'Open to cuddle buddies', 2, 12),
(29, 'Open to friendships', 2, 13),
(30, 'Open to couples', 2, 14),
(31, 'Seeking third', 2, 15),
(32, 'Likely to message first', 2, 16),
(33, 'Willing to invest emotional labor in a partnership', 2, 17),
(34, 'Willing to take responsibility for safe sex', 2, 18),
(35, 'Willing to take responsibility for contraception', 2, 19),
(36, 'Dominant', 3, 1),
(37, 'Submissive', 3, 2),
(38, 'Switch', 3, 3),
(39, 'Asexual', 3, 4),
(40, 'Demisexual', 3, 5),
(41, 'Queer', 3, 6),
(42, 'Straight', 3, 7),
(43, 'Gay', 3, 8),
(44, 'Lesbian', 3, 9),
(45, 'Bisexual', 3, 10),
(46, 'Pansexual', 3, 11),
(47, 'Questioning', 3, 12),
(48, 'Top', 3, 13),
(49, 'Bottom', 3, 14),
(50, 'High sex drive', 3, 15),
(51, 'Willing to prioritize a partner\'s pleasure', 3, 16),
(52, 'Femme-attracted', 3, 17),
(53, 'Androgyny-attracted', 3, 18),
(54, 'Masc-attracted', 3, 19),
(55, 'Sexually inexperienced', 3, 20),
(56, 'Kink inexperienced', 3, 21),
(57, 'Queer inexperienced', 3, 22),
(58, 'Looking to explore', 3, 23),
(59, 'Sadist', 3, 24),
(60, 'Masochist', 3, 25),
(61, 'Into aftercare', 3, 26),
(62, 'Into bondage', 3, 27),
(63, 'Into body worship', 3, 28),
(64, 'Sensualist', 3, 29),
(65, 'Boob enthusiast', 3, 30),
(66, 'Butt enthusiast', 3, 31),
(67, 'Penis enthusiast', 3, 32),
(68, 'Vulva enthusiast', 3, 33),
(69, 'Fellatio enthusiast', 3, 34),
(70, 'Cunnilingus enthusiast', 3, 35),
(71, 'Into receiving anal play', 3, 36),
(72, 'Into giving anal play', 3, 37),
(73, 'Into strap-ons', 3, 38),
(74, 'Vanilla', 3, 39),
(75, 'Kinky', 3, 40),
(76, 'Exhibitionist', 3, 41),
(77, 'Voyeur', 3, 42),
(78, 'Into humiliation', 3, 43),
(79, 'Foot fetishist', 3, 44),
(80, 'Sissy', 3, 45),
(81, 'Slut', 3, 46),
(82, 'Cuckold', 3, 47),
(83, 'Cougar', 3, 48),
(84, 'Daddy', 3, 49),
(85, 'Mommy', 3, 50),
(86, 'Clothing fetishist', 3, 51),
(87, 'Bimbo', 3, 52),
(88, 'Furry', 3, 53),
(89, 'Mistress/Master', 3, 54),
(90, 'Sex slave', 3, 55),
(91, 'Goddess/God', 3, 56),
(92, 'Into water sports', 3, 57),
(93, 'Race player', 3, 58),
(94, 'Age player', 3, 59),
(95, 'Pet player', 3, 60),
(96, 'Vegetarian', 4, 1),
(97, 'Vegan', 4, 2),
(98, 'Diet conscious', 4, 3),
(99, 'Physically active', 4, 4),
(100, 'In therapy', 4, 5),
(101, 'Living with mental illness', 4, 6),
(102, 'Neuro-typical', 4, 7),
(103, 'Living with chronic health condition', 4, 8),
(104, 'Autistic', 4, 9),
(105, 'Trauma survivor', 4, 10),
(106, 'Care-giver', 4, 11),
(107, 'Divorced', 4, 12),
(108, 'Freelancer', 4, 13),
(109, '9-5er', 4, 14),
(110, 'Career focused', 4, 15),
(111, 'Entrepreneur', 4, 16),
(112, 'Medical professional', 4, 17),
(113, 'Education professional', 4, 18),
(114, 'Seeking roommate', 2, 50),
(115, 'Parent', 4, 19),
(116, 'In the military', 4, 20),
(117, 'Combat veteran', 4, 21),
(118, 'Person of color', 4, 22),
(119, 'Descendant of slaves', 4, 23),
(120, 'En la communidad hispanica', 4, 24),
(121, 'Ex-pat', 4, 25),
(122, 'Learning English', 4, 26),
(123, 'Learning another language', 4, 27),
(124, 'Struggling with life', 4, 28),
(125, 'Sober', 4, 29),
(126, 'Tobacco smoker', 4, 30),
(127, 'Partaker of 420', 4, 31),
(128, 'Partaker of alcohol', 4, 32),
(130, 'Sex worker', 4, 34),
(131, 'Outdoorsy', 5, 1),
(132, 'Gym goer', 5, 2),
(133, 'Into fitness', 5, 3),
(134, 'Athlete', 5, 4),
(135, 'Into cooking', 5, 5),
(136, 'Into makeup', 5, 6),
(137, 'Into fashion', 5, 7),
(138, 'Partier', 5, 8),
(139, 'Yogi', 5, 9),
(140, 'Artist', 5, 10),
(141, 'Meditator', 5, 11),
(142, 'Dog lover', 5, 12),
(143, 'Cat lover', 5, 13),
(144, 'Reader', 5, 14),
(145, 'Music enthusiast', 5, 15),
(146, 'Musician', 5, 16),
(147, 'Dancer', 5, 17),
(148, 'Nerd', 5, 18),
(149, 'Cosplayer', 5, 19),
(150, 'Gamer', 5, 20),
(151, 'Movie buff', 5, 21),
(152, 'Sci fi lover', 5, 22),
(153, 'Binge-watcher', 5, 23),
(154, 'Burning Man attendee', 5, 24),
(155, 'Crossdresser', 5, 25),
(156, 'Religious', 6, 1),
(157, 'Atheist', 6, 2),
(158, 'Skeptic', 6, 3),
(159, 'Mindful', 6, 4),
(160, 'Altruist', 6, 5),
(161, 'Hedonist', 6, 6),
(162, 'Astrologer', 6, 7),
(163, 'Pacifist', 6, 8),
(164, 'Liberal', 6, 9),
(165, 'Conservative', 6, 10),
(166, 'Buddhist', 6, 11),
(167, 'Jewish', 6, 12),
(168, 'Christian', 6, 13),
(169, 'Muslim', 6, 14),
(170, 'Hindu', 6, 15),
(171, 'Catholic', 6, 16),
(172, 'Feminist', 6, 17),
(173, 'Academic', 6, 18),
(174, 'Intellectual', 6, 19),
(175, 'Scientist', 6, 20),
(176, 'Anarchist', 6, 21),
(177, 'Socialist', 6, 22),
(178, 'Communist', 6, 23),
(179, 'Libertarian', 6, 24),
(180, 'Black Lives Matter', 6, 25),
(181, 'Pro immigrant rights', 6, 26),
(182, 'Pro LGBTQ+ rights', 6, 27),
(183, 'Climate change defender', 6, 28),
(184, 'Pro sex worker rights', 6, 29),
(185, 'Joyful', 7, 1),
(186, 'Friendly', 7, 2),
(187, 'Funny', 7, 3),
(188, 'Jealous', 7, 4),
(189, 'Trusting', 7, 5),
(190, 'Quick to anger', 7, 6),
(191, 'Open', 7, 7),
(192, 'Confident', 7, 8),
(193, 'Shy', 7, 9),
(194, 'Silly', 7, 10),
(195, 'Organized', 7, 11),
(196, 'Independent', 7, 12),
(197, 'Commitment-phobe', 7, 13),
(198, 'Quiet', 7, 14),
(199, 'Nurturing', 7, 15),
(200, 'Outgoing', 7, 16),
(201, 'Anxious', 7, 17),
(202, 'Energetic', 7, 18),
(203, 'Awkward', 7, 19),
(204, 'Adventurous', 7, 20),
(205, 'Empathetic', 7, 21),
(206, 'Emotionally intelligent', 7, 22),
(207, 'Analytically intelligent', 7, 23),
(208, 'Assigned female at birth', 8, 1),
(209, 'Assigned male at birth', 8, 2),
(210, 'Intersex', 8, 3),
(211, 'Dark skinned', 8, 4),
(212, 'Brown skinned', 8, 5),
(213, 'Fair skinned', 8, 6),
(214, 'Asian heritage', 4, 22),
(215, 'Hairy', 8, 8),
(216, 'Smooth', 8, 9),
(217, 'Able bodied', 8, 10),
(218, 'Bald', 8, 11),
(219, 'Bearded', 8, 12),
(220, 'Tattooed', 8, 13),
(221, 'Pierced', 8, 14),
(222, 'Penis-haver', 8, 15),
(223, 'Vagina-haver', 8, 16),
(224, 'Hard bodied', 8, 17),
(225, 'Soft bodied', 8, 18),
(226, 'Curvy', 8, 19),
(227, 'Slender', 8, 20),
(228, 'Muscled', 8, 21),
(229, 'Fit', 8, 22),
(230, 'Thick', 8, 23),
(231, 'Broad shouldered', 8, 24),
(232, 'Fat', 8, 25),
(233, 'Chubby', 8, 26),
(234, 'Hourglass shaped', 8, 27),
(235, 'Busty', 8, 28),
(236, 'Flat chested', 8, 29),
(237, 'Hung', 8, 30),
(238, 'Bootylicious', 8, 31),
(240, 'Seeking collaborators', 2, 30),
(241, 'Tomboy', 1, 20),
(242, 'Drag King', 1, 17),
(243, 'Tall', 8, 50),
(244, 'Short', 8, 51),
(245, 'Selfie-lover', 5, 40),
(246, 'Civil Servant', 4, 30),
(247, 'Service-oriented', 3, 212),
(248, 'Indigenous heritage', 4, 23),
(249, 'Deaf', 4, 10),
(250, 'Differently abled', 4, 9),
(251, 'Into public displays of affection', 3, 17),
(252, 'Leg enthusiast', 3, 31),
(253, 'Hand fetishist', 3, 44),
(254, 'Stocky', 8, 20),
(255, 'Union member', 4, 16),
(256, 'African heritage', 4, 22),
(257, 'European heritage', 4, 22),
(258, 'Seeking childcare', 2, 20),
(259, 'Breast-haver', 8, 16),
(260, 'Introvert', 7, 1),
(261, 'Extrovert', 7, 1),
(262, 'Bull', 3, 46),
(263, 'Dude', 1, 14),
(264, 'Bro', 1, 14),
(265, 'Babe', 1, 14),
(266, 'Vain', 7, 4),
(267, 'Aggressive', 7, 5),
(268, 'Seeking mentor', 2, 21),
(269, 'Seeking mentee', 2, 22),
(270, 'Pro gun control', 6, 27),
(271, 'Gun owner', 4, 21),
(272, 'Brat', 3, 45),
(273, 'Little', 3, 57),
(274, 'Big', 3, 57),
(275, 'Pillow princess', 3, 43),
(276, 'Seeking pen pal', 2, 24),
(277, 'Domestic worker', 4, 19),
(278, 'Into erotic literature', 5, 25),
(279, 'Goth', 5, 18),
(280, 'Into genealogy', 5, 25),
(281, 'Economically comfortable', 4, 20),
(282, 'Uterus-haver', 8, 16),
(283, 'Doll', 3, 46),
(284, 'Spiritual', 6, 6),
(285, 'Into consensual non-consent', 3, 55),
(286, 'Light skinned', 8, 4);

-- --------------------------------------------------------

--
-- Table structure for table `dimension_categories`
--

CREATE TABLE `dimension_categories` (
  `id` int(255) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `color` varchar(100) NOT NULL,
  `default_order` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dimension_categories`
--

INSERT INTO `dimension_categories` (`id`, `name`, `color`, `default_order`) VALUES
(1, 'Gender', 'rgb(138,70,171)', 1),
(2, 'Relationship Style', 'rgb(111,209,176)', 2),
(3, 'Sexuality', 'rgb(222,89,111)', 3),
(4, 'Life', 'rgb(242, 209, 102)', 4),
(5, 'Interests', 'rgb(240,143,81)', 5),
(6, 'Values', 'rgb(67,60,178)', 6),
(7, 'Personality', 'rgb(204,230,124)', 7),
(8, 'Body', 'rgb(0,123,160)', 8);

-- --------------------------------------------------------

--
-- Table structure for table `identities`
--

CREATE TABLE `identities` (
  `id` int(255) NOT NULL,
  `profile_id` int(255) NOT NULL,
  `dimension_id` int(255) NOT NULL,
  `yesNo` tinyint(1) NOT NULL DEFAULT '0',
  `slider` float NOT NULL DEFAULT '1',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

CREATE TABLE `preferences` (
  `id` int(255) NOT NULL,
  `profile_id` int(255) NOT NULL,
  `dimension_id` int(255) NOT NULL,
  `yesNo` tinyint(1) NOT NULL DEFAULT '0',
  `slider` float NOT NULL DEFAULT '1',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(255) NOT NULL,
  `account_id` int(255) NOT NULL,
  `preferred_name` varchar(200) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `picture_file` varchar(256) DEFAULT NULL,
  `nyc` tinyint(4) DEFAULT NULL,
  `contact` varchar(500) DEFAULT NULL,
  `bioline` varchar(140) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Human',
  `bio1` varchar(5000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `bio2` varchar(5000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `bio3` varchar(5000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(255) NOT NULL,
  `account` int(255) NOT NULL,
  `profile` int(255) NOT NULL,
  `signon_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(255) NOT NULL,
  `session_hash` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `signup_emails`
--

CREATE TABLE `signup_emails` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `signup_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `invited_time` timestamp NULL DEFAULT NULL,
  `invite_code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dimensions`
--
ALTER TABLE `dimensions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dimension_categories`
--
ALTER TABLE `dimension_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `identities`
--
ALTER TABLE `identities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `signup_emails`
--
ALTER TABLE `signup_emails`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dimensions`
--
ALTER TABLE `dimensions`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `dimension_categories`
--
ALTER TABLE `dimension_categories`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `identities`
--
ALTER TABLE `identities`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preferences`
--
ALTER TABLE `preferences`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signup_emails`
--
ALTER TABLE `signup_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
