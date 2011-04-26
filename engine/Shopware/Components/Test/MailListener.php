<?php
/**
 * Shopware Mail Test Listener
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Components
 */
class Shopware_Components_Test_MailListener implements PHPUnit_Framework_TestListener
{
	protected $mailTransport;
	protected $mailRecipients;
	
	/**
	 * Constructor method
	 *
	 * @param unknown_type $mailRecipients
	 * @param unknown_type $mailTransport
	 */
	public function __construct($mailRecipients, $mailTransport = array())
    {
    	if(!$mailTransport instanceof Zend_Mail_Transport_Abstract) {
	    	if(empty($mailTransport['type'])) {
	    		$mailTransport['type'] = 'sendmail';
	    	}
	    	if(!Shopware()->Loader()->loadClass($mailTransport['type'])) {
				$transportName = ucfirst(strtolower($mailTransport['type']));
				$transportName = 'Zend_Mail_Transport_'.$transportName;
			}
			if($transportName=='Zend_Mail_Transport_Smtp') {
				$mailTransport = Enlight_Class::Instance($transportName, array($mailTransport['host'], $mailTransport));
			} else {
				$mailTransport = Enlight_Class::Instance($transportName, array($mailTransport));
			}
    	}
    	$this->mailTransport = $mailTransport;
    	$this->mailRecipients = explode(',', $mailRecipients);
    }
    
    /**
     * Adds an error to the list of errors.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message .= "\n".$e->getMessage();
        
        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
        	$message .= "\n".$e->getCustomMessage();
        }
        
        $name = $test->getName(false);
        
        $mail = new Enlight_Components_Mail();
        
        $mail->addTo($this->mailRecipients);
        $mail->setSubject('PHPUnit test "' . $name . '" failed.');
        $mail->setBodyText($message);
        
        $mail->send($this->mailTransport);
    }
    
    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    	$this->addError($test, $e, $time);
    }
    
    /**
     * Incomplete test method.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    	
    }

    /**
     * Skipped test method.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    	
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    	
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    	
    }

    /**
     * A test started method.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
    	
    }

    /**
     * A test ended method.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    	
    }
}