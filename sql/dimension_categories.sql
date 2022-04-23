CREATE TABLE `dimension_categories` (
  `id` int(255) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `color` varchar(100) NOT NULL,
  `default_order` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `dimension_categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dimension_categories`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
