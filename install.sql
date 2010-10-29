UPDATE `s_core_config` SET `value` = '5' WHERE `name` = 'sCHARTRANGE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '100' WHERE `name` = 'sMAXPURCHASE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '0' WHERE `name` = 'sLASTARTICLESTHUMB' LIMIT 1 ;

TRUNCATE `s_emarketing_lastarticles`;
TRUNCATE `s_statistics_currentusers`;
TRUNCATE `s_statistics_pool`;
TRUNCATE `s_statistics_referer`;
TRUNCATE `s_statistics_search`;
TRUNCATE `s_statistics_visitors`;

------------------------------

DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sSERVICE%';
DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sCHEAPER';

INSERT IGNORE INTO `s_cms_support` (`id`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `ticket_typeID`, `isocode`) VALUES
(10, 'Rückgabe', '<h2>Hier k&ouml;nnen Sie Informationen zur R&uuml;ckgabe einstellen...</h2>', 'rueckgabe@shopware2.de', 'INSERT INTO s_user_service\r\n(clientnumber, email, billingnumber, articles, description, description2, description3,\r\ndescription4,date,type)\r\nVALUES (\r\n			''{$kdnr}'',\r\n			''{$email}'',\r\n			''{$rechnung}'',\r\n			''{$artikel}'',\r\n			''{$info}'',\r\n			'''',\r\n			'''',\r\n			'''',\r\n			''{$date}'',\r\n2\r\n		)\r\n\r\n', 'Rückgabe', '<p>Formular erfolgreich versandt.</p>', 0, 'de');

INSERT IGNORE INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `vtyp`, `added`, `position`, `ticket_task`) VALUES
(60, '', 'kdnr', '', 'text', 1, 10, 'KdNr.(siehe Rechnung)', 'normal', '', '', '2007-11-06 17:31:38', 1, ''),
(61, '', 'email', '', 'text', 1, 10, 'eMail-Adresse', 'normal', '', '', '2007-11-06 17:31:51', 2, ''),
(62, '', 'rechnung', '', 'text', 1, 10, 'Rechnungsnummer', 'normal', '', '', '2007-11-06 17:32:02', 3, ''),
(63, '', 'artikel', '', 'textarea', 1, 10, 'Artikelnummer(n)', 'normal', '', '', '2007-11-06 17:32:17', 4, ''),
(64, '', 'info', '', 'textarea', 0, 10, 'Kommentar', 'normal', '', '', '2007-11-06 17:32:42', 5, '');

UPDATE `s_core_snippets` SET `value` = '{link file=''frontend/_resources/favicon.ico''}' WHERE `value` = '{link file=''resources/favicon.ico''}';
DELETE FROM `s_core_snippets` WHERE `namespace` LIKE 'templates/_default/%';
DELETE FROM `s_core_config_groups` WHERE `name` = 'Debugging';