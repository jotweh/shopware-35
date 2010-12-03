#352 changes
DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sSERVICE%';
DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sCHEAPER';

INSERT IGNORE INTO `s_cms_support` (`id`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `ticket_typeID`, `isocode`) VALUES
(10, 'Rückgabe', '<h2>Hier k&ouml;nnen Sie Informationen zur R&uuml;ckgabe einstellen...</h2>', 'info@example.de', 'Rückgabe - Shopware Demoshop\r\n \r\nKundennummer: {sVars.kdnr}\r\neMail: {sVars.email}\r\n \r\nRechnungsnummer: {sVars.rechnung}\r\nArtikelnummer: {sVars.artikel}\r\n \r\nKommentar:\r\n \r\n{sVars.info}', 'Rückgabe', '<p>Formular erfolgreich versandt.</p>', 0, 'de');

INSERT IGNORE INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `vtyp`, `added`, `position`, `ticket_task`) VALUES
(60, '', 'kdnr', '', 'text', 1, 10, 'KdNr.(siehe Rechnung)', 'normal', '', '', '2007-11-06 17:31:38', 1, ''),
(61, '', 'email', '', 'text', 1, 10, 'eMail-Adresse', 'normal', '', '', '2007-11-06 17:31:51', 2, ''),
(62, '', 'rechnung', '', 'text', 1, 10, 'Rechnungsnummer', 'normal', '', '', '2007-11-06 17:32:02', 3, ''),
(63, '', 'artikel', '', 'textarea', 1, 10, 'Artikelnummer(n)', 'normal', '', '', '2007-11-06 17:32:17', 4, ''),
(64, '', 'info', '', 'textarea', 0, 10, 'Kommentar', 'normal', '', '', '2007-11-06 17:32:42', 5, '');

UPDATE `s_core_snippets` SET `value` = '{link file=''frontend/_resources/favicon.ico''}' WHERE `value` = '{link file=''resources/favicon.ico''}';
DELETE FROM `s_core_snippets` WHERE `namespace` LIKE 'templates/_default/%';
DELETE FROM `s_core_config_groups` WHERE `name` = 'Debugging';


# Changes to Crontab Table
ALTER TABLE `s_crontab` ADD `pluginID` INT NOT NULL;
-- DELETE FROM `s_crontab` WHERE `action` IN ('clearing','translation','search');
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

ALTER TABLE `s_filter_values` DROP INDEX `optionID`;
ALTER TABLE `s_filter_values` DROP INDEX `optionID_2`;
ALTER TABLE `s_filter_values` DROP INDEX `groupID` ;
ALTER TABLE `s_filter_values` ADD INDEX ( `groupID` );
ALTER TABLE `s_filter_values` ADD INDEX ( `optionID` , `articleID` , `value` ) ;

UPDATE `s_core_snippets` SET `value` = 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnungsadressen zugreifen.' WHERE `name` = 'SelectBillingInfoEmpty';

DELETE FROM `s_core_config_groups` WHERE `name` = `Debugging`;

# Seo Changes
UPDATE `s_core_config` SET `value` = CONCAT(`value`, ',search,account,checkout,register') WHERE `name` = 'sSEOVIEWPORTBLACKLIST' AND `value` NOT LIKE '%checkout%';

# PayPal Changes
UPDATE `s_core_config` SET `multilanguage` = '1' WHERE `name` IN ('sXPRESS', 'sPaypalLogo');