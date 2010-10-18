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
 * @package    Zend_Browser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Abstract Class to define a browser type.
 *
 * @category   Zend
 * @package    Zend_Browser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Http_UserAgent_AbstractUserAgent
{

    /**
     * Browser signature
     *
     * @access public
     * @var string
     */
    public $browser = '';

    /**
     * Browser version
     *
     * @access public
     * @var string
     */
    public $browser_version = '';

    /**
     * User Agent chain
     *
     * @access public
     * @var string
     */
    public $userAgent;

    protected $_images = array(
        'jpeg' , 
        'gif' , 
        'png' , 
        'pjpeg' , 
        'x-png' , 
        'bmp'
    );

    /**
     * Browser/Device features
     *
     * @access protected
     * @var array
     */
    protected $_aFeatures;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct ()
    {
        $this->userAgent = Zend_Http_UserAgent::getUserAgent();
        $this->_getDefaultFeatures();
        $this->defineFeatures();
    }

    /**
     * Look for features
     *
     * @access public
     * @return array
     */
    public function defineFeatures ()
    {
        $features = self::loadFeaturesAdapter($this->getType());
        
        if (is_array($features)) {
            $this->_aFeatures = array_merge($this->_aFeatures, $features);
        }
        
        return $this->_aFeatures;
    }

    /**
     * Match method 
     *
     * @static
     * @access public
     * @param string $userAgent User Agent chain
     * @return bool
     */
    abstract static function match ($userAgent);

    /**
     * IGets the browser type identifier
     *
     * @access public
     * @return string
     */
    abstract public function getType ();

    /**
     * Check a feature for the current browser/device.
     *
     * @access public
     * @param string $feature The feature to check.
     * @return bool
     */
    public function hasFeature ($feature)
    {
        $return = false;
        if (! empty($this->_aFeatures[$feature])) {
            $return = true;
        }
        return $return;
    }

    /**
     * Gets the value of the current browser/device feature
     *
     * @access public
     * @param string $feature Feature to search
     * @return string
     */
    public function getFeature ($feature)
    {
        $return = null;
        if (! empty($this->_aFeatures[$feature])) {
            $return = $this->_aFeatures[$feature];
        }
        return $return;
    }

    /**
     * Set a feature for the current browser/device.
     *
     * @access public
     * @param string $feature The feature to set.
     * @param string $value (option) feature value.
     * @param string $group (option) Group to associate with the feature
     * @return void
     */
    public function setFeature ($feature, $value = false, $group = '')
    {
        $this->_aFeatures[$feature] = $value;
        if (! empty($group)) {
            $this->setGroup($group, $feature);
        }
    }

    /**
     * Affects a feature to a group
     *
     * @access public
     * @param string $group Group name
     * @param string $feature Feature name
     * @return void
     */
    public function setGroup ($group, $feature)
    {
        if (! isset($this->_aGroup[$group])) {
            $this->_aGroup[$group] = array();
        }
        if (! in_array($feature, $this->_aGroup[$group])) {
            $this->_aGroup[$group][] = $feature;
        }
    }

    /**
     * Gets an array of features associated to a group
     *
     * @access public
     * @param string $group Group param
     * @return array
     */
    public function getGroup ($group)
    {
        return $this->_aGroup[$group];
    }

    /**
     * Gets all th browser/device features
     *
     * @access public
     * @return arrya
     */
    public function getAllFeatures ()
    {
        return $this->_aFeatures;
    }

    /**
     * Gets all th browser/device features' groups
     *
     * @access public
     * @return array
     */
    public function getAllGroups ()
    {
        return $this->_aGroup;
    }

    /**
     * Sets all the standard features extrated from the User Agent chain and $_SERVER
     * vars
     *
     * @access protected
     * @return void
     */
    protected function _getDefaultFeatures ()
    {
        /** gets info from user agent chain */
        
        $uaExtract = $this->extractFromUserAgent($this->userAgent);
        
        if (is_array($uaExtract)) {
            foreach ($uaExtract as $key => $info) {
                $this->setFeature($key, $info, 'product_info');
            }
        }
        
        if (isset($uaExtract['browser_name'])) {
            $this->browser = $uaExtract['browser_name'];
        }
        if (isset($uaExtract['browser_version'])) {
            $this->browser_version = $uaExtract['browser_version'];
        }
        if (isset($uaExtract['device_os'])) {
            $this->device_os = $uaExtract['device_os_name'];
        }
        
        /* browser & device info */
        $this->setFeature('is_wireless_device', false, 'product_info');
        $this->setFeature('is_mobile', false, 'product_info');
        $this->setFeature('is_desktop', false, 'product_info');
        $this->setFeature('is_tablet', false, 'product_info');
        $this->setFeature('is_bot', false, 'product_info');
        $this->setFeature('is_email', false, 'product_info');
        $this->setFeature('is_text', false, 'product_info');
        $this->setFeature('device_claims_web_support', false, 'product_info');
        
        $this->setFeature('is_' . strtolower($this->getType()), true, 'product_info');
        
        /** sets the browser name */
        if (isset($this->list) && empty($this->browser)) {
            $lowerUserAgent = strtolower($this->userAgent);
            foreach ($this->list as $browser_signature) {
                if (strpos($lowerUserAgent, $browser_signature) !== false) {
                    $this->browser = strtolower($browser_signature);
                    $this->setFeature('browser_name', $this->browser, 'product_info');
                }
            }
        }
        
        /** sets the client IP */
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $this->setFeature('client_ip', $_SERVER["REMOTE_ADDR"], 'product_info');
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $this->setFeature('client_ip', $_SERVER["HTTP_X_FORWARDED_FOR"], 'product_info');
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $this->setFeature('client_ip', $_SERVER["HTTP_CLIENT_IP"], 'product_info');
        }
        
        /** sets the server infos */
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false) {
                $server['VERSION'] = 1;
                if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache/2') !== false) {
                    $server['VERSION'] = 2;
                }
                $server['SERVER'] = 'apache';
            }
            
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
                $server['SERVER'] = 'iis';
            }
            
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'Unix') !== false) {
                $server['OS'] = 'unix';
                if (isset($_ENV['MACHTYPE'])) {
                    if (strpos($_ENV['MACHTYPE'], 'linux') !== false) {
                        $server['OS'] = 'linux';
                    }
                }
            } elseif (strpos($_SERVER['SERVER_SOFTWARE'], 'Win') !== false) {
                $server['OS'] = 'windows';
            }
            
            if (preg_match('/Apache\/([0-9\.]*)/', $_SERVER['SERVER_SOFTWARE'], $arr)) {
                if ($arr[1]) {
                    $server['VERSION'] = $arr[1];
                    $server['SERVER'] = 'apache';
                }
            }
        }
        
        $this->setFeature('php_version', phpversion(), 'server_info');
        $this->setFeature('server_os', $server['SERVER'], 'server_info');
        $this->setFeature('server_os_version', $server['VERSION'], 'server_info');
        $this->setFeature('server_http_accept', $_SERVER["HTTP_ACCEPT"], 'server_info');
        $this->setFeature('server_http_accept_language', $_SERVER["HTTP_ACCEPT_LANGUAGE"], 'server_info');
        $this->setFeature('server_ip', $_SERVER["SERVER_ADDR"], 'server_info');
        $this->setFeature('server_name', $_SERVER["SERVER_NAME"], 'server_info');
    
    }

    /**
     * Extract and sets informations from the User Agent chain
     *
     * @static
     * @access public
     * @param string $userAgent User Agent chain
     * @return void
     */
    public function extractFromUserAgent ($userAgent)
    {
        $userAgent = trim($userAgent);
        
        //$pattern = "(([^/]*)(/([^[:space:]]*))?)";
        $pattern = "(([^/\\(]*)(/([^[:space:]]*))?)"; // a valider
        $pattern .= "([[:space:]]*\[[a-zA-Z][a-zA-Z]\])?" . "[[:space:]]*";
        $pattern .= "(\\((([^()]|(\\([^()]*\\)))*)\\))?" . "[[:space:]]*";
        ereg($pattern, $userAgent, $match);
        
        $comment = explode(';', $match[7]);
        /** second part if exists */
        $end = substr($userAgent, strlen($match[0]));
        if (! empty($end)) {
            $result['others']['full'] = $end;
        }
        
        $match2 = array();
        if (isset($result['others'])) {
            preg_match_all('/(([^\/\s]*)(\/)?([^\/\(\)\s]*)?)(\s\((([^\)]*)*)\))?/i', $result['others']['full'], $match2);
        }
        $result['user_agent'] = trim($match[1]);
        $result['product_name'] = trim($match[2]);
        $result['browser_name'] = trim($match[2]);
        if (trim($match[4])) {
            $result['product_version'] = trim($match[4]);
            $result['browser_version'] = trim($match[4]);
        }
        if (! empty($comment[0])) {
            $result['comment']['full'] = trim($match[7]);
            $result['comment']['detail'] = $comment;
            $result['compatibility_flag'] = trim($comment[0]);
            if (isset($comment[1])) {
                $result['browser_token'] = trim($comment[1]);
            }
            if (isset($comment[2])) {
                $result['device_os_token'] = trim($comment[2]);
            }
        }
        if (empty($result['device_os_token']) && ! empty($result['compatibility_flag'])) {
            /** some browsers does not have platform token */
            $result['device_os_token'] = $result['compatibility_flag'];
        }
        if ($match2) {
            $i = 0;
            $max = count($match2[0]);
            for ($i = 0; $i < $max; $i ++) {
                if (! empty($match2[0][$i])) {
                    $result['others']['detail'][] = array(
                        $match2[0][$i] , 
                        $match2[2][$i] , 
                        $match2[4][$i]
                    );
                }
            }
        }
        
        /** Security level */
        $security = array(
            'N' => 'no security' , 
            'U' => 'strong security' , 
            'I' => 'weak security'
        );
        if (! empty($result['browser_token'])) {
            if (isset($security[$result['browser_token']])) {
                $result['security_level'] = $security[$result['browser_token']];
                unset($result['browser_token']);
            }
        }
        
        $product = strtolower($result['browser_name']);
        
        /** Mozilla : true && false */
        $compatibleOrIe = false;
        if (isset($result['compatibility_flag']) && isset($result['comment'])) {
            $compatibleOrIe = ($result['compatibility_flag'] == 'compatible' || strpos($result['comment']['full'], "MSIE") !== false);
        }
        if ($product == 'mozilla' && $compatibleOrIe) {
            if (! empty($result['browser_token'])) {
                /** classical Mozilla chain */
                preg_match_all('/([^\/\s].*)(\/|\s)(.*)/i', $result['browser_token'], $real);
            } else {
                /** MSIE specific chain with 'Windows' compatibility flag */
                foreach ($result['comment']['detail'] as $v) {
                    if (strpos($v, 'MSIE') !== false) {
                        $real[0][1] = trim($v);
                        $result['browser_engine'] = "MSIE";
                        $real[1][0] = "Internet Explorer";
                        $temp = explode(' ', trim($v));
                        $real[3][0] = $temp[1];
                    
                    }
                    if (strpos($v, 'Win') !== false) {
                        $result['device_os_token'] = trim($v);
                    }
                }
            }
            
            if (! empty($real[0])) {
                $result['browser_name'] = $real[1][0];
                $result['browser_version'] = $real[3][0];
            } else {
                $result['browser_name'] = $result['browser_token'];
                $result['browser_version'] = '??';
            }
        } elseif ($product == 'mozilla' && $result['browser_version'] < 5.0) {
            /* handles the real Mozilla (or old Netscape if version < 5.0) */
            $result['browser_name'] = 'Netscape';
        }
        
        /** windows */
        if ($result['browser_name'] == 'MSIE') {
            $result['browser_engine'] = 'MSIE';
            $result['browser_name'] = 'Internet Explorer';
        }
        if (isset($result['device_os_token'])) {
            if (strpos($result['device_os_token'], 'Win') !== false) {
                
                $windows = array(
                    'Windows NT 6.1' => 'Windows 7' , 
                    'Windows NT 6.0' => 'Windows Vista' , 
                    'Windows NT 5.2' => 'Windows Server 2003' , 
                    'Windows NT 5.1' => 'Windows XP' , 
                    'Windows NT 5.01' => 'Windows 2000 SP1' , 
                    'Windows NT 5.0' => 'Windows 2000' , 
                    'Windows NT 4.0' => 'Microsoft Windows NT 4.0' , 
                    'WinNT' => 'Microsoft Windows NT 4.0' , 
                    'Windows 98; Win 9x 4.90' => 'Windows Me' , 
                    'Windows 98' => 'Windows 98' , 
                    'Win98' => 'Windows 98' , 
                    'Windows 95' => 'Windows 95' , 
                    'Win95' => 'Windows 95' , 
                    'Windows CE' => 'Windows CE'
                );
                if (isset($windows[$result['device_os_token']])) {
                    $result['device_os_name'] = $windows[$result['device_os_token']];
                } else {
                    $result['device_os_name'] = $result['device_os_token'];
                }
            }
        }
        
        /** iphone */
        $apple_device = array(
            'iPhone' , 
            'iPod' , 
            'iPad'
        );
        if (isset($result['compatibility_flag'])) {
            if (in_array($result['compatibility_flag'], $apple_device)) {
                $result['device'] = strtolower($result['compatibility_flag']);
                $result['device_os_token'] = 'iPhone OS';
                $result['browser_language'] = trim($comment[3]);
                $result['browser_version'] = $result['others']['detail'][1][2];
                if (! empty($result['others']['detail'][2])) {
                    $result['firmware'] = $result['others']['detail'][2][2];
                }
                if (! empty($result['others']['detail'][3])) {
                    $result['browser_name'] = $result['others']['detail'][3][1];
                    $result['browser_build'] = $result['others']['detail'][3][2];
                }
            }
        }
        
        /** Safari */
        if (isset($result['others'])) {
            if ($result['others']['detail'][0][1] == 'AppleWebKit') {
                $result['browser_engine'] = 'AppleWebKit';
                if ($result['others']['detail'][1][1] == 'Version') {
                    $result['browser_version'] = $result['others']['detail'][1][2];
                } else {
                    $result['browser_version'] = $result['others']['detail'][count($result['others']['detail']) - 1][2];
                }
                $result['browser_language'] = trim($comment[3]);
                
                $last = $result['others']['detail'][count($result['others']['detail']) - 1][1];
                
                if (empty($result['others']['detail'][2][1]) || $result['others']['detail'][2][1] == 'Safari') {
                    $result['browser_name'] = ($result['others']['detail'][1][1] && $result['others']['detail'][1][1] != 'Version' ? $result['others']['detail'][1][1] : 'Safari');
                    $result['browser_version'] = ($result['others']['detail'][1][2] ? $result['others']['detail'][1][2] : $result['others']['detail'][0][2]);
                } else {
                    $result['browser_name'] = $result['others']['detail'][2][1];
                    $result['browser_version'] = $result['others']['detail'][2][2];
                    /** mobile version */
                    if ($result['browser_name'] == 'Mobile') {
                        $result['browser_name'] = 'Safari ' . $result['browser_name'];
                        if ($result['others']['detail'][1][1] == 'Version') {
                            $result['browser_version'] = $result['others']['detail'][1][2];
                        }
                    }
                }
                /** for Safari < 2.2, AppleWebKit version gives the Safari version */
                if (strpos($result['browser_version'], '.') > 2 || (int) $result['browser_version'] > 20) {
                    $temp = explode('.', $result['browser_version']);
                    $build = (int) $temp[0];
                    $awkVersion = array(
                        48 => '0.8' , 
                        73 => '0.9' , 
                        85 => '1.0' , 
                        103 => '1.1' , 
                        124 => '1.2' , 
                        300 => '1.3' , 
                        400 => '2.0'
                    );
                    foreach ($awkVersion as $k => $v) {
                        if ($build >= $k) {
                            $result['browser_version'] = $v;
                        }
                    }
                }
            }
            
            /** Gecko (Firefox or compatible) */
            if ($result['others']['detail'][0][1] == 'Gecko') {
                $searchRV = true;
                if (! empty($result['others']['detail'][1][1]) && ! empty($result['others']['detail'][count($result['others']['detail']) - 1][2]) || strpos(strtolower($result['others']['full']), 'opera') !== false) {
                    $searchRV = false;
                    $result['browser_engine'] = $result['others']['detail'][0][1];
                    // the name of application is at the end indepenently of quantity of information in $result['others']['detail']
                    $last = count($result['others']['detail']) - 1;
                    /** exception : if the version of the last information is empty we take the previous one */
                    if (empty($result['others']['detail'][$last][2])) {
                        $last --;
                    }
                    /** exception : if the last one is 'Red Hat' or 'Debian' => use rv: to find browser_version */
                    if (in_array($result['others']['detail'][$last][1], array(
                        'Debian' , 
                        'Hat'
                    ))) {
                        $searchRV = true;
                    }
                    $result['browser_name'] = $result['others']['detail'][$last][1];
                    $result['browser_version'] = $result['others']['detail'][$last][2];
                    if (isset($comment[4])) {
                        $result['browser_build'] = trim($comment[4]);
                    }
                    $result['browser_language'] = trim($comment[3]);
                    /** Netscape */
                    if ($result['browser_name'] == 'Navigator' || $result['browser_name'] == 'Netscape6') {
                        $result['browser_name'] = 'Netscape';
                    }
                }
                if ($searchRV) {
                    /** Mozilla alone : the version is identified by rv: */
                    $result['browser_name'] = 'Mozilla';
                    if (isset($result['comment']['detail'])) {
                        foreach ($result['comment']['detail'] as $rv) {
                            if (strpos($rv, 'rv:') !== false) {
                                $result['browser_version'] = trim(str_replace('rv:', '', $rv));
                            }
                        }
                    }
                }
            }
            
            /** Netscape */
            if ($result['others']['detail'][0][1] == 'Netscape') {
                $result['browser_name'] = 'Netscape';
                $result['browser_version'] = $result['others']['detail'][0][2];
            }
            /** Opera */
            /** Opera : engine Presto */
            if ($result['others']['detail'][0][1] == 'Presto') {
                $result['browser_engine'] = 'Presto';
                if (! empty($result['others']['detail'][1][2])) {
                    $result['browser_version'] = $result['others']['detail'][1][2];
                }
            }
            /** ua ends with 'Opera X.XX' */
            if ($result['others']['detail'][0][1] == 'Opera') {
                $result['browser_name'] = $result['others']['detail'][0][1];
                $result['browser_version'] = $result['others']['detail'][1][1];
            }
            
            /** Opera Mini */
            if (isset($result["browser_token"])) {
                if (strpos($result["browser_token"], 'Opera Mini') !== false) {
                    $result['browser_name'] = 'Opera Mini';
                }
            }
            
            /** Symbian */
            if ($result['others']['detail'][0][1] == 'SymbianOS') {
                $result['device_os_token'] = 'SymbianOS';
            }
        }
        /** ua ends with 'Opera X.XX' */
        if (isset($result['browser_name']) && isset($result['browser_engine'])) {
            if ($result['browser_name'] == 'Opera' && $result['browser_engine'] == 'Gecko' && empty($result['browser_version'])) {
                $result['browser_version'] = $result['others']['detail'][count($result['others']['detail']) - 1][1];
            }
        }
        
        /** cleanup */
        if (isset($result['browser_version']) && isset($result['browser_build'])) {
            if ($result['browser_version'] == $result['browser_build']) {
                unset($result['browser_build']);
            }
        }
        //$result['browser_version'] = str_replace('v', '', $result['browser_version']);
        


        /** compatibility */
        $compatibility['AppleWebKit'] = 'Safari';
        $compatibility['Gecko'] = 'Firefox';
        $compatibility['MSIE'] = 'Internet Explorer';
        $compatibility['Presto'] = 'Opera';
        if (! empty($result['browser_engine'])) {
            if (isset($compatibility[$result['browser_engine']])) {
                $result['browser_compatibility'] = $compatibility[$result['browser_engine']];
            }
        }
        
        ksort($result);
        
        return $result;
    }

    /**
     * Loads the Features Adapter if it's defined in the $config array
     * Otherwise, nothing is done
     *
     * @static
     * @access public
     * @param string $browserType Browser type
     * @return array
     */
    static public function loadFeaturesAdapter ($browserType)
    {
        if (isset(Zend_Http_UserAgent::$config[$browserType]['features'])) {
            if (! empty(Zend_Http_UserAgent::$config[$browserType]['features']['classname'])) {
                $className = Zend_Http_UserAgent::$config[$browserType]['features']['classname'];
            } else {
                require_once 'Zend/Http/UserAgent/Exception.php';
                throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' features adapter must have a "classname" config parameter defined');
            }
            if (! class_exists($className)) {
                if (isset(Zend_Http_UserAgent::$config[$browserType]['features']['path'])) {
                    $path = Zend_Http_UserAgent::$config[$browserType]['features']['path'];
                } elseif (isset(Zend_Http_UserAgent::$config[$browserType]['features'])) {
                    require_once 'Zend/Http/UserAgent/Exception.php';
                    throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' features adapter must have a "path" config parameter defined');
                }
                
                try {
                    include_once ($path);
                } catch (Exception $e) {
                    throw new Zend_Http_UserAgent_Exception('The ' . $browserType . ' features adapter path does not exists');
                }
            }
            
            $features = call_user_func_array(array(
                $className , 
                'getFromRequest'
            ), array(
                $_SERVER
            ));
            
            return $features;
        }
    }

    /**
     * __DESC__
     *
     * @access public
     * @return array
     */
    public function getImageFormatSupport ()
    {
        return $this->_images;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getMaxImageHeight ()
    {
        return null;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getMaxImageWidth ()
    {
        return null;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getPhysicalScreenHeight ()
    {
        return null;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getPhysicalScreenWidth ()
    {
        return null;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getPreferredMarkup ()
    {
        return 'xhtml';
    }

    /**
     * __DESC__
     *
     * @access public
     * @return string
     */
    public function getXhtmlSupportLevel ()
    {
        return 4;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return bool
     */
    public function hasFlashSupport ()
    {
        return true;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return bool
     */
    public function hasPdfSupport ()
    {
        return true;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return bool
     */
    public function hasPhoneNumber ()
    {
        return false;
    }

    /**
     * __DESC__
     *
     * @access public
     * @return bool
     */
    public function httpsSupport ()
    {
        return true;
    }
}