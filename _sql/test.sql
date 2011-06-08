-- Install.sql for Shopware 3.5.4

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_multilanguage` SET `switchCurrencies` = '1' WHERE `s_core_multilanguage`.`locale` = 1 AND `s_core_multilanguage`.`parentID` = 3;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
REPLACE INTO `s_core_config_mails` VALUES(NULL, 'sORDER', 'info@example.com', 'Shopware 3.0 Demo', 'Ihre Bestellung im Demoshop', 'Hallo {$billingaddress.firstname} {$billingaddress.lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\nInformationen zu Ihrer Bestellung:\r\n \r\nPos. Art.Nr.              Menge         Preis        Summe\r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\r\n{$details.articlename|wordwrap:49|indent:5}\r\n{/foreach}\r\n \r\nVersandkosten: {$sShippingCosts}\r\nGesamtkosten Netto: {$sAmountNet}\r\n{if !$sNet}\r\nGesamtkosten Brutto: {$sAmount}\r\n{/if}\r\n \r\nGewählte Zahlungsart: {$additional.payment.description}\r\n{$additional.payment.additionaldescription}\r\n{if $additional.payment.name == "debit"}\r\nIhre Bankverbindung:\r\nKontonr: {$sPaymentTable.account}\r\nBLZ:{$sPaymentTable.bankcode}\r\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\r\n{/if}\r\n{if $additional.payment.name == "prepayment"}\r\n \r\nUnsere Bankverbindung:\r\nKonto: ###\r\nBLZ: ###\r\n{/if}\r\n \r\n{if $sComment}\r\nIhr Kommentar:\r\n{$sComment}\r\n{/if}\r\n \r\nRechnungsadresse:\r\n{$billingaddress.company}\r\n{$billingaddress.firstname} {$billingaddress.lastname}\r\n{$billingaddress.street} {$billingaddress.streetnumber}\r\n{$billingaddress.zipcode} {$billingaddress.city}\r\n{$billingaddress.phone}\r\n{$additional.country.countryname}\r\n \r\nLieferadresse:\r\n{$shippingaddress.company}\r\n{$shippingaddress.firstname} {$shippingaddress.lastname}\r\n{$shippingaddress.street} {$shippingaddress.streetnumber}\r\n{$shippingaddress.zipcode} {$shippingaddress.city}\r\n{$additional.country.countryname}\r\n \r\n{if $billingaddress.ustid}\r\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\r\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\r\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\r\n{/if}\r\n \r\n \r\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt:\r\n \r\nWir wünschen Ihnen noch einen schönen Tag.\r\n \r\nFirma: ###\r\nAdresse: ###\r\nTelefon: ###\r\neMail: ###\r\nURL: ###\r\nGeschäftsführer: ###\r\nRegistriergericht: ###\r\n\r\n## Bei Bestellbestätigungen muss die Widerrufsbelehrung mitgeschickt werden. ###', '<div style="font-family:arial; font-size:12px;">\r\n<img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="Logo" />\r\n \r\n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\r\n \r\nvielen Dank fuer Ihre Bestellung bei {$sConfig.sSHOPNAME} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\n<br/>\r\n<br/>\r\n<strong>Informationen zu Ihrer Bestellung:</strong></p>\r\n  <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">\r\n    <tr>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Summe</strong></td>\r\n    </tr>\r\n \r\n    {foreach item=details key=position from=$sOrderDetails}\r\n    <tr>\r\n      <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.1}<img src="{$details.image.src.1}" alt="{$details.articlename}" />{else} {/if}</td>\r\n      <td>{$position+1|fill:4} </td>\r\n      <td>{$details.ordernumber|fill:20}</td>\r\n      <td>{$details.quantity|fill:6}</td>\r\n      <td>{$details.price|padding:8}{$sCurrency}</td>\r\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\r\n    </tr>\r\n    <tr>\r\n      <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>\r\n    </tr>\r\n    {/foreach}\r\n \r\n  </table>\r\n \r\n<p>\r\n  <br/>\r\n  <br/>\r\n    Versandkosten: {$sShippingCosts}<br/>\r\n    Gesamtkosten Netto: {$sAmountNet}<br/>\r\n    {if !$sNet}\r\n    Gesamtkosten Brutto: {$sAmount}<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>\r\n    {$additional.payment.additionaldescription}\r\n    {if $additional.payment.name == "debit"}\r\n    Ihre Bankverbindung:<br/>\r\n    Kontonr: {$sPaymentTable.account}<br/>\r\n    BLZ:{$sPaymentTable.bankcode}<br/>\r\n    Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    {if $additional.payment.name == "prepayment"}\r\n    Unsere Bankverbindung:<br/>\r\n    Konto: ###<br/>\r\n    BLZ: ###<br/>\r\n    {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\r\n</p>\r\n<p>\r\n  {if $sComment}\r\n    <strong>Ihr Kommentar:</strong><br/>\r\n    {$sComment}<br/>\r\n  {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Rechnungsadresse:</strong><br/>\r\n    {$billingaddress.company}<br/>\r\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\r\n    {$billingaddress.street} {$billingaddress.streetnumber}<br/>\r\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\r\n    {$billingaddress.phone}<br/>\r\n    {$additional.country.countryname}<br/>\r\n  <br/>\r\n  <br/>\r\n    <strong>Lieferadresse:</strong><br/>\r\n    {$shippingaddress.company}<br/>\r\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\r\n    {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\r\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\r\n    {$additional.countryShipping.countryname}<br/>\r\n  <br/>\r\n    {if $billingaddress.ustid}\r\n    Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\r\n    Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>\r\n    bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt: <br/>\r\n    <br/>\r\n    Mit freundlichen Grüßen,<br/>\r\n    Ihr Team von {$sConfig.sSHOPNAME}<br/>\r\n  <br/>\r\n  <br/>\r\n    Firma: ###<br/>\r\n    Adresse: ###<br/>\r\n    Telefon: ###<br/>\r\n    eMail: ###<br/>\r\n    URL: ###<br/>\r\n    Geschäftsführer: ###<br/>\r\n    Registriergericht: ###\r\n  <br/>\r\n  <br/>\r\n    ## Bei Bestellbestätigungen muss die Widerrufsbelehrung mitgeschickt werden. ###\r\n</p>\r\n</div>', 1, 1, '1.png;test.pdf/2.png;test2.pdf');

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

/*
 * @ticket 5258
 * @author h.lohaus
 * @since 3.5.4 - 2011/03/30
 */
DELETE FROM `s_core_snippets` WHERE `namespace` LIKE '/%' OR `namespace` LIKE 'templates/%';
UPDATE `s_core_snippets` SET `shopID` = 1 WHERE `shopID` = 0;

INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'backend/index/menu', 1, 2, 'Alle schliessen', 'Close all', '2011-03-31 11:47:42', '2011-03-31 11:47:42'),
(NULL, 'backend/index/menu', 1, 2, 'Anlegen', 'New', '2011-03-31 11:48:05', '2011-03-31 11:48:56'),
(NULL, 'backend/index/menu', 1, 2, 'Artikel', 'Products', '2011-03-31 11:49:30', '2011-04-01 11:42:15'),
(NULL, 'backend/index/menu', 1, 2, 'Artikel + Kategorien', 'Products + Categories', '2011-03-31 11:50:05', '2011-03-31 11:50:05'),
(NULL, 'backend/index/menu', 1, 2, 'Einstellungen', 'Settings', '2011-03-31 11:50:26', '2011-03-31 11:50:26'),
(NULL, 'backend/snippet/skeleton', 1, 2, 'WindowTitle', 'Textbausteine', '2011-04-01 11:33:58', '2011-04-01 11:33:58'),
(NULL, 'backend/auth/login_panel', 1, 2, 'UserNameField', 'User', '2011-04-01 11:34:47', '2011-04-01 11:36:30'),
(NULL, 'backend/auth/login_panel', 1, 2, 'PasswordMessage', 'Please enter a password!', '2011-04-01 11:35:29', '2011-04-01 11:36:08'),
(NULL, 'backend/auth/login_panel', 1, 2, 'UserNameMessage', 'Please enter a user name!', '2011-04-01 11:35:57', '2011-04-01 11:36:28'),
(NULL, 'backend/index/index', 1, 2, 'SearchLabel', 'Search', '2011-04-01 11:37:50', '2011-04-01 11:39:30'),
(NULL, 'backend/index/index', 1, 2, 'AccountMissing', 'No account created!', '2011-04-01 11:38:03', '2011-04-01 11:39:25'),
(NULL, 'backend/index/index', 1, 2, 'UserLabel', 'User: {$UserName}', '2011-04-01 11:38:20', '2011-04-01 11:39:31'),
(NULL, 'backend/index/index', 1, 2, 'LiveViewLabel', 'Shop view', '2011-04-01 11:38:40', '2011-04-01 11:39:26'),
(NULL, 'backend/index/index', 1, 2, 'AccountBalance', 'Balance: {$Amount} SC', '2011-04-01 11:38:57', '2011-04-01 11:39:24'),
(NULL, 'backend/index/menu', 1, 2, 'Fenster', 'Window', '2011-04-01 11:39:53', '2011-04-01 11:40:07'),
(NULL, 'backend/index/menu', 1, 2, 'Inhalte', 'Content', '2011-04-01 11:40:43', '2011-04-01 11:40:47'),
(NULL, 'backend/index/menu', 1, 2, 'Hilfe', 'Help', '2011-04-01 11:41:03', '2011-04-01 11:41:08'),
(NULL, 'backend/index/menu', 1, 2, 'Kunden', 'Customers', '2011-04-01 11:41:58', '2011-04-01 11:42:04'),
(NULL, 'backend/auth/login_panel', 1, 2, 'LoginButton', 'Login', '2011-04-01 11:37:09', '2011-04-01 11:37:09'),
(NULL, 'backend/auth/login_panel', 1, 2, 'LocaleField', 'Language', '2011-04-01 11:37:32', '2011-04-01 11:37:32'),
(NULL, 'backend/auth/login_panel', 1, 2, 'PasswordField', 'Password', '2011-04-01 11:37:32', '2011-04-01 11:37:32');

/*
 * @ticket 4778
 * @author h.lohaus
 * @since 3.5.4 - 2011/04/01
 */
ALTER TABLE `s_core_currencies` ADD `symbol_position` INT( 11 ) UNSIGNED NOT NULL AFTER `templatechar`;

/*
 * @ticket 5068
 * @author h.lohaus
 * @since 3.5.4 - 2011/04/12
 */
UPDATE `s_core_menu` SET `style` = 'background-position: 5px 5px;' WHERE `name` = 'Textbausteine';
UPDATE `s_core_config` SET `value` = '3.5.4' WHERE `name` = 'sVERSION';

/*
 * @ticket 4836
 * @author st.hamann
 * @since 3.5.4 - 2011/05/18
 */
INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/password', 1, 1, 'PasswordSendAction', 'Passwort anfordern', '2011-05-17 11:47:42', '2011-05-17 11:47:42');


/*
 * @ticket 5125
 * @author h.lohaus
 * @since 3.5.4 - 2011/05/18
 */
UPDATE `s_core_config_mails` SET `name` = TRIM(`name`);

/*
 * @ticket 5125
 * @author h.lohaus
 * @since 3.5.4 - 2011/05/18
 */
DELETE FROM `s_core_subscribes` WHERE `listener` LIKE 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::%';
INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onRouteShutdown', 35, -100);
INSERT IGNORE INTO `s_core_plugin_configs` (`id`, `name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
(NULL, 'rfi_protection', 's:1:"1";', 35, 1, 1),
(NULL, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 35, 1, 1);
INSERT IGNORE INTO `s_core_plugin_elements` (`id`, `pluginID`, `name`, `value`, `label`, `description`, `type`, `required`, `order`, `scope`, `filters`, `validators`) VALUES
(NULL, 35, 'rfi_protection', 'i:1;', 'RemoteFileInclusion-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL),
(NULL, 35, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 'RemoteFileInclusion-Filter', '', 'Text', 0, 0, 0, NULL, NULL);

/*
 * @ticket 4708
 * @author st.hamann
 * @since 3.5.4 - 2011/05/21
 */
ALTER TABLE `s_emarketing_vouchers` ADD `taxconfig` VARCHAR( 15 ) NOT NULL;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/listing/box_article', 1, 1, 'ListingBoxArticleContent', 'Inhalt', '2011-05-24 10:31:14', '2011-05-24 10:31:47'),
(NULL, 'frontend/listing/box_article', 1, 1, 'ListingBoxBaseprice', 'Grundpreis', '2011-05-24 10:33:36', '2011-05-24 10:33:55'),
(NULL, 'frontend/note/item', 1, 1, 'NoteUnitPriceContent', 'Inhalt', '2011-05-24 11:25:13', '2011-05-24 11:26:33'),
(NULL, 'frontend/note/item', 1, 1, 'NoteUnitPriceBaseprice', 'Grundpreis', '2011-05-24 11:25:13', '2011-05-24 11:26:46'),
(NULL, 'frontend/compare/col', 1, 1, 'CompareContent', 'Inhalt', '2011-05-24 11:51:10', '2011-05-24 11:51:36'),
(NULL, 'frontend/compare/col', 1, 1, 'CompareBaseprice', 'Grundpreis', '2011-05-24 11:51:10', '2011-05-24 11:51:46'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoContent', 'Inhalt', '2011-05-24 13:11:55', '2011-05-24 13:51:56'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoCurrentPrice', 'Aktueller Einzelpreis', '2011-05-24 14:22:31', '2011-05-24 14:22:59'),
(NULL, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(NULL, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoContent', 'Inhalt', '2011-05-24 14:22:31', '2011-05-24 14:22:59');

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
UPDATE `s_core_snippets`
SET `value` = '- Bestellen Sie f&uuml;r weitere {$sShippingcostsDifference|currency} um Ihre Bestellung versandkostenfrei in {$sCountry.countryname} zu erhalten!' WHERE `s_core_snippets`.`name` LIKE 'CartInfoFreeShippingDifference' AND `s_core_snippets`.`localeID` = 1;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
UPDATE `s_core_snippets` SET `value` = '<a title="Mehr Informationen zu {config name=Shopname}" href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank"> {config name=Shopname} ist ein von Trusted Shops gepr&uuml;fter Onlineh&auml;ndler mit G&uuml;tesiegel und <a href="http://www.trustedshops.de/info/garantiebedingungen/" target="_blank">K&auml;uferschutz.</a> <a title="Mehr Informationen zu " href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank">Mehr...</a> </a>' WHERE `s_core_snippets`.`name` LIKE 'WidgetsTrustedLogoText' AND `s_core_snippets`.`localeID` = 1;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalRequiredText', '* hierbei handelt es sich um ein Pflichtfeld', '2011-05-24 17:12:28', '2011-05-24 17:13:52');

/**
 * @ticket 4734
 * @author st.hamann
 * @since 3.5.4 - 2011/05/24
 */
ALTER TABLE `s_core_plugins` ADD `added` DATETIME NOT NULL AFTER `active`;
ALTER TABLE `s_core_plugin_elements` ADD `options` TEXT NOT NULL;

ALTER TABLE `s_core_plugins` ADD `checkversion` VARCHAR( 255 ) NOT NULL AFTER `version` ,
ADD `checkdate` DATE NOT NULL AFTER `checkversion`;

/**
 * @ticket 4766
 * @author h.lohaus
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_config` SET `value` = '1' WHERE `name` = 'sDISABLECACHE';

/**
 * @ticket 4354
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_snippets` SET `value` = 'Versandinfo:' WHERE `s_core_snippets`.`name` LIKE 'DispatchHeadNotice' AND `s_core_snippets`.`localeID` = 1;
UPDATE `s_core_snippets` SET `value` = 'Dispatch info:' WHERE `s_core_snippets`.`name` LIKE 'DispatchHeadNotice' AND `s_core_snippets`.`localeID` = 2;
UPDATE `s_core_snippets` SET `value` = 'Login' WHERE `s_core_snippets`.`name` LIKE 'AccountLoginTitle' AND `s_core_snippets`.`localeID` = 1;
UPDATE `s_core_snippets` SET `value` = 'Login' WHERE `s_core_snippets`.`name` LIKE 'AccountLoginTitle' AND `s_core_snippets`.`localeID` = 2;

/**
 * @ticket 4842
 * @author st.hamann
 * @since 3.5.4 - 2011/05/26
 */
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, '35', 'sTAXAUTOMODE', '1', 'Steuer für Rabatte dynamisch feststellen', '0', '0', '', '1', 'int');

/**
 * @ticket 4226
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/03
 */
INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_Acl', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', 36, 0);

/**
 * @ticket 5089
 * @author st.hamann
 * @since 3.5.4 - 2011/06/04
 */
CREATE TABLE IF NOT EXISTS `s_plugin_benchmark_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `hash` varchar(255) NOT NULL,
  `query` text NOT NULL,
  `parameters` text NOT NULL,
  `time` float NOT NULL,
  `ipaddress` varchar(24) NOT NULL,
  `route` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `datum` (`datum`),
  KEY `session` (`session`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=418 ;

/**
 * @ticket 5427
 * @author st.hamann
 * @since 3.5.4 - 2011/06/06
 */
ALTER TABLE `s_core_auth` ADD `failedlogins` INT NOT NULL ,
ADD `lockeduntil` DATETIME NOT NULL;

ALTER TABLE `s_user` ADD `failedlogins` INT NOT NULL ,
ADD `lockeduntil` DATETIME NOT NULL;

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` = 'sonstige');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`, `created`, `modified`, `locale`, `namespace`) VALUES
(NULL, @parent, 'sErrorLoginLocked', 'Zu viele fehlgeschlagene Versuche. Ihr Account wurde vorübergehend deaktivert - bitte probieren Sie es in einigen Minuten erneut!', '', NULL, NULL, 'de_DE', 'Frontend');

/**
 * @ticket 5124
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/07
 */
ALTER TABLE `s_core_paymentmeans` ADD `action` VARCHAR( 255 ) NULL,
ADD `pluginID` INT( 11 ) UNSIGNED NULL;

INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
(NULL, 'Frontend', 'Payment', 'Payment', 'Default', '', '', 1, '0000-00-00 00:00:00', '2011-05-11 14:06:17', '2011-05-11 14:06:17', 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');

SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `name` = 'Payment');

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_Payments', 0, 'Shopware_Plugins_Frontend_Payment_Bootstrap::onInitResourcePayments', @parent, 0);

/**
 * @ticket 5124
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/08
 */
DELETE FROM `s_core_config` WHERE `name` LIKE 'sCLICKPAY%';
DELETE FROM `s_core_config_groups` WHERE `name` LIKE 'ClickPay';
DELETE FROM `s_core_config_text` WHERE `name` LIKE 'sClickPay%';
DELETE FROM `s_core_menu` WHERE `name` LIKE '%ClickPay%';
DELETE FROM `s_core_paymentmeans` WHERE `name` LIKE 'clickpay_%';
DROP TABLE IF EXISTS `eos_risk_results`;

UPDATE `s_core_config` SET value = '' WHERE name = 'sACCOUNTID';
UPDATE `s_core_config` SET value = '0' WHERE name = 'sROUTERURLCACHE';

INSERT IGNORE INTO `s_core_menu` (
`id` ,
`parent` ,
`hyperlink` ,
`name` ,
`onclick` ,
`style` ,
`class` ,
`position` ,
`active` ,
`pluginID`
)
VALUES (
NULL , '40', '', 'Shopware ID registrieren', 'window.open(''http://account.shopware.de'',''Shopware'',''width=800,height=550,scrollbars=yes'')', 'background-position: 5px 5px', 'ico2 book_open', '-1', '1', NULL
);

UPDATE `s_core_multilanguage` SET `domainaliase` = '' LIMIT 1 ;
