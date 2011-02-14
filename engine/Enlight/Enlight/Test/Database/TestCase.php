<?php
/**
 * Enlight database test case
 */
abstract class Enlight_Test_Database_TestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
	protected function getConnection()
	{
		$pdo = Enlight::Instance()->Db()->getConnection();
		return $this->createDefaultDBConnection($pdo);
    }
}