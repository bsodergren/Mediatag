
CREATE TABLE `mediatag_keyword` (
  `id` int NOT NULL,
  `keyword` varchar(80) NOT NULL,
  `replacement` varchar(255) DEFAULT NULL,
  `keep` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `mediatag_keyword`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`);


ALTER TABLE `mediatag_keyword`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;