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
