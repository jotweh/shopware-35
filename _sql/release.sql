/**
 * Insert sql queries for shopware 3.5.5
 * @author st.hamann
 * @since 3.5.4 - 2011/06/06
 */


/**
 * @ticket #5716 (intern)
 * @ticket #100485 (extern)
 * @author s.pohl
 * @date 2011-07-27
 */
UPDATE `s_core_snippets` SET `value` = 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet. Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestätigen.' WHERE `s_core_snippets`.`localeID` = 1 AND `s_core_snippets`.`name` LIKE 'sMailConfirmation';
