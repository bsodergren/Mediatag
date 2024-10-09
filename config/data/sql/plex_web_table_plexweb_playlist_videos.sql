
CREATE TABLE `plexweb_playlist_videos` (
  `id` int NOT NULL,
  `playlist_id` int NOT NULL,
  `playlist_video_id` int NOT NULL,
  `Library` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `plexweb_playlist_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `playlist_video_id` (`playlist_video_id`),
  ADD KEY `playlist_id` (`playlist_id`);
  
ALTER TABLE `plexweb_playlist_videos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;