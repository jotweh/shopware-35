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

