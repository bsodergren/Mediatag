
CREATE TABLE `plexweb_playlist_data` (
  `id` int NOT NULL,
  `name` varchar(70) NOT NULL,
  `genre` varchar(200) NOT NULL,
  `Library` varchar(30) DEFAULT NULL,
  `search_id` int DEFAULT NULL,
  `hide` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `plexweb_playlist_data`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `search_id` (`search_id`);


ALTER TABLE `plexweb_playlist_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
