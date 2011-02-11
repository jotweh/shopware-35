<?php
/**
 * Enter description here...
 *
 */
abstract class Enlight_Test_TestCase extends PHPUnit_Framework_TestCase
{
	/**
     * Returns a mock object for the specified class.
     *
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.0.0
     */
	public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)
	{
		$originalClassName = Enlight_Class::getClassName($originalClassName);
		return parent::getMock($originalClassName, $methods, $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload);
	}
}