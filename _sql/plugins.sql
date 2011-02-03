DROP TABLE IF EXISTS `s_core_plugin_configs`;
CREATE TABLE IF NOT EXISTS `s_core_plugin_configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `pluginID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Tabellenstruktur für Tabelle `s_core_subscribes`
--

DROP TABLE IF EXISTS `s_core_subscribes`;
CREATE TABLE IF NOT EXISTS `s_core_subscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe` varchar(255) NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `listener` varchar(255) NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `position` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribe` (`subscribe`,`type`,`listener`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `s_core_subscribes`
--

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(1, 'Enlight_Bootstrap_InitResource_License', 0, 'Shopware_Plugins_Core_License_Bootstrap::onInitResourceLicense', 17, 0),
(2, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_Shop_Bootstrap::onPreDispatch', 8, 0),
(3, 'Enlight_Bootstrap_InitResource_Shop', 0, 'Shopware_Plugins_Core_Shop_Bootstrap::onInitResourceShop', 8, 0),
(4, 'Enlight_Bootstrap_InitResource_Auth', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAuth', 36, 0),
(5, 'Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onPreDispatchBackend', 36, 0),
(6, 'Enlight_Bootstrap_InitResource_Menu', 0, 'Shopware_Plugins_Backend_Menu_Bootstrap::onInitResourceMenu', 35, 0),
(7, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Core_ControllerBase_Bootstrap::onPostDispatch', 13, 100),
(8, 'Enlight_Controller_Front_StartDispatch', 0, 'Shopware_Plugins_Core_ErrorHandler_Bootstrap::onStartDispatch', 12, 0),
(9, 'Enlight_Plugins_ViewRenderer_FilterRender', 0, 'Shopware_Plugins_Core_PostFilter_Bootstrap::onFilterRender', 9, 0),
(10, 'Enlight_Bootstrap_InitResource_System', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceSystem', 6, 0),
(11, 'Enlight_Bootstrap_InitResource_Modules', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceModules', 6, 0),
(12, 'Enlight_Bootstrap_InitResource_Adodb', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceAdodb', 6, 0),
(13, 'Enlight_Bootstrap_InitResource_Template', 0, 'Shopware_Plugins_Core_Template_Bootstrap::onInitResourceTemplate', 1, 0),
(14, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_ViewportForward_Bootstrap::onPreDispatch', 7, 10),
(15, 'Enlight_Controller_Front_RouteStartup', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onRouteStartup', 4, 0),
(16, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onRouteShutdown', 4, 0),
(17, 'Enlight_Controller_Router_FilterAssembleParams', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble', 4, 0),
(18, 'Enlight_Controller_Router_FilterUrl', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl', 4, 0),
(19, 'Enlight_Controller_Router_Assemble', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onAssemble', 4, 100);


--
-- Tabellenstruktur für Tabelle `s_core_plugins`
--

DROP TABLE IF EXISTS `s_core_plugins`;
CREATE TABLE IF NOT EXISTS `s_core_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `description_long` text NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `installation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `autor` varchar(255) DEFAULT NULL,
  `copyright` varchar(255) DEFAULT NULL,
  `license` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `support` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

--
-- Daten für Tabelle `s_core_plugins`
--

INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`) VALUES
(1, 'Core', 'Template', '', '', '', '', 1, '2010-10-13 22:02:31', '2010-10-13 22:02:31', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(2, 'Core', 'Cron', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(3, 'Core', 'Debug', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(4, 'Core', 'Router', '', '', '', '', 1, '2010-10-13 22:08:39', '2010-10-13 22:08:39', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(5, 'Core', 'BenchmarkEvents', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(6, 'Core', 'System', '', '', '', '', 1, '2010-10-13 22:02:24', '2010-10-13 22:02:24', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(7, 'Core', 'ViewportForward', '', '', '', '', 1, '2010-10-13 22:02:35', '2010-10-13 22:02:35', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(8, 'Core', 'Shop', '', '', '', '', 1, '2010-10-13 22:02:03', '2010-10-13 22:02:03', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(9, 'Core', 'PostFilter', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(10, 'Core', 'Log', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(11, 'Core', 'CronRating', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(12, 'Core', 'ErrorHandler', '', '', '', '', 1, '2010-10-13 22:01:35', '2010-10-13 22:01:35', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(13, 'Core', 'ControllerBase', '', '', '', '', 1, '2010-10-13 22:01:28', '2010-10-13 22:01:28', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(14, 'Core', 'Benchmark', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(15, 'Core', 'CronStock', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(16, 'Core', 'Api', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(17, 'Core', 'License', '', '', '', '', 1, '2010-10-13 22:01:40', '2010-10-13 22:01:40', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(18, 'Frontend', 'Compare', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(19, 'Frontend', 'Seo', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(20, 'Frontend', 'LastArticles', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(21, 'Frontend', 'RouterOld', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(22, 'Frontend', 'Ticket', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(23, 'Frontend', 'Google', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(24, 'Frontend', 'ViewportDispatcher', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(25, 'Frontend', 'Paypal', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(26, 'Frontend', 'ReCaptcha', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(27, 'Frontend', 'AdvancedMenu', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(28, 'Frontend', 'CouponsSelling', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(29, 'Frontend', 'Statistics', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(30, 'Frontend', 'Recommendation', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(31, 'Frontend', 'Notification', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(32, 'Frontend', 'RouterRewrite', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(33, 'Frontend', 'TagCloud', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(34, 'Frontend', 'InputFilter', '', '', '', '', 1, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(35, 'Backend', 'Menu', '', '', '', '', 1, '2010-10-13 22:01:22', '2010-10-13 22:01:22', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', ''),
(36, 'Backend', 'Auth', '', '', '', '', 1, '2010-10-13 22:01:17', '2010-10-13 22:01:17', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', '');


DROP TABLE IF EXISTS `s_core_plugin_elements`;
CREATE TABLE IF NOT EXISTS `s_core_plugin_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pluginID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL, 
  `label` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `order` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `filters` text,
  `validators` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pluginID` (`pluginID`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

SET @parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Einstellungen');

INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `ul_properties`) VALUES
(NULL, @parent, '', 'Plugins', 'openAction(''plugin'');', 'background-position: 5px 5px;', 'ico2 bricks', -4, 1, '');

ALTER TABLE `s_core_subscribes` CHANGE `position` `position` INT( 11 ) NOT NULL;

ALTER TABLE `s_core_plugins` ADD `changes` TEXT NOT NULL ,
ADD `link` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `s_core_plugin_configs` ADD UNIQUE (
	`name` ,
	`pluginID` ,
	`localeID` ,
	`shopID`
);