CREATE TABLE IF NOT EXISTS `bandsBandCustomFields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `bandsBandCustomFields`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `bandsBandCustomFields`
ADD CONSTRAINT `bandsBandCustomFields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `bandsBand` (`id`) ON DELETE CASCADE;