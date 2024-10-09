
CREATE TABLE `plexweb_video_sequence` (
  `id` int NOT NULL,
  `seq_id` int NOT NULL,
  `video_id` int NOT NULL,
  `video_key` varchar(60) NOT NULL,
  `Library` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `plexweb_video_sequence`
  ADD KEY `plexweb_sequence_ibfk_1` (`video_key`);
  
  