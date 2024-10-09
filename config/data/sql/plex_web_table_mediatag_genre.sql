
CREATE TABLE `mediatag_genre` (
  `id` int NOT NULL,
  `genre` varchar(80) NOT NULL,
  `replacement` varchar(255) DEFAULT NULL,
  `keep` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE `mediatag_genre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `genre` (`genre`);


ALTER TABLE `mediatag_genre`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;