
CREATE TABLE `mediatag_video_info` (
  `id` int NOT NULL,
  `video_key` varchar(60) DEFAULT NULL,
  `Library` varchar(20) DEFAULT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `bit_rate` int DEFAULT NULL,
  `format` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE `mediatag_video_info`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `video_key` (`video_key`);
ALTER TABLE `mediatag_video_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

