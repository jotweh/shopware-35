<?php
class Enlight_Components_Mail extends Zend_Mail
{
	protected $_isHtml = false;
	protected $_fromName = null;
	protected $_plainBody = null;
	protected $_plainBodyText = null;
	
	public function IsHTML($isHtml = true)
	{
		$this->_isHtml = (bool) $isHtml;
	}
	
	public function AddAddress($email, $name = '')
	{
		return $this->addTo($email, $name);
	}
	
	public function ClearAddresses()
	{
		return $this->clearRecipients();
	}
	
	public function addAttachment($attachment)
    {
    	if(!$attachment instanceof Zend_Mime_Part)
    	{
    		if(func_num_args()>1) {
    			$filename = func_get_arg(1);
    		} else {
    			$filename = basename($attachment);
    		}
    		$this->createAttachment(
    			file_get_contents($attachment),
    			Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $filename
            );
            return $this;
    	}
        return parent::addAttachment($attachment);
    }
	
	public function setFrom($email, $name = null)
    {
    	$this->_fromName = $name;
    	return parent::setFrom($email, $name);
    }
    
    public function clearFrom()
    {
    	$this->_fromName = null;
    	return parent::clearFrom();
    }
    
    public function getFromName()
    {
    	return $this->_fromName;
    }
	
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'From':
				$fromName = $this->getFromName();
				$this->clearFrom();
				$this->setFrom($value, $fromName);
				break;
			case 'FromName':
				$from = $this->getFrom();
				$this->clearFrom();
				$this->setFrom($from, $value);
				break;
			case 'Subject':
				$this->clearSubject();
				$this->setSubject($value);
				break;
			case 'Body':
				$this->_plainBody = $value;
				if($this->_isHtml) {
					$this->setBodyHtml($value);
				} else {
					$this->setBodyText($value);
				}
				break;
			case 'AltBody':
				$this->_plainBodyText = $value;
				if($this->_isHtml) {
					$this->setBodyText($value);
				}
				break;
		}
	}
	
	public function __get($name)
	{
		switch ($name)
		{
			case 'From':
				return $this->getFrom();
				break;
			case 'FromName':
				return $this->getFromName();
				break;
			case 'Subject':
				return $this->getSubject();
				break;
			case 'Body':
				return $this->_plainBody;
				break;
			case 'AltBody':
				return $this->_plainBodyText;
				break;
		}
	}
}