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
 * @category Zend
 * @package Zend_Http_UserAgent
 * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Http/UserAgent/Exception.php';
require_once 'Zend/Http/UserAgent/AbstractUserAgent.php';

/**
 * Lists of User Agent chains for testing :
 * http://www.useragentstring.com/layout/useragentstring.php
 * http://user-agent-string.info/list-of-ua
 * http://www.user-agents.org/allagents.xml
 * http://en.wikipedia.org/wiki/List_of_user_agents_for_mobile_phones
 * http://www.mobilemultimedia.be/fr/
 *
 * @category Zend
 * @package Zend_Http_UserAgent
 * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent
{

    /**
     * 'desktop' by default if the sequence return false for each item or is empty
     */
    const DEFAULT_IDENTIFICATION_SEQUENCE = 'mobile,desktop';

    /**
     * 
     * Default persitent storage adapter : Session or NonPersitent
     */
    const DEFAULT_PERSISTENT_STORAGE_ADAPTER = 'Session';

    /**
     * 'desktop' by default if the sequence return false for each item
     */
    const DEFAULT_BROWSER_TYPE = 'desktop';

    /**
     * Default User Agent chain to prevent empty value 
     */
    const DEFAULT_HTTP_USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)';

    /**
     * Default Http Accept param to prevent empty value 
     */
    const DEFAULT_HTTP_ACCEPT = "application/xhtml+xml";

    /**
     * Default markup language 
     */
    const DEFAULT_MARKUP_LANGUAGE = "xhtml";

    /**
     * Persistent storage handler
     *
     * @var Zend_Http_UserAgent_Storage
     */
    protected static $_storage = null;

    /**
     * Static array to store config
     * 
     * @static
     * @access public
     * @var array
     */
    public static $config;

    /**
     * Trace of items matched to identify the browser type
     *
     * @var array
     */
    public static $matchLog = array();

    /**
     * Identified device
     *
     * @access public
     * @var Zend_Http_UserAgent_AbstractUserAgent
     */
    public $device;

    /**
     * Browser type
     *
     * @access public
     * @var string
     */
    public $browserType;

    public function __construct ($userAgent = null)
    {
        
        /** init the config param if needed */
        $this->_defaultConfig();
        
        /** get the User Agent chain : from $_SERVER or forced by the $userAgent param */
        
        $browser = self::getUserAgent($userAgent);
        
        /** search an existing identification ine the session */
        $storage = $this->getStorage($browser);
        
        if (! $storage->isEmpty()) {
            //var_dump ( 'session' );
            


            /** if the user agent and features are already existing, the Zend_Http_UserAgent object is serialized in session */
            $object = $storage->read();
            self::loadClass($object['type']);
            $instance = unserialize($object['object']);
            foreach ($instance as $key => $val) {
                $this->{$key} = $val;
            }
        } else {
            //var_dump ( 'new' );
            


            /** otherwise, the identification is made and stored in session */
            /** find the browser type */
            $this->browserType = $this->matchUserAgent();
            
            /** search the device and browser features */
            $this->device = self::factory($this->browserType);
            
            /* put the result in storage */
            $this->getStorage($browser)->write(array(
                'type' => $this->browserType , 
                'object' => serialize($this)
            ));
        }
    }

    /**
     * Starts the identification of the browser/device's features
     *
     * @access protected
     * @return void
     */
    static public function factory ($browsertype)
    {
        self::loadClass($browsertype);
        $className = self::getClassName($browsertype);
        return new $className();
    }

    /**
     * Comparison of the UserAgent chain and browser signatures.
     * 
     * The comparison is case-insensitive : the browser signatures must be in lower
     * case
     *
     * @static
     * @access public
     * @param string $userAgent UserAgent chain
     * @param string $uaSignatures (option) Browsers signatures (in lower case)
     * @return bool
     */
    public static function match ($userAgent, $uaSignatures = null)
    {
        $lowerUserAgent = strtolower($userAgent);
        if (! is_null($uaSignatures)) {
            foreach ($uaSignatures as $browser_signature) {
                if (! empty($browser_signature)) {
                    if (strpos($lowerUserAgent, $browser_signature) !== false) {
                        self::$matchLog[] = $browser_signature; //trace
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Run the identification sequence to match the right browser type according to the
     * user agent
     *
     * @access protected
     * @return Zend_Http_UserAgent_Result
     */
    public function matchUserAgent ()
    {
        $type = self::DEFAULT_BROWSER_TYPE;
        if (! empty(self::$config['identification_sequence'])) {
            $sequence = explode(',', self::$config['identification_sequence']);
            if (is_array($sequence)) {
                foreach ($sequence as $browserType) {
                    $browserType = trim($browserType);
                    self::loadClass($browserType);
                    $className = self::getClassName($browserType);
                    //var_dump($className);
                    /** the match method must exists, declared in the abstract class */
                    $match = call_user_func_array(array(
                        $className , 
                        'match'
                    ), array(
                        self::getUserAgent()
                    ));
                    if ($match) {
                        $type = $browserType;
                        break;
                    }
                }
            }
        }
        return $type;
    }

    /**
     * Config parameters is an Array or a Zend_Config object
     * 
     * The allowed parameters are :
     * - the identification sequence (can be empty) => desktop browser type is the
     * default browser type returned
     * $config['identification_sequence'] : ',' separated browser types
     * - the persistent storage adapter 
     * $config['persistent_storage_adapter'] = "Session" or "NonPersistent"
     * - to add or replace a browser type matcher 
     * $config[(type)]['matcher']['path']
     * $config[(type)]['matcher']['classname']
     * - to add or replace a browser type features adapter 
     * $config[(type)]['features']['path']
     * $config[(type)]['features']['classname']
     * 
     * @static
     * @access public
     * @param mixed $config (option) Config array
     * @return void
     */
    public static function setConfig ($config = array())
    {
        if (! is_null($config)) {
            if ($config instanceof Zend_Config) {
                $config = $config->toArray();
            }
            /*
            * Verify that Config parameters are in an array.
            */
            if (! is_array($config)) {
                /**
                 * @see Zend_Exception
                 */
                require_once 'Zend/Http/UserAgent/Exception.php';
                throw new Zend_Http_UserAgent_Exception('Config parameters must be in an array or a Zend_Config object');
            }
            self::$config = $config;
        }
    }

    /**
     * To define the minimum config
     *
     * @static
     * @access public
     * @return void
     */
    protected function _defaultConfig ()
    {
        if (! isset(self::$config['identification_sequence'])) {
            self::$config['identification_sequence'] = self::DEFAULT_IDENTIFICATION_SEQUENCE;
        }
        if (empty(self::$config['persistent_storage_adapter'])) {
            self::$config['persistent_storage_adapter'] = self::DEFAULT_PERSISTENT_STORAGE_ADAPTER;
        }
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been
     * set.
     *
     * @access public
     * @param string $browser Browser identifier (User Agent chain)
     * @return Zend_Http_UserAgent_Storage
     */
    public function getStorage ($browser)
    {
        if (null === self::$_storage) {
            require_once 'Zend/Http/UserAgent/Storage/' . self::$config['persistent_storage_adapter'] . '.php';
            $adapter = 'Zend_Http_UserAgent_Storage_' . self::$config['persistent_storage_adapter'];
            $this->setStorage(new $adapter($browser));
        }
        return self::$_storage;
    }

    /**
     * Clean the persistent storage
     *
     * @access public
     * @param string $browser Browser identifier (User Agent chain)
     * @return Zend_Http_UserAgent_Storage
     */
    public function clearStorage ($browser)
    {
        if (self::$_storage instanceof Zend_Http_UserAgent_Storage) {
            self::$_storage->clear();
        }
    }

    /**
     * Sets the persistent storage handler
     *
     * @static
     * @access public
     * @param Zend_Http_UserAgent_Storage $storage
     * @return Zend_Http_UserAgent
     */
    public function setStorage (Zend_Http_UserAgent_Storage $storage)
    {
        self::$_storage = $storage;
    }

    /**
     * Returns the current Browser type
     *
     * @access public
     * @return string
     */
    public function getType ()
    {
        return $this->browserType;
    }

    /**
     * Loads class for a user agent matcher
     *
     * @static
     * @access public
     * @param string $browserType Browser type
     * @return void
     */
    static function loadClass ($browserType)
    {
        /* maybe to replace with this :
        if (!class_exists($adapterName)) {
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($adapterName);
        }
        */
        $path = '';
        if (! class_exists(self::getClassName($browserType))) {
            if (! empty(self::$config[$browserType]['matcher']['path'])) {
                /** default */
                $path = self::$config[$browserType]['matcher']['path'];
            } elseif (isset(self::$config[$browserType]['matcher'])) {
                require_once 'Zend/Http/UserAgent/Exception.php';
                throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' matcher must have a "path" config parameter defined');
            } else {
                $path = 'Zend/Http/UserAgent/' . UcFirst(strtolower($browserType)) . '.php';
            }
            try {
                include_once ($path);
            } catch (Exception $e) {
                throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' matcher path does not exists');
            }
        }
        return $path;
    }

    /**
     * Gets class name for a user agent matcher
     *
     * @static
     * @access public
     * @param string $browserType Browser type
     * @return string
     */
    public static function getClassName ($browserType)
    {
        $className = '';
        if (! empty(self::$config[$browserType]['matcher']['classname'])) {
            $className = self::$config[$browserType]['matcher']['classname'];
        } elseif (isset(self::$config[$browserType]['matcher'])) {
            require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' matcher must have a "classname" config parameter defined');
        } else {
            /** default name */
            $className = 'Zend_Http_UserAgent_' . ucFirst(strtolower($browserType));
        }
        return $className;
    }

    /**
     * Returns the User Agent value
     * if $userAgent param is null, the value of $_SERVER['HTTP_USER_AGENT'] is
     * returned
     * 
     * Otherwise it can be forced
     *
     * In practice, the first call can use a $userAgent param to force or initialize
     * the $_SERVER param,
     * the next calls can be made without the $userAgent param
     *
     * @static
     * @access public
     * @param string $userAgent (option) forced UserAgent chain
     * @return string
     */
    static public function getUserAgent ($userAgent = null)
    {
        /** to prevent empty UA or to force an UA chain */
        self::initUserAgent($userAgent);
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Returns the HTTP Accept server param
     *
     * @static
     * @access public
     * @param string $httpAccept (option) forced HTTP Accept chain
     * @return string
     */
    static public function getHttpAccept ($httpAccept = null)
    {
        /** to prevent empty HTTP_ACCEPT or to force an HTTP_ACCEPT chain */
        self::initHttpAccept($httpAccept);
        return $_SERVER['HTTP_ACCEPT'];
    }

    /**
     * User Agent initialisation if it's empty or to be forced
     *
     * @static
     * @access public
     * @param string $userAgent (option) forced UserAgent chain
     * @return void
     */
    static public function initUserAgent ($userAgent = null)
    {
        if (! is_null($userAgent)) {
            self::setUserAgent($userAgent);
        } elseif (empty($_SERVER['HTTP_USER_AGENT']) or is_null($_SERVER['HTTP_USER_AGENT'])) {
            self::setUserAgent(self::DEFAULT_HTTP_USER_AGENT);
        }
    }

    /**
     * HTTP Accept initialisation
     *
     * @static
     * @access public
     * @param string $httpAccept (option) forced HTTP Accept chain
     * @return void
     */
    static public function initHttpAccept ($httpAccept = null)
    {
        if (! is_null($httpAccept)) {
            self::setHttpAccept($httpAccept);
        } elseif (empty($_SERVER['HTTP_ACCEPT']) or is_null($_SERVER['HTTP_ACCEPT'])) {
            self::setHttpAccept(self::DEFAULT_HTTP_ACCEPT);
        }
    }

    /**
     * Force or replace the UA chain in $_SERVER variable
     *
     * @static
     * @access public
     * @param string $userAgent Forced UserAgent chain
     * @return void
     */
    static public function setUserAgent ($userAgent)
    {
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
    }

    /**
     * Force or replace the HTTP_ACCEPT chain in $_SERVER variable
     *
     * @static
     * @access public
     * @param string $httpAccept Forced HTTP Accept chain
     * @return void
     */
    static public function setHttpAccept ($httpAccept)
    {
        $_SERVER['HTTP_ACCEPT'] = $httpAccept;
    }

}