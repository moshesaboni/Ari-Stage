-- Initial Schema for Lineup Application

CREATE TABLE IF NOT EXISTS `lineups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `songs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `artist` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lineup_songs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lineup_id` INT NOT NULL,
    `song_id` INT NOT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lineup_id`) REFERENCES `lineups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`song_id`) REFERENCES `songs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `lineup_song_unique` (`lineup_id`, `song_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

