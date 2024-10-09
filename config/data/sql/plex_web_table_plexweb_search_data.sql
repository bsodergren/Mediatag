
CREATE TABLE `plexweb_search_data` (
  `id` int NOT NULL,
  `video_list` text NOT NULL,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE `plexweb_search_data`
  ADD UNIQUE KEY `id` (`id`);
  
  
ALTER TABLE `plexweb_search_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;