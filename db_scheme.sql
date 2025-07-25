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

CREATE TABLE IF NOT EXISTS `note_location` (
  `note` INT NOT NULL PRIMARY KEY,
  `lat` DOUBLE NOT NULL,
  `lon` DOUBLE NOT NULL,
  `display_name` TEXT,
  `country` VARCHAR(100),
  `country_code` VARCHAR(2),
  `state` VARCHAR(100),                -- Bundesland
  `region` VARCHAR(100),               -- Regierungsbezirk (wenn vorhanden)
  `county` VARCHAR(100),               -- Landkreis
  `city` VARCHAR(100),                 -- Stadt
  `town` VARCHAR(100),                 -- manchmal statt city
  `village` VARCHAR(100),              -- manchmal statt city
  `suburb` VARCHAR(100),               -- Stadtteil
  `postcode` VARCHAR(20),
  `address_json` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
