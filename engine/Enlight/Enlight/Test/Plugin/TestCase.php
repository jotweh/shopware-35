<?php
/**
 * Enter description here...
 *
 */
abstract class Enlight_Test_Plugin_TestCase extends Enlight_Test_Controller_TestCase
{
	/**
	 * Enter description here...
	 *
	 * @param string|array $name|$args
	 * @param array $args
	 * @return Enlight_Event_EventArgs
	 */
	public function createEventArgs($name=null, $args=array())
	{
		if($name===null) {
			$name = get_class($this);
		} elseif (is_array($name)) {
			$args = $name;
			$name = get_class($this);
		}
		return new Enlight_Event_EventArgs($name, $args);
	}
}