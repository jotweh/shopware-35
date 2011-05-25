-- Install.sql for Shopware 3.5.4

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_multilanguage` SET `switchCurrencies` = '1' WHERE `s_core_multilanguage`.`locale` = 1 AND `s_core_multilanguage`.`parentID` = 3;