-- Add index for display_order to speed up sorting
ALTER TABLE `lineup_songs` ADD INDEX `idx_display_order` (`display_order`);
