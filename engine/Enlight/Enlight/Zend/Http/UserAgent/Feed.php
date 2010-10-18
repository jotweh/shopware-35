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
 * Feed browser type matcher
 *
 * @category   Zend
 * @package    Zend_Browser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Http_UserAgent_Feed extends Zend_Http_UserAgent_AbstractUserAgent
{

    /**
     * User Agent Signatures
     *
     * @static
     * @access public
     * @var array
     */
    static $uaSignatures = array(
        'bloglines' , 
        'everyfeed' , 
        'feedfetcher' , 
        'gregarius'
    );

    /**
     * Comparison of the UserAgent chain and User Agent signatures
     *
     * @static
     * @access public
     * @param string $userAgent User Agent chain
     * @return bool
     */
    static public function match ($userAgent)
    {
        return Zend_Http_UserAgent::match($userAgent, self::$uaSignatures);
    }

    /**
     * Gives the current browser type
     *
     * @access public
     * @return string
     */
    public function getType ()
    {
        return 'feed';
    }

    /**
     * Look for features
     *
     * @access public
     * @return string
     */
    public function defineFeatures ()
    {
        $this->setFeature('iframes', false, 'product_capability');
        $this->setFeature('frames', false, 'product_capability');
        $this->setFeature('javascript', false, 'product_capability');
        return parent::defineFeatures();
    }
}
