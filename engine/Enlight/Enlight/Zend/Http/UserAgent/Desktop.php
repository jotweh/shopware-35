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
require_once 'Zend/Http/UserAgent/AbstractUserAgent.php';

/**
 * Desktop browser type matcher
 *
 * @category   Zend
 * @package    Zend_Browser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Http_UserAgent_Desktop extends Zend_Http_UserAgent_AbstractUserAgent
{

    public static $browserFamily = array(
        'mozilla' => array(
            'bonecho' , 
            'camino' , 
            'epiphany' , 
            'firebird' , 
            'firefox' , 
            'flock' , 
            'galeon' , 
            'iceape' , 
            'icecat' , 
            'iceweasel' , 
            'k-meleon' , 
            'mozilla' , 
            'minefield' , 
            'minimo' , 
            'multizilla' , 
            'netscape' , 
            'netscape6' , 
            'phoenix' , 
            'seamonkey' , 
            'shiretoko' , 
            'songbird' , 
            'swiftfox'
        ) , 
        'webkit' => array(
            'applewebkit' , 
            'arora' , 
            'chrome' , 
            'epiphany' , 
            'gtklauncher' , 
            'konqueror' , 
            'midori' , 
            'omniweb' , 
            'safari' , 
            'uzbl' , 
            'webkit'
        )
    );

    /**
     * Used by default : must be always true
     *
     * @static
     * @access public
     * @param string $userAgent User Agent chain
     * @return bool
     */
    static public function match ($userAgent)
    {
        return true;
    }

    /**
     * Gives the current browser type
     *
     * @access public
     * @return string
     */
    public function getType ()
    {
        return 'desktop';
    }

    /**
     * Look for features : The common features are already defined in Zend_Http_UserAgent_Abstract
     *
     * @access public
     * @return string
     */
    public function defineFeatures ()
    {
        /*
        $this->setFeature('resolution_width');
        $this->setFeature('resolution_height');
        $this->setFeature('xhtml_support_level');
        $this->setFeature('preferred_markup');
        */
        return null;
    }
}
