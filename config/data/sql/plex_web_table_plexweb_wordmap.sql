
CREATE TABLE `plexweb_wordmap` (
  `id` int NOT NULL,
  `find` varchar(80) NOT NULL,
  `replace` varchar(255) DEFAULT NULL,
  `regex` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
