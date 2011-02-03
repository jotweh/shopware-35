# 351 Changes
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

# Crontab Changes
UPDATE `s_crontab` SET `interval` = 86400 WHERE `interval` IN (1, 10, 100);

# Snippet Changes
UPDATE `s_core_snippets` SET `value` = 'Ich habe die <a href="{url controller=custom sCustom=4 forceSecure}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.' WHERE `name` = 'ConfirmTerms';
UPDATE `s_core_snippets` SET `value` = 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnungsadressen zugreifen.' WHERE `name` = 'SelectBillingInfoEmpty';

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/box_article', 1, 1, 'Star', '*', '2010-12-08 02:51:26', '2010-12-08 02:51:26'),
('frontend/listing/box_article', 1, 1, 'reducedPrice', 'Statt: ', '2010-12-08 02:52:32', '2010-12-08 02:52:32');

UPDATE `s_core_snippets` SET `value` = 'Prüfen und Bestellen' WHERE `value` = 'Bestellung abschließen';

# Seo Changes
UPDATE `s_core_config` SET `value` = CONCAT(`value`, ',search,account,checkout,register') WHERE `name` = 'sSEOVIEWPORTBLACKLIST' AND `value` NOT LIKE '%checkout%';

# PayPal Changes
UPDATE `s_core_config` SET `multilanguage` = '1' WHERE `name` IN ('sXPRESS', 'sPaypalLogo');

# Max. Suppliers Config
SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Kategorien / Listen');
INSERT IGNORE INTO `s_core_config` (`group`,`name`,`value`,`description`)
VALUES (@parent, 'sMAXSUPPLIERSCATEGORY', '30', 'Max. Anzahl Hersteller in Sidebar');

# Plugin Changes
CREATE TABLE IF NOT EXISTS `s_core_plugin_configs_copy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `pluginID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`pluginID`,`localeID`,`shopID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `s_core_plugin_configs_copy` (`name`, `value`, `pluginID`, `localeID`, `shopID`)
SELECT `name`, `value`, `pluginID`, `localeID`, `shopID`
FROM `s_core_plugin_configs`
ORDER BY `pluginID`, `shopID`, `name`, `id` DESC;

DROP TABLE IF EXISTS `s_core_plugin_configs`;
RENAME TABLE `s_core_plugin_configs_copy` TO `s_core_plugin_configs`;

# Filter Changes
CREATE TABLE IF NOT EXISTS `s_filter_values_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `optionID` (`optionID`,`articleID`,`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `s_filter_values_copy` (`id`, `groupID`, `optionID`, `articleID`, `value`)
SELECT `id`, `groupID`, `optionID`, `articleID`, `value`
FROM `s_filter_values`;

DROP TABLE IF EXISTS `s_filter_values`;
RENAME TABLE `s_filter_values_copy` TO `s_filter_values`;

# Table Changes

ALTER TABLE `s_crontab` ADD `pluginID` INT( 11 ) UNSIGNED NULL;