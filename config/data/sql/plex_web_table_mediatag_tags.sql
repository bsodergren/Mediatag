
CREATE TABLE `mediatag_tags` (
  `id` int NOT NULL,
  `file_id` int NOT NULL,
  `tag_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `mediatag_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_2` (`id`);
  

ALTER TABLE `mediatag_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;