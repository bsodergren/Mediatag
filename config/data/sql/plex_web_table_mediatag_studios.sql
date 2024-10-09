
CREATE TABLE `mediatag_studios` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `Library` varchar(50) NOT NULL,
  `studio` varchar(60) DEFAULT NULL,
  `path` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE `mediatag_studios`
  ADD UNIQUE KEY `id_3` (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `id` (`id`),
  ADD KEY `id_2` (`id`);


ALTER TABLE `mediatag_studios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
