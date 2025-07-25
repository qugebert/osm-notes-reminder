CREATE TABLE IF NOT EXISTS `reminder_bot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note` int NOT NULL,
  `comment` int NOT NULL,
  `date` date NOT NULL,
  `user` int NOT NULL,
  `done` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `user_config` (
  `user_id` int NOT NULL,
  `user_name` varchar(500) NOT NULL,
  `custom_message` text,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;
