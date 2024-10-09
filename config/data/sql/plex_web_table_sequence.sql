
CREATE TABLE `sequence` (
  `name` varchar(100) NOT NULL,
  `increment` int NOT NULL DEFAULT '1',
  `min_value` int NOT NULL DEFAULT '1',
  `max_value` bigint NOT NULL DEFAULT '9223372036854775807',
  `cur_value` bigint DEFAULT '1',
  `cycle` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `sequence`
  ADD PRIMARY KEY (`name`);