-- Add display_order column to lineup_songs table
ALTER TABLE `lineup_songs` ADD COLUMN `display_order` INT NOT NULL DEFAULT 0 AFTER `song_id`;
