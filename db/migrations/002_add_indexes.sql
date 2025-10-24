-- Add indexes to lineup_songs for faster lookups
ALTER TABLE `lineup_songs` ADD INDEX `idx_lineup_id` (`lineup_id`);
ALTER TABLE `lineup_songs` ADD INDEX `idx_song_id` (`song_id`);

-- Add a unique constraint to prevent adding the same song to a lineup twice
-- This also creates an index
ALTER TABLE `lineup_songs` ADD UNIQUE `unique_lineup_song` (`lineup_id`, `song_id`);
