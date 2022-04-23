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

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sessions`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
