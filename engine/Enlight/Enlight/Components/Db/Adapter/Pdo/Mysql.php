<?php
class Enlight_Components_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
	/*
	protected $_defaultStmtClass = 'Enlight_Components_Db_Statement_Pdo';
	protected $_rowCount;
	
	public function Insert_ID() 
	{ 
		return $this->lastInsertId(); 
	}
	public function Execute($sql, $bind = array())
	{
		$stm = $this->query($sql, $bind);
		$stm->MoveNext();
		$this->_rowCount = $stm->rowCount();
		return $stm;
	}
	public function qstr($value)
	{ 
		return $this->quote($value); 
	}
	public function Param($value)
	{ 
		return '?';
	}
	
	public $sysDate = 'CURDATE()';
	public $sysTimeStamp = 'NOW()';
	public function OffsetDate($dayFraction, $date=null)
	{		
		if (empty($date)) $date = $this->sysDate;
		
		$fraction = $dayFraction * 24 * 3600;
		return '('. $date . ' + INTERVAL ' .	 $fraction.' SECOND)';
	}
	public $fmtDate = "Y-m-d";
	public $fmtTimeStamp = "Y-m-d H:i:s";
	public function DBTimeStamp($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $this->quote($date->toString($this->fmtTimeStamp, 'php'));
	}
	public function DBDate($timestamp)
	{
		if(empty($timestamp) && $timestamp!==0) return 'null';
		$date = new Zend_Date($timestamp);
		return $this->quote($date->toString($this->fmtDate, 'php'));
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
		$error = $this->getConnection()->errorInfo();
		return isset($error[2]) ? $error[2] : null;
	}
	public function Affected_Rows()
	{
		return $this->_rowCount; 
	}
	
	public function GetAll($sql, $bind = array())
	{ 
		return $this->fetchAll($sql, $bind);
	}
	public function GetRow($sql, $bind = array())
	{ 
		$result = $this->fetchRow($sql, $bind);
		return $result===false ? array() : $result;
	}
	public function GetOne($sql, $bind = array())
	{ 
		return $this->fetchOne($sql, $bind);
	}
	public function GetCol($sql, $bind = array())
	{
		return $this->fetchCol($sql, $bind);
	} 
	public function GetAssoc($sql, $bind = array())
	{ 		
		$stmt = $this->query($sql, $bind);
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
		$sql = $this->limit($sql, $count, $offset);
		return $this->Execute($sql, $bind);
	}
	
	public function CacheExecute($timeout, $sql, $bind = array())
	{ 
		return $this->Execute($sql, $bind); 
	}
	public function CacheGetAll($timeout, $sql=null, $bind = array())
	{ 
		if(!isset($sql)) $sql = $timeout;
		return $this->fetchAll($sql, $bind);
	}
	public function CacheGetRow($timeout, $sql=null, $bind = array())
	{ 
		if(!isset($sql)) $sql = $timeout;
		return $this->fetchRow($sql, $bind);
	}
	public function CacheGetOne($timeout, $sql, $bind = array())
	{ 
		return $this->fetchOne($sql, $bind);
	}
	public function CacheGetCol($timeout, $sql, $bind = array())
	{
		return $this->fetchCol($sql, $bind);
	}
	public function CacheGetAssoc($timeout, $sql, $bind = array())
	{
		return $this->GetAssoc($sql, $bind);
	}
	*/
}
/*
class Enlight_Components_Db_Statement_Pdo extends Zend_Db_Statement_Pdo
{
	public $fields = array();
	public $EOF = true;
		
	public function RecordCount()
	{
		return $this->rowCount();
	}
	
	public function MoveNext()
	{
		if($this->columnCount()) {
			$this->fields = $this->fetch();
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
		return $this->fetch(Zend_Db::FETCH_ASSOC);
	}
	
	public function Close()
	{
		return $this->closeCursor();
	}
}
*/