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

ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `profiles`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
