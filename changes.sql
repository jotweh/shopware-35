DROP TABLE `sessions2`;
DROP TABLE `adodb_logsql`;

CREATE TABLE IF NOT EXISTS `s_core_sessions` (
  `id` varchar(64) NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `expireref` varchar(255) default NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `data` longtext,
  PRIMARY KEY  (`id`),
  KEY `expiry` (`expiry`),
  KEY `expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

UPDATE `s_cms_support_fields`
SET `value` = ''
WHERE `supportID`=(SELECT `id` FROM `s_cms_support` WHERE `name` = 'Rückgabe')
AND `value` IN (1,2,3,4,5,6);

CREATE TABLE IF NOT EXISTS `s_core_snippets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`name`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

ALTER TABLE `s_core_multilanguage` ADD `locale` VARCHAR( 255 ) NOT NULL AFTER `isocode`;

ALTER TABLE `s_categories` ADD `noviewselect` INT( 1 ) UNSIGNED NOT NULL AFTER `template`;

ALTER TABLE `s_statistics_referer` CHANGE `referer` `referer` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `s_order` CHANGE `referer` `referer` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `s_core_translations` CHANGE objectdata objectdata LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL; 


UPDATE s_core_menu SET onclick = '',style=''  WHERE name = 'Textbausteine';
SET @parent = (SELECT id FROM s_core_menu WHERE name = 'Textbausteine');
INSERT INTO s_core_menu (`parent`,name,onclick,style,class,position,active)
VALUES (@parent,'Neue Templatebasis','openAction(\'snippet\')','background-position: 5px 5px','ico2 plugin','0',1);
INSERT INTO s_core_menu (`parent`,name,onclick,style,class,position,active)
VALUES (@parent,'Alte Templatebasis','loadSkeleton(\'snippets\')','background-position: 5px 5px','ico2 plugin','1',1);

DELETE FROM s_core_menu WHERE name = 'FAQ';
UPDATE s_core_menu SET name = 'Zur Community',onclick='window.open(\'http://www.shopware.de/wiki\',\'Shopware\',\'width=800,height=550,scrollbars=yes\')' WHERE name = 'Wiki';


ALTER TABLE `s_core_config_groups` ADD `action` VARCHAR( 255 ) NOT NULL ;
UPDATE s_core_config_groups SET `file` = '' WHERE `file`='documents.php';
SET @parent = (SELECT id FROM s_core_config_groups WHERE `name` = 'PDF-Belegerstellung');
INSERT INTO `s_core_config_groups` (`name`,`position`,`parent`,`file`)
VALUES (
'Alte Templatebasis',2,@parent,'documents.php'
);
INSERT INTO `s_core_config_groups` (`name`,`position`,`parent`,`action`)
VALUES (
'Neue Templatebasis',1,@parent,'backend/document/settings'
);
UPDATE s_core_menu SET onclick='openAction(\'cache\');' WHERE onclick = 'deleteCache();' LIMIT 1;

SET @parent = (SELECT id FROM s_core_menu WHERE `name` = 'Shopcache leeren');

INSERT INTO  `s_core_menu` (
`id` ,
`parent` ,
`hyperlink` ,
`name` ,
`onclick` ,
`style` ,
`class` ,
`position` ,
`active`)
VALUES (
NULL ,  @parent,  '',  'Artikel + Kategorien',  'deleteCache(''articles'');',  'background-position: 5px 5px;',  'ico2 bin',  '1',  '1'
);

INSERT INTO  `s_core_menu` (
`id` ,
`parent` ,
`hyperlink` ,
`name` ,
`onclick` ,
`style` ,
`class` ,
`position` ,
`active` 
)
VALUES (
NULL ,  @parent,  '',  'Konfiguration',  'deleteCache(''config'');',  'background-position: 5px 5px;',  'ico2 bin',  '1',  '1'
);

INSERT INTO  `s_core_menu` (
`id` ,
`parent` ,
`hyperlink` ,
`name` ,
`onclick` ,
`style` ,
`class` ,
`position` ,
`active`
)
VALUES (
NULL ,  @parent,  '',  'Textbausteine',  'deleteCache(''snippets'');',  'background-position: 5px 5px;',  'ico2 bin',  '1',  '1'
);




CREATE TABLE IF NOT EXISTS `s_core_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `numbers` varchar(25) NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `top` int(11) NOT NULL,
  `bottom` int(11) NOT NULL,
  `pagebreak` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;


INSERT INTO `s_core_documents` (`id`, `name`, `template`, `numbers`, `left`, `right`, `top`, `bottom`, `pagebreak`) VALUES
(1, 'Rechnung', 'index.tpl', 'doc_0', 25, 10, 20, 20, 10),
(2, 'Lieferschein', 'index_ls.tpl', 'doc_1', 25, 10, 20, 20, 10),
(3, 'Gutschrift', 'index_gs.tpl', 'doc_2', 25, 10, 20, 20, 10),
(4, 'Stornorechnung', 'index_sr.tpl', 'doc_3', 25, 10, 20, 20, 10);


CREATE TABLE IF NOT EXISTS `s_core_documents_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) NOT NULL,
  `name` varchar(35) NOT NULL,
  `style` longtext NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;


