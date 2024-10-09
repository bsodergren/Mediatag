
CREATE TABLE `plexweb_video_chapter` (
  `id` int NOT NULL COMMENT 'Primary Key',
  `video_id` int NOT NULL,
  `timeCode` decimal(10,0) NOT NULL,
  `markerText` varchar(255) DEFAULT NULL,
  `markerThumbnail` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `plexweb_video_chapter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `timeCode` (`timeCode`);

ALTER TABLE `plexweb_video_chapter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
