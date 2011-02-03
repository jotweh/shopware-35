<?php
class Enlight_Components_Adodb extends Enlight_Class
{
	/**
	 * Enter description here...
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db = array();
	
	/**
	 * Enter description here...
	 *
	 * @var Zend_Cache_Core
	 */
	protected $_cache;

	protected $_cacheTags = array();
	protected $_cacheLifetime = 0;
	
	protected $_rowCount;
	
	public function __construct($config)
	{
		if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        if(isset($config['db'])) {
        	$this->_db = $config['db'];
        }
        if(isset($config['cache'])) {
        	$this->_cache = $config['cache'];
        }
        if(isset($config['cacheTags'])) {
        	$this->_cacheTags = $config['cacheTags'];
        }
	}
	
	public function Insert_ID() 
	{ 
		return $this->_db->lastInsertId(); 
	}
	
	public function Execute($sql, $bind = array())
	{
		$stm = $this->_db->query($sql, $bind);
		$this->_rowCount = $stm->rowCount();
		return new Enlight_Components_Adodb_Statement($stm);
	}
	
	public function qstr($value)
	{ 
		return $this->_db->quote($value); 
	}
	
	public function quote($value)
	{ 
		return $this->_db->quote($value); 
	}
	
	public function Param($value)
	{ 
		return '?';
	}
	
	public $sysDate = 'CURDATE()';
	public $sysTimeStamp = 'NOW()';
	protected $fmtDate = "Y-m-d";
	protected $fmtTimeStamp = "Y-m-d H:i:s";
	
	public function OffsetDate($dayFraction, $date=null)
	{		
		if (empty($date)) {
			$date = $this->sysDate;
		}
		$fraction = $dayFraction * 24 * 3600;
		return '('. $date . ' + INTERVAL ' .	 $fraction.' SECOND)';
	}
	
	public function DBTimeStamp($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $this->_db->quote($date->toString($this->fmtTimeStamp, 'php'));
	}
	
	public function DBDate($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $this->_db->quote($date->toString($this->fmtDate, 'php'));
	}
	
	public function BindTimeStamp($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $date->toString($this->fmtTimeStamp, 'php');
	}
	
	public function LogSQL($enable=true)
	{
		return true;
	}
	
	public function ErrorMsg()
	{
		$error = $this->_db->getConnection()->errorInfo();
		return isset($error[2]) ? $error[2] : null;
	}
	
	public function Affected_Rows()
	{
		return $this->_rowCount; 
	}
	
	public function GetAll($sql, $bind = array())
	{ 
		return $this->_db->fetchAll($sql, $bind);
	}
	
	public function GetRow($sql, $bind = array())
	{ 
		$result = $this->_db->fetchRow($sql, $bind);
		return $result===false ? array() : $result;
	}
	
	public function GetOne($sql, $bind = array())
	{ 
		return $this->_db->fetchOne($sql, $bind);
	}
	
	public function GetCol($sql, $bind = array())
	{
		return $this->_db->fetchCol($sql, $bind);
	}
	
	public function GetAssoc($sql, $bind = array())
	{ 		
		$stmt = $this->_db->query($sql, $bind);
		$data = array();
		if($stmt->columnCount()==2)
		{
			while ($row = $stmt->fetch(Zend_Db::FETCH_NUM))
			{
	            $data[$row[0]] = $row[1];
	        }
		}
		elseif($stmt->columnCount() > 2)
		{ 
			while($row = $stmt->fetch()) 
			{ 
				$row_id = array_shift($row); 
				$data[$row_id] = $row; 
			}
		}
		return $data; 
	}
	
	public function SelectLimit($sql, $count, $offset = 0, $bind = array())
	{
		$sql = $this->_db->limit($sql, $count, $offset);
		return $this->_db->Execute($sql, $bind);
	}
	
	public function CacheExecute($timeout, $sql, $bind = array())
	{ 
		return $this->Execute($sql, $bind); 
	}
		
	public function __call($name, $params=array())
	{
		switch ($name) {
			case 'CacheGetAll':
			case 'CacheGetRow':
			case 'CacheGetOne':
			case 'CacheGetCol':
			case 'CacheGetAssoc':
				
				if(!isset($params[1])||$params[1]===null) {
					$sql = $params[0];
					$timeout = $this->_cacheLifetime;
				} else {
					$sql = $params[1];
					$timeout = $params[0];
				}
				$bind = isset($params[2]) ? $params[2] : array();
				$name = substr($name, 5);
				
				
				if($timeout>0&&$this->_cache!==null) {
					$id = md5($name.$sql.serialize($bind));
					$tags = $this->_cacheTags;
					if (!empty($params[3])){
						$tags[] = $params[3];
					}
					if(!$this->_cache->test($id)) {
						$result = $this->$name($sql, $bind);
						$this->_cache->save($result, $id, $tags, $timeout);
					} else {
						$result = $this->_cache->load($id);
					}
				} else {
					$result = $this->$name($sql, $bind);
				}
				
				return $result;
			default:
				return parent::__call($name, $params);
		}
	}
}

class Enlight_Components_Adodb_Statement extends Enlight_Class
{
	protected $_statement;
	
	public $fields = array();
	public $EOF = true;
	
	public function __construct($statement)
    {
        $this->_statement = $statement;
        $this->MoveNext();
    }
		
	public function RecordCount()
	{
		return $this->_statement->rowCount();
	}
	
	public function MoveNext()
	{
		if($this->_statement->columnCount()) {
			$this->fields = $this->_statement->fetch();
			$this->EOF = $this->fields===false;
		}
	}
	
	public function FetchRow()
	{
		if($this->fields) {
			$result = $this->fields;
			$this->fields = array();
			return $result;
		}
		return $this->_statement->fetch(Zend_Db::FETCH_ASSOC);
	}
	
	public function Close()
	{
		return $this->_statement->closeCursor();
	}
}