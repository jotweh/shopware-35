# Changes to Crontab Table
ALTER TABLE `s_crontab` ADD `pluginID` INT NOT NULL;
DELETE FROM `s_crontab` WHERE `action` IN ('clearing','translation','search');
UPDATE `s_crontab` SET `interval` = 86400 WHERE `interval` IN (1,10,100);

# Changes to Plugin Tables
ALTER TABLE `s_core_plugin_configs` ADD UNIQUE (
`name` ,
`pluginID` ,
`localeID` ,
`shopID`
);

# Snippet Changes
UPDATE `s_core_snippets` SET `value` = 'Ich habe die <a href="{url controller=custom sCustom=4 forceSecure}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.' WHERE `s_core_snippets`.`name` = 'ConfirmTerms';
