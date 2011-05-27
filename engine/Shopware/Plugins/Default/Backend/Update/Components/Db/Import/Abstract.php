<?php
abstract class	Shopware_Components_Db_Import_Abstract implements SeekableIterator, Countable
{
	protected $count;
	protected $stream;
	protected $position;
	protected $current;
	
	public function __construct($filename)
	{
		$this->stream = @fopen($filename, 'rb');
		if(!$this->stream) {
			throw new Exception('Dump can\'t open failure');
		}
		$this->position = 0;
		$this->count = 0;
		while (!feof($this->stream)) {
			$this->fetch();
			$this->count++;
		}
		$this->rewind();
	}
	
	abstract public function fetch();
	
	public function seek($position)
	{
		while($this->position < $position) {
			$this->next();
		}
	}
	
	public function count()
	{
		return $this->count;
	}
	
	public function rewind()
	{
		rewind($this->stream);
		$this->next();
		$this->position = 0;
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
		$this->fetch();
		++$this->position;
	}

	public function valid()
	{
		return !feof($this->stream);
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
}