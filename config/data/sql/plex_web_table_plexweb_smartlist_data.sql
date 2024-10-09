
CREATE TABLE `plexweb_smartlist_data` (
  `id` int NOT NULL,
  `name` varchar(70) NOT NULL,
  `filter` varchar(200) NOT NULL,
  `Library` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `plexweb_smartlist_data`
  ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `plexweb_smartlist_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;