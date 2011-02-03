UPDATE `s_core_config` SET `value` = '5' WHERE `name` = 'sCHARTRANGE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '100' WHERE `name` = 'sMAXPURCHASE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '0' WHERE `name` = 'sLASTARTICLESTHUMB' LIMIT 1 ;

TRUNCATE `s_emarketing_lastarticles`;
TRUNCATE `s_statistics_currentusers`;
TRUNCATE `s_statistics_pool`;
TRUNCATE `s_statistics_referer`;
TRUNCATE `s_statistics_search`;
TRUNCATE `s_statistics_visitors`;

UPDATE `s_core_config` SET `value` = '' WHERE `name` IN ('sHOST', 'sBASEPATH');
UPDATE `s_core_auth` SET `window_size` = '', `lastlogin` = '2000-01-01 00:00:00';

TRUNCATE TABLE `s_core_licences`;
TRUNCATE TABLE `s_core_log`;
TRUNCATE TABLE `s_core_sessions`;
TRUNCATE TABLE `s_order_basket`;
TRUNCATE TABLE `s_statistics_pool`;
TRUNCATE TABLE `s_statistics_visitors`;

UPDATE `s_export`
SET `last_export` = '2000-01-01 00:00:00',
	`active` = '0',
	`hash` = MD5(RAND()),
	`count_articles` = '0',
	`expiry` = '2000-01-01 00:00:00',
	`categoryID` = NULL ,
	`partnerID` = '',
	`active_filter` = '0',
	`count_filter` = '0';

UPDATE `s_core_plugins`
SET `installation_date` = '2010-10-18 00:00:00', `update_date` = '2010-10-18 00:00:00'
WHERE `installation_date` IS NOT NULL;

UPDATE `s_core_snippets` SET `shopID` = '1';