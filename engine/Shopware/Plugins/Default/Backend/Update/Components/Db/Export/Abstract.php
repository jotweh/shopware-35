<?php
abstract	class Shopware_Components_Db_Export_Abstract implements SeekableIterator
{
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $db;
	protected $step = 10;
	protected $position;
	protected $table;
	protected $fields;
	protected $current;
	
	public function __construct(Zend_Db_Adapter_Abstract $db, $table)
	{
		$this->db = $db;
		$this->table = $table;
		$this->fields = $db->describeTable($table);
		
		$this->rewind();
	}
	
	public function seek($position)
	{
		$this->position = (int) $position;
		$this->fetch();
	}
	
	public function rewind()
	{
		$this->position = 0;
		$this->fetch();
	}

	public function current()
	{
		return $this->current;
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
		$this->fetch();
	}

	public function valid()
	{
		return $this->current!==false;
	}
	
	public function fetch()
	{
		if(!$this->position) {
			$this->current = $this->createTable();
		} else {
			$offset = $this->position==1 ? 0 : ($this->position-1) * $this->step;
			$this->current = $this->createData($this->step, $offset);
		}
	}
	
	public function each()
	{
		if(!$this->valid()) {
			return false;
		}
		$result = array($this->key(), $this->current());
		$this->next();
		return $result;
	}
	
	abstract public function createData($limit, $offset=0);

	abstract public function createTable($newTable = null);
}