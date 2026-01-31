ALTER TABLE `listings` ADD FULLTEXT KEY `search_idx` (`title`, `description`, `city`);
