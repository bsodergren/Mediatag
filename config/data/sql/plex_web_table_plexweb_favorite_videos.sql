
CREATE TABLE `plexweb_favorite_videos` (
  `id` int NOT NULL,
  `video_id` int NOT NULL,
  `Library` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `plexweb_favorite_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `video_id` (`video_id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `video_id_2` (`video_id`);
ALTER TABLE `plexweb_favorite_videos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

