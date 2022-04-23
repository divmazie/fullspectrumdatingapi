CREATE TABLE `accounts` (
  `id` int(255) NOT NULL,
  `email` varchar(1000) NOT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `signupEmail_id` int(255) DEFAULT NULL,
  `password_hash` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `accounts`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
