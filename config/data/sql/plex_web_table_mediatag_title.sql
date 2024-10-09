
CREATE TABLE `mediatag_title` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `mediatag_title`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);


ALTER TABLE `mediatag_title`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
