
CREATE TABLE `plexweb_settings` (
  `id` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `value` varchar(500) DEFAULT NULL,
  `type` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `plexweb_settings`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `plexweb_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
  
  