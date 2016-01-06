-- After running migration command.
-- ALTER TABLE `billing_estimates` DROP `virtual_user_id`;
-- ALTER TABLE `billing_estimates` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NOT NULL;
-- ALTER TABLE `billing_estimate_positions` DROP `virtual_user_id`;
-- ALTER TABLE `billing_estimate_positions` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NOT NULL;

