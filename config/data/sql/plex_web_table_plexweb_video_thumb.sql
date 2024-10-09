
CREATE TABLE `plexweb_video_thumb` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `thumbnail` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `plexweb_video_thumb`
  ADD PRIMARY KEY (`id`);
