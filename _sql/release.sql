-- Release.sql for Shopware 3.5.4

/*
 * @ticket 4847
 * @author h.lohaus 
 * @since 3.5.4 - 2011/03/22
 */

UPDATE `s_core_config` SET `description` = 'Method to send mail: ("mail", "smtp" or "file").' WHERE `name`='sMAILER_Mailer';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Mailer');
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sMAILER_Auth', '', 'Sets connection auth. Options are "", "plain",  "login" or "crammd5"', 0, 0, '', 1, '');