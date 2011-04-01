<?php
/**
 * Enlight Auth Adapter
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @subpackage Components
 */
class Enlight_Components_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
	protected $expiryColumn;
	protected $expiry;
	protected $sessionId;
	protected $sessionIdColumn;
	
	/**
	 * Add condition method
	 *
	 * @param string $condition
	 * @return Enlight_Components_Auth_Adapter_DbTable
	 */
	public function addCondition($condition)
	{
		$this->getDbSelect()->where($condition);
		return $this;
	}
	
	/**
	 * Set expiry column method
	 *
	 * @param string $expiryColumn
	 * @param int $expiry
	 * @return Enlight_Components_Auth_Adapter_DbTable
	 */
	public function setExpiryColumn($expiryColumn, $expiry=3600)
	{
		$this->expiryColumn = $expiryColumn;
		$this->expiry = $expiry;
		return $this;
	}
	
	/**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
	public function authenticate()
	{
		$result = parent::authenticate();
		if($result->isValid()) {
			$this->updateExpiry();
			$this->updateSessionId();
		}
		return $result;
	}
	
	/**
	 * Update expiry method
	 */
	protected function updateExpiry()
	{
		if($this->expiryColumn === null) {
			return;
		}
		
		$this->_zendDb->update($this->_tableName, array(
			$this->expiryColumn => Zend_Date::now()
		), $this->_zendDb->quoteInto(
			$this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?',
			$this->_identity
		));
	}
	
	/**
	 * Update session id method
	 */
	protected function updateSessionId()
	{
		if($this->sessionId === null) {
			return;
		}
		$this->_zendDb->update($this->_tableName, array(
			$this->sessionIdColumn => $this->sessionId
		), $this->_zendDb->quoteInto(
			$this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?',
			$this->_identity
		));
	}
	
	/**
	 * Refresh auth metod
	 *
	 * @return Zend_Auth_Result
	 */
    public function refresh()
    {
    	$credential = $this->_credential;
    	$credentialColumn = $this->_credentialColumn;
    	$identity = $this->_identity;
    	$identityColumn = $this->_identityColumn;
    	$credentialTreatment = $this->_credentialTreatment;
    	
    	$expiry = Zend_Date::now()->subSecond($this->expiry);
    	$this->setCredential($expiry);
    	$this->setCredentialColumn($this->expiryColumn);
    	$expiryColumn = $this->_zendDb->quoteIdentifier($this->expiryColumn, true);
    	$this->setCredentialTreatment('IF('.$expiryColumn.'>=?, '.$expiryColumn.', NULL)');
    	
    	$this->setIdentity($this->sessionId);
    	$this->setIdentityColumn($this->sessionIdColumn);
    	    	
    	$result = parent::authenticate();

    	$this->_credential = $credential;
    	$this->_credentialColumn = $credentialColumn;
    	$this->_identity = $identity;
    	$this->_identityColumn = $identityColumn;
    	$this->_credentialTreatment = $credentialTreatment;
    	
    	if($result->isValid()) {
			$this->updateExpiry();
		}
		    	    	
    	return $result;
    }
    
    /**
     * Set session id column
     *
     * @param string $sessionIdColumn
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setSessionIdColumn($sessionIdColumn)
    {
        $this->sessionIdColumn = $sessionIdColumn;
        return $this;
    }
    
    /**
     * Set session id method
     *
     * @param string $value
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setSessionId($value)
    {
        $this->sessionId = $value;
        return $this;
    }
}