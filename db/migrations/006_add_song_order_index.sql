-- Add index for song_order to speed up sorting
ALTER TABLE `lineup_songs` ADD INDEX `idx_song_order` (`song_order`);
