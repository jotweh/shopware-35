<?php
/**
 * Test suite
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @subpackage Test
 */
class Enlight_Components_Test_TestSuite extends PHPUnit_Framework_TestSuite
{	
	/**
     * Adds a test to the suite.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  array                  $groups
     */
	public function addTest(PHPUnit_Framework_Test $test, $groups = array())
    {
    	if ($test instanceof PHPUnit_Framework_TestSuite && empty($groups)) {
            $groups = $test->getGroups();
        }
        $groups[] = $this->getName();
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            $tests = $test->tests();
            $test = new self();
            foreach ($tests as $childTest) {
            	$test->addTest($childTest, $groups);
            }
        }
    	parent::addTest($test, $groups);
    }
}