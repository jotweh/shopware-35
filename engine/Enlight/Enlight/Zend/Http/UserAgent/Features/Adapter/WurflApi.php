<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http_UserAgent_Features
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Http_UserAgent_Features_Adapter_Interface
 */
require_once 'Zend/Http/UserAgent/Features/Adapter.php';

/**
 * Features adapter build with the official WURFL PHP API
 * See installation instruction here : http://wurfl.sourceforge.net/nphp/ 
 * Download : http://sourceforge.net/projects/wurfl/files/WURFL PHP/1.1/wurfl-php-1.1.tar.gz/download
 *
 * @category   Zend
 * @package    Itk
 * @subpackage Zend_Browser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent_Features_Adapter_WurflApi implements Zend_Http_UserAgent_Features_Adapter {
	
	const DEFAULT_API_VERSION = '1.1';
	
	/**
	 * @static
	 * @access public
	 * @var string Download url of Wurfl Db
	 */
	public static $WURFL_DL = "http://downloads.sourceforge.net/project/wurfl/wurlf/latest/wurfl-latest.zip";
	
	/**
	 * @static
	 * @access public
	 * @var string CVS repository of Wurfl Db
	 */
	public static $WURFL_CVS_URL = "http://wurfl.cvs.sourceforge.net/%2Acheckout%2A/wurfl/xml/wurfl.xml";
	
	/**
	 * __DESC__
	 *
	 * @static
	 * @access public
	 * @param array $request $_SERVER variable
	 * @return array
	 */
	static public function getFromRequest($request) {
		if (empty ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_lib_dir'] )) {
			require_once 'Zend/Http/UserAgent/Features/Exception.php';
			throw new Zend_Http_UserAgent_Features_Exception ( 'The "wurfl_lib_dir" parameter is not defined' );
			return;
		}
		if (empty ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_config_file'] ) && empty ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_config_array'] )) {
			require_once 'Zend/Http/UserAgent/Features/Exception.php';
			//throw new Zend_Http_UserAgent_Features_Exception('The "wurfl_config_file" or "wurfl_config_array" parameter is not defined');
			throw new Zend_Http_UserAgent_Features_Exception ( 'The "wurfl_config_file" parameter is not defined' );
			return;
		}
		if (empty ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_api_version'] )) {
			Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_api_version'] = self::DEFAULT_API_VERSION;
		}
		
		switch (Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_api_version']) {
			case '1.0' :
				{
					/** Zend_Http_UserAgent::$config['wurflapi']['wurfl_config_file'] must be an XML file */
					require_once (Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_lib_dir'] . 'WURFLManagerProvider.php');
					$wurflManager = WURFL_WURFLManagerProvider::getWURFLManager ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_config_file'] );
					break;
				}
			case '1.1' :
				{
					require_once (Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_lib_dir'] . 'Application.php');
					if (! empty ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_config_file'] )) {
						$wurflConfig = WURFL_Configuration_ConfigFactory::create ( Zend_Http_UserAgent::$config ['wurflapi'] ['wurfl_config_file'] );
					}
					/** @TODO / NOT FINISHED
                     elseif (! empty(Zend_Http_UserAgent::$config['wurflapi']['wurfl_config_array'])) {
                        $configuration = Zend_Http_UserAgent::$config['wurflapi']['wurfl_config_array'];
                        $wurflConfig = new WURFL_Configuration_InMemoryConfig();
                        $wurflConfig->wurflFile($configuration['wurfl']['main-file'])->wurflPatch($configuration['wurfl']['patches'])->persistence($configuration['persistence']['provider'], $configuration['persistence']['dir']);
                    }
					 */
					$wurflManagerFactory = new WURFL_WURFLManagerFactory ( $wurflConfig );
					$wurflManager = $wurflManagerFactory->create ();
					break;
				}
		}
		
		$device = $wurflManager->getDeviceForHttpRequest ( $request );
		$features = $device->getAllCapabilities ();
		return $features;
	}
}