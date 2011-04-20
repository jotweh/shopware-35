<?php
/**
 * Selenium test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @subpackage Test
 */
abstract class Enlight_Components_Test_Selenium_TestCase extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/';
    protected $screenshotUrl = 'http://localhost/';

	/**
	 * Setup Shop - Set base url
	 * @return void
	 */
    protected function setUp()
    {
        $this->setBrowserUrl('http://daily.shopvm.de/');
    }
}