
CREATE TABLE `mediatag_artists` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `replacement` varchar(50) NOT NULL,
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  `isFemale` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `mediatag_artists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `mediatag_artists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
