<?php
/**
 * Enter description here...
 *
 */
abstract class Enlight_Test_Database_TestCase extends PHPUnit_Extensions_Database_TestCase
{
	protected function getConnection()
	{
		$pdo = Enlight::Instance()->Db()->getConnection();
		return $this->createDefaultDBConnection($pdo);
    }
}