INSERT INTO `s_core_documents_box` (`id`, `documentID`, `name`, `style`, `value`) VALUES
(1, 1, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(2, 1, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://debiananwenderhandbuch.de/bilder/ubuntu/UbuntuLogo.png" alt="" width="341" height="88" /></p>'),
(3, 1, 'Header_Recipient', '', ''),
(4, 1, 'Header', 'height: 60mm;', ''),
(5, 1, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(6, 1, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(7, 1, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(8, 1, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(9, 1, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(10, 1, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(11, 1, 'Td_Name', 'white-space:normal;', ''),
(12, 1, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(13, 1, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(14, 1, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(15, 1, 'Content_Amount', 'margin-left:90mm;', ''),
(16, 1, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(17, 2, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(18, 2, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://debiananwenderhandbuch.de/bilder/ubuntu/UbuntuLogo.png" alt="" width="341" height="88" /></p>'),
(19, 2, 'Header_Recipient', '', ''),
(20, 2, 'Header', 'height: 60mm;', ''),
(21, 2, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(22, 2, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(23, 2, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(24, 2, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(25, 2, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(26, 2, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(27, 2, 'Td_Name', 'white-space:normal;', ''),
(28, 2, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(29, 2, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(30, 2, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(31, 2, 'Content_Amount', 'margin-left:98mm;', ''),
(32, 2, 'Content_Info', '', ''),
(33, 3, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', '<p>test</p>'),
(34, 3, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://debiananwenderhandbuch.de/bilder/ubuntu/UbuntuLogo.png" alt="" width="341" height="88" /></p>'),
(35, 3, 'Header_Recipient', '', ''),
(36, 3, 'Header', 'height: 60mm;', ''),
(37, 3, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(38, 3, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(39, 3, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(40, 3, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(41, 3, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(42, 3, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(43, 3, 'Td_Name', 'white-space:normal;', ''),
(44, 3, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(45, 3, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(46, 3, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(47, 3, 'Content_Amount', 'margin-left:98mm;', ''),
(48, 3, 'Content_Info', '', ''),
(49, 4, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', '<p>test</p>'),
(50, 4, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://debiananwenderhandbuch.de/bilder/ubuntu/UbuntuLogo.png" alt="" width="341" height="88" /></p>'),
(51, 4, 'Header_Recipient', '', ''),
(52, 4, 'Header', 'height: 60mm;', ''),
(53, 4, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(54, 4, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(55, 4, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(56, 4, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(57, 4, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(58, 4, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(59, 4, 'Td_Name', 'white-space:normal;', ''),
(60, 4, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(61, 4, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(62, 4, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(63, 4, 'Content_Amount', 'margin-left:98mm;', ''),
(64, 4, 'Content_Info', '', '');

ALTER TABLE `s_order_documents` ADD `hash` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `s_order_details` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `s_core_snippets` ADD `shopID` INT( 11 ) UNSIGNED NOT NULL AFTER `namespace`;
ALTER TABLE `s_core_snippets` DROP INDEX `namespace` ;
ALTER TABLE `s_core_snippets` ADD UNIQUE (
	`namespace` ,
	`shopID` ,
	`name` ,
	`locale`
);

--
-- Tabellenstruktur für Tabelle `s_core_locales`
--

DROP TABLE IF EXISTS `s_core_locales`;
CREATE TABLE `s_core_locales` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `territory` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=256 ;

--
-- Daten für Tabelle `s_core_locales`
--

INSERT INTO `s_core_locales` (`id`, `locale`, `language`, `territory`) VALUES
(1, 'de_DE', 'Deutsch', 'Deutschland'),
(2, 'en_GB', 'Englisch', 'Vereinigtes Königreich'),
(3, 'aa_DJ', 'Afar', 'Dschibuti'),
(4, 'aa_ER', 'Afar', 'Eritrea'),
(5, 'aa_ET', 'Afar', 'Äthiopien'),
(6, 'af_NA', 'Afrikaans', 'Namibia'),
(7, 'af_ZA', 'Afrikaans', 'Südafrika'),
(8, 'ak_GH', 'Akan', 'Ghana'),
(9, 'am_ET', 'Amharisch', 'Äthiopien'),
(10, 'ar_AE', 'Arabisch', 'Vereinigte Arabische Emirate'),
(11, 'ar_BH', 'Arabisch', 'Bahrain'),
(12, 'ar_DZ', 'Arabisch', 'Algerien'),
(13, 'ar_EG', 'Arabisch', 'Ägypten'),
(14, 'ar_IQ', 'Arabisch', 'Irak'),
(15, 'ar_JO', 'Arabisch', 'Jordanien'),
(16, 'ar_KW', 'Arabisch', 'Kuwait'),
(17, 'ar_LB', 'Arabisch', 'Libanon'),
(18, 'ar_LY', 'Arabisch', 'Libyen'),
(19, 'ar_MA', 'Arabisch', 'Marokko'),
(20, 'ar_OM', 'Arabisch', 'Oman'),
(21, 'ar_QA', 'Arabisch', 'Katar'),
(22, 'ar_SA', 'Arabisch', 'Saudi-Arabien'),
(23, 'ar_SD', 'Arabisch', 'Sudan'),
(24, 'ar_SY', 'Arabisch', 'Syrien'),
(25, 'ar_TN', 'Arabisch', 'Tunesien'),
(26, 'ar_YE', 'Arabisch', 'Jemen'),
(27, 'as_IN', 'Assamesisch', 'Indien'),
(28, 'az_AZ', 'Aserbaidschanisch', 'Aserbaidschan'),
(29, 'be_BY', 'Weißrussisch', 'Belarus'),
(30, 'bg_BG', 'Bulgarisch', 'Bulgarien'),
(31, 'bn_BD', 'Bengalisch', 'Bangladesch'),
(32, 'bn_IN', 'Bengalisch', 'Indien'),
(33, 'bo_CN', 'Tibetisch', 'China'),
(34, 'bo_IN', 'Tibetisch', 'Indien'),
(35, 'bs_BA', 'Bosnisch', 'Bosnien und Herzegowina'),
(36, 'byn_ER', 'Blin', 'Eritrea'),
(37, 'ca_ES', 'Katalanisch', 'Spanien'),
(38, 'cch_NG', 'Atsam', 'Nigeria'),
(39, 'cs_CZ', 'Tschechisch', 'Tschechische Republik'),
(40, 'cy_GB', 'Walisisch', 'Vereinigtes Königreich'),
(41, 'da_DK', 'Dänisch', 'Dänemark'),
(42, 'de_AT', 'Deutsch', 'Österreich'),
(43, 'de_BE', 'Deutsch', 'Belgien'),
(44, 'de_CH', 'Deutsch', 'Schweiz'),
(45, 'de_LI', 'Deutsch', 'Liechtenstein'),
(46, 'de_LU', 'Deutsch', 'Luxemburg'),
(47, 'dv_MV', 'Maledivisch', 'Malediven'),
(48, 'dz_BT', 'Bhutanisch', 'Bhutan'),
(49, 'ee_GH', 'Ewe-Sprache', 'Ghana'),
(50, 'ee_TG', 'Ewe-Sprache', 'Togo'),
(51, 'el_CY', 'Griechisch', 'Zypern'),
(52, 'el_GR', 'Griechisch', 'Griechenland'),
(53, 'en_AS', 'Englisch', 'Amerikanisch-Samoa'),
(54, 'en_AU', 'Englisch', 'Australien'),
(55, 'en_BE', 'Englisch', 'Belgien'),
(56, 'en_BW', 'Englisch', 'Botsuana'),
(57, 'en_BZ', 'Englisch', 'Belize'),
(58, 'en_CA', 'Englisch', 'Kanada'),
(59, 'en_GU', 'Englisch', 'Guam'),
(60, 'en_HK', 'Englisch', 'Sonderverwaltungszone Hongkong'),
(61, 'en_IE', 'Englisch', 'Irland'),
(62, 'en_IN', 'Englisch', 'Indien'),
(63, 'en_JM', 'Englisch', 'Jamaika'),
(64, 'en_MH', 'Englisch', 'Marshallinseln'),
(65, 'en_MP', 'Englisch', 'Nördliche Marianen'),
(66, 'en_MT', 'Englisch', 'Malta'),
(67, 'en_NA', 'Englisch', 'Namibia'),
(68, 'en_NZ', 'Englisch', 'Neuseeland'),
(69, 'en_PH', 'Englisch', 'Philippinen'),
(70, 'en_PK', 'Englisch', 'Pakistan'),
(71, 'en_SG', 'Englisch', 'Singapur'),
(72, 'en_TT', 'Englisch', 'Trinidad und Tobago'),
(73, 'en_UM', 'Englisch', 'Amerikanisch-Ozeanien'),
(74, 'en_US', 'Englisch', 'Vereinigte Staaten'),
(75, 'en_VI', 'Englisch', 'Amerikanische Jungferninseln'),
(76, 'en_ZA', 'Englisch', 'Südafrika'),
(77, 'en_ZW', 'Englisch', 'Simbabwe'),
(78, 'es_AR', 'Spanisch', 'Argentinien'),
(79, 'es_BO', 'Spanisch', 'Bolivien'),
(80, 'es_CL', 'Spanisch', 'Chile'),
(81, 'es_CO', 'Spanisch', 'Kolumbien'),
(82, 'es_CR', 'Spanisch', 'Costa Rica'),
(83, 'es_DO', 'Spanisch', 'Dominikanische Republik'),
(84, 'es_EC', 'Spanisch', 'Ecuador'),
(85, 'es_ES', 'Spanisch', 'Spanien'),
(86, 'es_GT', 'Spanisch', 'Guatemala'),
(87, 'es_HN', 'Spanisch', 'Honduras'),
(88, 'es_MX', 'Spanisch', 'Mexiko'),
(89, 'es_NI', 'Spanisch', 'Nicaragua'),
(90, 'es_PA', 'Spanisch', 'Panama'),
(91, 'es_PE', 'Spanisch', 'Peru'),
(92, 'es_PR', 'Spanisch', 'Puerto Rico'),
(93, 'es_PY', 'Spanisch', 'Paraguay'),
(94, 'es_SV', 'Spanisch', 'El Salvador'),
(95, 'es_US', 'Spanisch', 'Vereinigte Staaten'),
(96, 'es_UY', 'Spanisch', 'Uruguay'),
(97, 'es_VE', 'Spanisch', 'Venezuela'),
(98, 'et_EE', 'Estnisch', 'Estland'),
(99, 'eu_ES', 'Baskisch', 'Spanien'),
(100, 'fa_AF', 'Persisch', 'Afghanistan'),
(101, 'fa_IR', 'Persisch', 'Iran'),
(102, 'fi_FI', 'Finnisch', 'Finnland'),
(103, 'fil_PH', 'Filipino', 'Philippinen'),
(104, 'fo_FO', 'Färöisch', 'Färöer'),
(105, 'fr_BE', 'Französisch', 'Belgien'),
(106, 'fr_CA', 'Französisch', 'Kanada'),
(107, 'fr_CH', 'Französisch', 'Schweiz'),
(108, 'fr_FR', 'Französisch', 'Frankreich'),
(109, 'fr_LU', 'Französisch', 'Luxemburg'),
(110, 'fr_MC', 'Französisch', 'Monaco'),
(111, 'fr_SN', 'Französisch', 'Senegal'),
(112, 'fur_IT', 'Friulisch', 'Italien'),
(113, 'ga_IE', 'Irisch', 'Irland'),
(114, 'gaa_GH', 'Ga-Sprache', 'Ghana'),
(115, 'gez_ER', 'Geez', 'Eritrea'),
(116, 'gez_ET', 'Geez', 'Äthiopien'),
(117, 'gl_ES', 'Galizisch', 'Spanien'),
(118, 'gsw_CH', 'Schweizerdeutsch', 'Schweiz'),
(119, 'gu_IN', 'Gujarati', 'Indien'),
(120, 'gv_GB', 'Manx', 'Vereinigtes Königreich'),
(121, 'ha_GH', 'Hausa', 'Ghana'),
(122, 'ha_NE', 'Hausa', 'Niger'),
(123, 'ha_NG', 'Hausa', 'Nigeria'),
(124, 'ha_SD', 'Hausa', 'Sudan'),
(125, 'haw_US', 'Hawaiisch', 'Vereinigte Staaten'),
(126, 'he_IL', 'Hebräisch', 'Israel'),
(127, 'hi_IN', 'Hindi', 'Indien'),
(128, 'hr_HR', 'Kroatisch', 'Kroatien'),
(129, 'hu_HU', 'Ungarisch', 'Ungarn'),
(130, 'hy_AM', 'Armenisch', 'Armenien'),
(131, 'id_ID', 'Indonesisch', 'Indonesien'),
(132, 'ig_NG', 'Igbo-Sprache', 'Nigeria'),
(133, 'ii_CN', 'Sichuan Yi', 'China'),
(134, 'is_IS', 'Isländisch', 'Island'),
(135, 'it_CH', 'Italienisch', 'Schweiz'),
(136, 'it_IT', 'Italienisch', 'Italien'),
(137, 'ja_JP', 'Japanisch', 'Japan'),
(138, 'ka_GE', 'Georgisch', 'Georgien'),
(139, 'kaj_NG', 'Jju', 'Nigeria'),
(140, 'kam_KE', 'Kamba', 'Kenia'),
(141, 'kcg_NG', 'Tyap', 'Nigeria'),
(142, 'kfo_CI', 'Koro', 'Côte d?Ivoire'),
(143, 'kk_KZ', 'Kasachisch', 'Kasachstan'),
(144, 'kl_GL', 'Grönländisch', 'Grönland'),
(145, 'km_KH', 'Kambodschanisch', 'Kambodscha'),
(146, 'kn_IN', 'Kannada', 'Indien'),
(147, 'ko_KR', 'Koreanisch', 'Republik Korea'),
(148, 'kok_IN', 'Konkani', 'Indien'),
(149, 'kpe_GN', 'Kpelle-Sprache', 'Guinea'),
(150, 'kpe_LR', 'Kpelle-Sprache', 'Liberia'),
(151, 'ku_IQ', 'Kurdisch', 'Irak'),
(152, 'ku_IR', 'Kurdisch', 'Iran'),
(153, 'ku_SY', 'Kurdisch', 'Syrien'),
(154, 'ku_TR', 'Kurdisch', 'Türkei'),
(155, 'kw_GB', 'Kornisch', 'Vereinigtes Königreich'),
(156, 'ky_KG', 'Kirgisisch', 'Kirgisistan'),
(157, 'ln_CD', 'Lingala', 'Demokratische Republik Kongo'),
(158, 'ln_CG', 'Lingala', 'Kongo'),
(159, 'lo_LA', 'Laotisch', 'Laos'),
(160, 'lt_LT', 'Litauisch', 'Litauen'),
(161, 'lv_LV', 'Lettisch', 'Lettland'),
(162, 'mk_MK', 'Mazedonisch', 'Mazedonien'),
(163, 'ml_IN', 'Malayalam', 'Indien'),
(164, 'mn_CN', 'Mongolisch', 'China'),
(165, 'mn_MN', 'Mongolisch', 'Mongolei'),
(166, 'mr_IN', 'Marathi', 'Indien'),
(167, 'ms_BN', 'Malaiisch', 'Brunei Darussalam'),
(168, 'ms_MY', 'Malaiisch', 'Malaysia'),
(169, 'mt_MT', 'Maltesisch', 'Malta'),
(170, 'my_MM', 'Birmanisch', 'Myanmar'),
(171, 'nb_NO', 'Norwegisch Bokmål', 'Norwegen'),
(172, 'nds_DE', 'Niederdeutsch', 'Deutschland'),
(173, 'ne_IN', 'Nepalesisch', 'Indien'),
(174, 'ne_NP', 'Nepalesisch', 'Nepal'),
(175, 'nl_BE', 'Niederländisch', 'Belgien'),
(176, 'nl_NL', 'Niederländisch', 'Niederlande'),
(177, 'nn_NO', 'Norwegisch Nynorsk', 'Norwegen'),
(178, 'nr_ZA', 'Süd-Ndebele-Sprache', 'Südafrika'),
(179, 'nso_ZA', 'Nord-Sotho-Sprache', 'Südafrika'),
(180, 'ny_MW', 'Nyanja-Sprache', 'Malawi'),
(181, 'oc_FR', 'Okzitanisch', 'Frankreich'),
(182, 'om_ET', 'Oromo', 'Äthiopien'),
(183, 'om_KE', 'Oromo', 'Kenia'),
(184, 'or_IN', 'Orija', 'Indien'),
(185, 'pa_IN', 'Pandschabisch', 'Indien'),
(186, 'pa_PK', 'Pandschabisch', 'Pakistan'),
(187, 'pl_PL', 'Polnisch', 'Polen'),
(188, 'ps_AF', 'Paschtu', 'Afghanistan'),
(189, 'pt_BR', 'Portugiesisch', 'Brasilien'),
(190, 'pt_PT', 'Portugiesisch', 'Portugal'),
(191, 'ro_MD', 'Rumänisch', 'Republik Moldau'),
(192, 'ro_RO', 'Rumänisch', 'Rumänien'),
(193, 'ru_RU', 'Russisch', 'Russische Föderation'),
(194, 'ru_UA', 'Russisch', 'Ukraine'),
(195, 'rw_RW', 'Ruandisch', 'Ruanda'),
(196, 'sa_IN', 'Sanskrit', 'Indien'),
(197, 'se_FI', 'Nord-Samisch', 'Finnland'),
(198, 'se_NO', 'Nord-Samisch', 'Norwegen'),
(199, 'sh_BA', 'Serbo-Kroatisch', 'Bosnien und Herzegowina'),
(200, 'sh_CS', 'Serbo-Kroatisch', 'Serbien und Montenegro'),
(201, 'sh_YU', 'Serbo-Kroatisch', ''),
(202, 'si_LK', 'Singhalesisch', 'Sri Lanka'),
(203, 'sid_ET', 'Sidamo', 'Äthiopien'),
(204, 'sk_SK', 'Slowakisch', 'Slowakei'),
(205, 'sl_SI', 'Slowenisch', 'Slowenien'),
(206, 'so_DJ', 'Somali', 'Dschibuti'),
(207, 'so_ET', 'Somali', 'Äthiopien'),
(208, 'so_KE', 'Somali', 'Kenia'),
(209, 'so_SO', 'Somali', 'Somalia'),
(210, 'sq_AL', 'Albanisch', 'Albanien'),
(211, 'sr_BA', 'Serbisch', 'Bosnien und Herzegowina'),
(212, 'sr_CS', 'Serbisch', 'Serbien und Montenegro'),
(213, 'sr_ME', 'Serbisch', 'Montenegro'),
(214, 'sr_RS', 'Serbisch', 'Serbien'),
(215, 'sr_YU', 'Serbisch', ''),
(216, 'ss_SZ', 'Swazi', 'Swasiland'),
(217, 'ss_ZA', 'Swazi', 'Südafrika'),
(218, 'st_LS', 'Süd-Sotho-Sprache', 'Lesotho'),
(219, 'st_ZA', 'Süd-Sotho-Sprache', 'Südafrika'),
(220, 'sv_FI', 'Schwedisch', 'Finnland'),
(221, 'sv_SE', 'Schwedisch', 'Schweden'),
(222, 'sw_KE', 'Suaheli', 'Kenia'),
(223, 'sw_TZ', 'Suaheli', 'Tansania'),
(224, 'syr_SY', 'Syrisch', 'Syrien'),
(225, 'ta_IN', 'Tamilisch', 'Indien'),
(226, 'te_IN', 'Telugu', 'Indien'),
(227, 'tg_TJ', 'Tadschikisch', 'Tadschikistan'),
(228, 'th_TH', 'Thailändisch', 'Thailand'),
(229, 'ti_ER', 'Tigrinja', 'Eritrea'),
(230, 'ti_ET', 'Tigrinja', 'Äthiopien'),
(231, 'tig_ER', 'Tigre', 'Eritrea'),
(232, 'tn_ZA', 'Tswana-Sprache', 'Südafrika'),
(233, 'to_TO', 'Tongaisch', 'Tonga'),
(234, 'tr_TR', 'Türkisch', 'Türkei'),
(236, 'ts_ZA', 'Tsonga', 'Südafrika'),
(237, 'tt_RU', 'Tatarisch', 'Russische Föderation'),
(238, 'ug_CN', 'Uigurisch', 'China'),
(239, 'uk_UA', 'Ukrainisch', 'Ukraine'),
(240, 'ur_IN', 'Urdu', 'Indien'),
(241, 'ur_PK', 'Urdu', 'Pakistan'),
(242, 'uz_AF', 'Usbekisch', 'Afghanistan'),
(243, 'uz_UZ', 'Usbekisch', 'Usbekistan'),
(244, 've_ZA', 'Venda-Sprache', 'Südafrika'),
(245, 'vi_VN', 'Vietnamesisch', 'Vietnam'),
(246, 'wal_ET', 'Walamo-Sprache', 'Äthiopien'),
(247, 'wo_SN', 'Wolof', 'Senegal'),
(248, 'xh_ZA', 'Xhosa', 'Südafrika'),
(249, 'yo_NG', 'Yoruba', 'Nigeria'),
(250, 'zh_CN', 'Chinesisch', 'China'),
(251, 'zh_HK', 'Chinesisch', 'Sonderverwaltungszone Hongkong'),
(252, 'zh_MO', 'Chinesisch', 'Sonderverwaltungszone Macao'),
(253, 'zh_SG', 'Chinesisch', 'Singapur'),
(254, 'zh_TW', 'Chinesisch', 'Taiwan'),
(255, 'zu_ZA', 'Zulu', 'Südafrika');

UPDATE `s_core_multilanguage` SET `locale` = '1';
UPDATE `s_core_multilanguage` SET `locale` = 'en' WHERE `isocode`=6;
UPDATE `s_core_multilanguage` SET `locale` = '2' WHERE `isocode`='en';

ALTER TABLE `s_core_snippets` DROP INDEX `namespace` ,
ADD UNIQUE `namespace` ( `namespace` , `shopID` , `name` , `localeID` );

UPDATE `s_core_config` SET `value` = 'cleanup : true, language: ''de'',skin : ''o2k7'',skin_variant : ''silver'', convert_urls : false, media_strict : false, fullscreen_new_window: true, relative_urls : false, width: "100%", invalid_elements:''script,applet,iframe'', theme_advanced_resizng : true, theme_advanced_toolbar_location : ''top'', theme_advanced_toolbar_align : ''left'', theme_advanced_path_location : ''bottom'', theme_advanced_resizing : true, extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID|value],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]"' WHERE `name` = 'sTINYMCEOPTIONS'  LIMIT 1 ;

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'SEO');

REPLACE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sSEOSTATICURLS', 'sViewport=sale&sAction=doSale,Bestellung abgeschlossen\r\nsViewport=admin&sAction=orders,{$sConfig.sSnippets.sIndexmyorders}\r\nsViewport=admin&sAction=downloads,{$sConfig.sSnippets.sIndexmyinstantdownloads}\r\nsViewport=admin&sAction=billing,{$sConfig.sSnippets.sIndexchangebillingaddress}\r\nsViewport=admin&sAction=shipping,{$sConfig.sSnippets.sIndexchangedeliveryaddress}\r\nsViewport=admin&sAction=payment,{$sConfig.sSnippets.sIndexchangepayment}\r\nsViewport=admin&sAction=ticketview,{$sConfig.sSnippets.sTicketSysSupportManagement}\r\nsViewport=logout,{$sConfig.sSnippets.sIndexlogout}\r\nsViewport=support&sFid={$sConfig.sINQUIRYID}&sInquiry=basket,{$sConfig.sSnippets.sBasketInquiry}\r\nsViewport=support&sFid={$sConfig.sINQUIRYID}&sInquiry=detail,{$sConfig.sSnippets.sArticlequestionsaboutarticle}\r\n{foreach from=$sConfig.sViewports item=viewport key=viewportID}\r\n{if $viewportID!=search}\r\nsViewport={$viewportID},{$viewport.name}\r\n{/if}\r\n{/foreach}', 'sonstige SEO-Urls', 0, 0, '', 1, 'textarea');

UPDATE `s_core_config_groups` SET file = '../../../engine/connectors/moneybookers/config.php' WHERE name = 'Moneybookers' LIMIT 1;

ALTER TABLE `s_order` ADD `internalcomment` TEXT NOT NULL AFTER `customercomment` ;
ALTER TABLE `s_user` ADD `internalcomment` TEXT NOT NULL;

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Intelligente Suche');

UPDATE `s_core_config` SET `group` = @parent, `value` = CONCAT(`value`, ',am'), `description` = 'Blacklist für Keywords' WHERE `name` = 'sBADWORDS' AND `value` NOT LIKE '%,am' LIMIT 1;

UPDATE `s_core_config` SET `name` = 'sMONEYBOOKERS_SWITCH_HIDE_LOGIN' WHERE `name` = 'sMONEYBOOKERS_HIDE_LOGIN' LIMIT 1;

CREATE TABLE IF NOT EXISTS `s_plugin_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `voucherID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID` (`voucherID`,`articleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_plugin_coupons_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `couponID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `codeID` int(11) NOT NULL,
  `stateID` int(11) NOT NULL,
  `pdf` varchar(255) NOT NULL,
  `pdfdate` datetime NOT NULL,
  `senddate` datetime NOT NULL,
  `cashdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `s_plugin_coupons` DROP INDEX `voucherID`;
ALTER TABLE `s_plugin_coupons` ADD INDEX ( `voucherID` );
ALTER TABLE `s_plugin_coupons` ADD INDEX ( `articleID` );


INSERT INTO `s_core_config_mails` (`id`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `htmlable`, `attachment`) VALUES
(NULL, 'PluginCouponsInformMerchant', 'info@shopware.de', 'Shopware Demo', 'Gutschein Bestellung - Keine oder wenige Codes vorhanden', 'Hallo,\r\n\r\nfür die Gutschein-Bestellung mit der Bestellnummer {$Ordernumber} stehen keine oder wenige Gutschein-Codes zur Verfügung! Bitte prüfen Sie, ob dieser Bestellung ein Gutschein zugeordnet werden konnte und schicken Sie dem Kunden ggf. manuell einen Gutschein-Code.\r\n', '', 0, 0, '');


INSERT INTO `s_core_config_mails` (`id`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `htmlable`, `attachment`) VALUES
(NULL, 'PluginCouponsSendCoupon', 'info@shopware.de', 'Shopware Demo', 'Ihre Gutschein Bestellung', 'Hallo {$sUser.billing_firstname}{$sUser.billing_lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrder.ordernumber}).\r\n\r\nAnbei schicken wir Ihnen die bestellten Gutschein-Codes.\r\n\r\n{$EventResult.code}\r\n\r\nViele Grüße,\r\n\r\nIhr Team von Shopware', '', 0, 0, '');

CREATE TABLE IF NOT EXISTS `s_articles_avoid_customergroups` (
  `articleID` int(11) NOT NULL,
  `customergroup` varchar(35) NOT NULL,
  UNIQUE KEY `articleID` (`articleID`,`customergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 
INSERT INTO `s_core_engine_groups` (
	`id` ,
	`group` ,
	`availablebyvariants` ,
	`position`
)
VALUES (
	NULL , 'Kundengruppen', '0', '4'
);

DELETE FROM `s_core_config` WHERE `name` IN (
 'sMAILER_SMTPDebug',
 'sMAILER_SMTPKeepAlive',
 'sMAILER_SingleTo',
 'sMAILER_Timeout',
 'sMAILER_SMTPAuth',
 'sMAILER_Sender',
 'sMAILER_Priority',
 'sMAILER_ContentType',
 'sMAILER_ErrorInfo',
 'sMAILER_WordWrap',
 'sMAILER_PluginDir',
 'sMAILER_MessageID',
 'sMAILER_Timeout',
 'sMAILER_Sendmail',
 'sMAILER_ConfirmReadingTo',
 'sMAILER_MessageID',
 'sMAILER_Helo',
 'sMAILER_SingleTo',
 'sADODB_LOG',
 'sTEMPLATEDEBUG',
 'sFIREPHP',
 'sHIGHPERFCACHE',
 'sDONTGZIP',
 'sREALCACHE',
 'sHIGHPERFCACHEVIEWPORTS',
 'sCACHESTATIC',
 'sUSEROUTER',
 'sDEBUG',
 'sPROLICENCE',
 'sLICENCENUMBER',
 'sLICENCEHOLDER',
 'sESDNUMDOWNLOADS',
 'sOPTIMIZEURLS',
 'sVOUCHERTELLFRIEND',
 'sVOUCHERTELLFRIENDVALUE',
 'sVOUCHERTELLFRIENDCODE',
 'sMEMCACHE',
 'sSHOWBASKET',
 'sDONTHASH',
 'sUSEDEFAULTTEMPLATES',
 'sVATCHECKCONFIRMATION',
 'sSHOWCLOUD'
);

UPDATE `s_core_config` SET `group` = '0' WHERE  `name` IN (
 'sORDERTABLE'
);

CREATE TABLE IF NOT EXISTS `s_categories_avoid_customergroups` (
  `categoryID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  UNIQUE KEY `articleID` (`categoryID`,`customergroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `s_categories` ADD `hidetop` INT( 1 ) NOT NULL ;

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'USt-IdNr. Überprüfung');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sVATCHECKNOSERVICE', '1', 'Wenn Service nicht erreichbar ist, nur einfach Überpürfung durchführen.', 0, 0, '', 0, 'int');

UPDATE `s_core_auth` SET `salted`=1, `password`=MD5(CONCAT('A9ASD:_AD!_=%a8nx0asssblPlasS$',`password`)) WHERE `salted`=0;

CREATE TABLE IF NOT EXISTS `s_plugin_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) NOT NULL,
  `banner_active` int(1) NOT NULL,
  `new_active` int(1) NOT NULL,
  `bought_active` int(1) NOT NULL,
  `supplier_active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID_2` (`categoryID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

DELETE FROM `s_core_config_groups` WHERE `name` = 'Google';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Anmeldung / Registrierung');

INSERT INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sNOACCOUNTDISABLE', '0', '"Kein Kundenkonto" deaktivieren', 0, 0, '', 0, 'int');

UPDATE `s_core_multilanguage` SET `template` = 'templates/orange'  WHERE `template` = 'templates/_default';
UPDATE `s_core_multilanguage` SET `doc_template` = 'templates/orange'  WHERE `doc_template` = 'templates/_default';

UPDATE `s_core_config` SET `description` = 'Eigene USt-IdNr. für die Überprüfung' WHERE `name` = 'sVATCHECKADVANCEDNUMBER';

UPDATE `s_core_menu` SET `onclick`='openAccount();' WHERE `name` = "Shopware Account";

ALTER TABLE `s_core_menu` ADD `pluginID` INT( 11 ) UNSIGNED NULL;

ALTER TABLE `s_core_menu` DROP `ul_properties`;

DELETE FROM `s_core_menu` WHERE `style` = 'display:none';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Caching');

INSERT INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sDISABLECACHE', '0', 'Shopcache deaktivieren', 0, 0, '', 0, 'int');