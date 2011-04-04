<?php
class	Enlight_Components_Adodb extends Enlight_Class
{
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $db;
	
	/**
	 * @var Zend_Cache_Core
	 */
	protected $cache;

	/**
	 * @var array
	 */
	protected $cacheTags = array();
	
	/**
	 * @var int
	 */
	protected $cacheLifetime = 0;
	
	/**
	 * @var int
	 */
	protected $rowCount;
	
	public function __construct($config)
	{
		if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        if(isset($config['db'])) {
        	$this->db = $config['db'];
        }
        if(isset($config['cache'])) {
        	$this->cache = $config['cache'];
        }
        if(isset($config['cacheTags'])) {
        	$this->cacheTags = $config['cacheTags'];
        }
	}
	
	public function Insert_ID() 
	{ 
		return $this->db->lastInsertId(); 
	}
	
	public function Execute($sql, $bind = array())
	{
		if(empty($bind) 
		  && ltrim(strtoupper(substr($sql, 0, 3))) != 'SEL' && empty($bind)) {
			$this->rowCount = $this->db->exec($sql);
			return $this->rowCount!==false;
		}
		$stm = $this->db->query($sql, $bind);
		$this->rowCount = $stm->rowCount();
		return new Enlight_Components_Adodb_Statement($stm);
	}
	
	public function qstr($value)
	{ 
		return $this->db->quote($value); 
	}
	
	public function quote($value)
	{ 
		return $this->db->quote($value); 
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
		return $this->db->quote($date->toString($this->fmtTimeStamp, 'php'));
	}
	
	public function DBDate($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $this->db->quote($date->toString($this->fmtDate, 'php'));
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
		$error = $this->db->getConnection()->errorInfo();
		return isset($error[2]) ? $error[2] : null;
	}
	
	public function Affected_Rows()
	{
		return $this->rowCount; 
	}
	
	public function GetAll($sql, $bind = array())
	{
		return $this->db->fetchAll($sql, $bind);
	}
	
	public function GetRow($sql, $bind = array())
	{ 
		$result = $this->db->fetchRow($sql, $bind);
		return $result===false ? array() : $result;
	}
	
	public function GetOne($sql, $bind = array())
	{ 
		return $this->db->fetchOne($sql, $bind);
	}
	
	public function GetCol($sql, $bind = array())
	{
		return $this->db->fetchCol($sql, $bind);
	}
	
	public function GetAssoc($sql, $bind = array())
	{ 		
		$stmt = $this->db->query($sql, $bind);
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
		$sql = $this->db->limit($sql, $count, $offset);
		return $this->db->Execute($sql, $bind);
	}
	
	public function CacheExecute($timeout, $sql, $bind = array())
	{ 
		return $this->Execute($sql, $bind); 
	}
	
	public function GetFoundRows()
	{
		return $this->db->fetchOne('SELECT FOUND_ROWS() as count');
	}
	
	public function CacheGetFoundRows()
	{
		if($this->foundRows===null) {
			$this->foundRows = $this->GetFoundRows();
		}
		return $this->foundRows;
	}
	
	protected $foundRows = null;
	
	public function getCacheId($name, $sql, $bind = array())
	{
		return md5($name.$sql.serialize($bind));
	}
	
	public function callCached($name, $timeout, $sql = null, $bind = array(), $tags = array())
	{
		if($sql === null) {
			$sql = $timeout;
			$timeout = null;
		}
		$timeout = $timeout===null ? $this->cacheLifetime : (int) $timeout;
		$tags = (array) $tags + $this->cacheTags;
		$bind = (array) $bind;
		
		$this->foundRows = null;
		
		if($timeout>0 && $this->cache!==null) {
			
			$id = $this->getCacheId($name, $sql, $bind);

			if(strpos($sql, 'SQL_CALC_FOUND_ROWS') !== false) {
				$calcFoundRows = true;
			} else {
				$calcFoundRows = false;
			}
			
			if(!$this->cache->test($id)) {
				$result = $this->$name($sql, $bind);
				if($calcFoundRows) {
					$result = array(
						'rows' => $result,
						'foundRows' => $this->GetFoundRows()
					);
				}
				$this->cache->save($result, $id, $tags, $timeout);
			} else {
				$result = $this->cache->load($id);
			}
			
			if($calcFoundRows && isset($result['rows'])) {
				$this->foundRows = $result['foundRows'];
				$result = $result['rows'];
			}
			
		} else {
			$result = $this->$name($sql, $bind);
		}
		
		return $result;
	}
		
	public function CacheGetAll($timeout, $sql = null, $bind = array(), $tags = array())
	{ 
		return $this->callCached('GetAll', $timeout, $sql, $bind, $tags);
	}
	
	public function CacheGetRow($timeout, $sql = null, $bind = array(), $tags = array())
	{ 
		return $this->callCached('GetRow', $timeout, $sql, $bind, $tags);
	}
	
	public function CacheGetOne($timeout, $sql = null, $bind = array(), $tags = array())
	{ 
		return $this->callCached('GetOne', $timeout, $sql, $bind, $tags);
	}
	
	public function CacheGetCol($timeout, $sql = null, $bind = array(), $tags = array())
	{ 
		return $this->callCached('GetCol', $timeout, $sql, $bind, $tags);
	}
	
	public function CacheGetAssoc($timeout, $sql = null, $bind = array(), $tags = array())
	{ 
		return $this->callCached('GetAssoc', $timeout, $sql, $bind, $tags);
	}
}