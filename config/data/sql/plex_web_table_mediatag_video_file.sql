
CREATE TABLE `mediatag_video_file` (
  `id` int NOT NULL,
  `video_key` varchar(200) NOT NULL,
  `Library` varchar(20) DEFAULT NULL,
  `filename` varchar(200) DEFAULT NULL,
  `fullpath` varchar(500) DEFAULT NULL,
  `thumbnail` longtext,
  `preview` longtext,
  `duration` bigint NOT NULL DEFAULT '0',
  `filesize` bigint NOT NULL DEFAULT '0',
  `favorite` varchar(15) NOT NULL DEFAULT '0',
  `rating` varchar(3) NOT NULL DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `added` timestamp NULL DEFAULT NULL,
  `new` int NOT NULL DEFAULT '0',
  `subLibrary` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



ALTER TABLE `mediatag_video_file`
  ADD PRIMARY KEY (`video_key`) USING BTREE,
  ADD UNIQUE KEY `id` (`id`);
  
  
ALTER TABLE `mediatag_video_file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

