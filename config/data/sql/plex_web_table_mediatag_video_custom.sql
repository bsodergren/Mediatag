
CREATE TABLE `mediatag_video_custom` (
  `id` int NOT NULL,
  `video_key` varchar(200) NOT NULL,
  `filename` varchar(200) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `genre` longtext,
  `studio` varchar(200) DEFAULT NULL,
  `network` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `keyword` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `mediatag_video_custom`
  ADD PRIMARY KEY (`video_key`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `video_key` (`video_key`),
  ADD KEY `id_2` (`id`);


ALTER TABLE `mediatag_video_custom`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
