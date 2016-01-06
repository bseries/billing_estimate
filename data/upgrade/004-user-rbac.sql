ALTER TABLE `billing_estimates` ADD `owner_id` INT(11)  UNSIGNED  NOT NULL  AFTER `id`;
UPDATE `billing_estimates` SET `owner_id` = 1;
