
BEGIN TRANSACTION;
ALTER TABLE `users` ADD `oauth_provider` VARCHAR(25);
ALTER TABLE `users` ADD `oauth_user_id` VARCHAR(50);
ALTER TABLE `users` ADD `oauth_user_email` VARCHAR(255);
ALTER TABLE `users` ADD `oauth_user_data` TEXT;
ALTER TABLE `users` ADD `oauth_access_token` TEXT;
ALTER TABLE `users` ADD `oauth_refresh_token` TEXT;
CREATE UNIQUE INDEX unique_oauth_provider_user_id ON `users` (`oauth_provider`, `oauth_user_id`);
COMMIT;
