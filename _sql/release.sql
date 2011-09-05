/**
 * Insert sql queries for shopware 3.5.5
 */

/**
 * @ticket 5716 (internal)
 * @ticket 100485 (external)
 * @author s.pohl
 * @since 3.5.5 - 2011/07/27
 */
UPDATE `s_core_snippets` SET `value` = 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet. Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestätigen.' WHERE `s_core_snippets`.`localeID` = 1 AND `s_core_snippets`.`name` LIKE 'sMailConfirmation';

/*
 * @ticket 5780 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/02
 */
ALTER TABLE `s_core_translations` CHANGE `objectkey` `objectkey` INT( 11 ) UNSIGNED NOT NULL;

/*
 * No Ticket - Update version info
 * @author st.hamann
 * @since 3.5.5 - 2011/08/08
 */
UPDATE `s_core_config` SET `value` = '3.5.5' WHERE `name` = 'sVERSION';
UPDATE `s_core_config` SET `value` = '6464' WHERE `name` = 'sREVISION';

/*
 * @ticket 5867 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/12
 */
ALTER TABLE `s_emarketing_lastarticles` ADD `shopID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_emarketing_lastarticles`
	CHANGE `articleID` `articleID` INT( 11 ) UNSIGNED NOT NULL,
	CHANGE `userID` `userID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_emarketing_lastarticles` DROP INDEX sessionID;
ALTER TABLE `s_emarketing_lastarticles` DROP INDEX articleID;
ALTER TABLE `s_emarketing_lastarticles` ADD UNIQUE (
	`articleID`,
	`sessionID`,
	`shopID`
);

/*
 * @ticket 5867 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/16
 */
DROP INDEX `changetime` ON `s_articles`;
ALTER TABLE `s_articles` ADD INDEX ( `changetime` );

/*
 * @ticket 5857 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/30
 */
INSERT IGNORE INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
('Backend', 'Check', 'Systeminfo', 'Default', '', '', 1, '2010-10-18 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');
SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `label` = 'Systeminfo');
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Dispatcher_ControllerPath_Backend_Check', 0, 'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend', @parent, 0);
UPDATE `s_core_menu` SET `onclick` = 'openAction(\'check\');', `pluginID` = @parent WHERE `name` = 'Systeminfo';

/*
 * @ticket 5418 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/09/05
 */
SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'USt-IdNr. Überprüfung');
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sVATCHECKCONFIRMATION', '0', 'Amtliche Bestätigungsmitteilung bei der erweiterten Überprüfung anfordern', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKVALIDRESPONSE', 'A, D', 'Gültige Ergebnisse bei der erweiterten Überprüfung', 0, 0, '', 0, '');
