CREATE TABLE `preferences` (
  `id` int(255) NOT NULL,
  `profile_id` int(255) NOT NULL,
  `dimension_id` int(255) NOT NULL,
  `yesNo` tinyint(1) NOT NULL DEFAULT '0',
  `slider` float NOT NULL DEFAULT '1',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `preferences`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
