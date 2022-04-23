CREATE TABLE `signup_emails` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `signup_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `invited_time` timestamp NULL DEFAULT NULL,
  `invite_code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `signup_emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `signup_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
