CREATE TABLE `dimensions` (
  `id` int(255) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `category` int(100) NOT NULL,
  `default_order` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `dimensions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dimensions`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;
