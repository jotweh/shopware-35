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
    protected $browserUrl = 'http://daily.shopvm.de/';
    protected $captureScreenshotOnFailure = true;
    protected $screenshotUrl = 'http://hl.shopvm.de/screenshots';
    protected $screenshotPath = 'D:\\XAMPP\\xampplite\\htdocs\\screenshots\\';

	/**
	 * Setup Shop - Set base url
	 * @return void
	 */
    protected function setUp()
    {
    	if($this->browserUrl !== null) {
    		$this->setBrowserUrl($this->browserUrl);
    	}
    	parent::setUp();
    }
    
    /**
     * Verify text method
     *
     * @param unknown_type $selector
     * @param unknown_type $content
     * @return unknown
     */
    public function verifyText($selector, $content)
    {
    	return $this->assertSelectEquals($selector, $content, true, $this->getHtmlSource());
    }
}