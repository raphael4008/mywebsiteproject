ALTER TABLE `listings` ADD `is_featured` BOOLEAN NOT NULL DEFAULT FALSE AFTER `furnished`;
UPDATE `listings` SET `is_featured` = 1 WHERE `id` IN (1, 2, 3, 4, 5, 6);
