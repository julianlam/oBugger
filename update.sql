-- v0.23a to latest

ALTER TABLE `bugs`  ADD `lastUpdated` INT(10) NOT NULL

ALTER TABLE `bugs` ADD `assignedTo` BIGINT NOT NULL DEFAULT '0' AFTER `priority` 
