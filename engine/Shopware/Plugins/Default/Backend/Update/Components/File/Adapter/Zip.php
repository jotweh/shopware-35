<?php
class	Shopware_Components_Zip implements SeekableIterator, Countable
{
	protected $position;
	protected $stream;
	
	public function __construct($filename=null, $flags=null)
	{
		if (!extension_loaded('zip')) {
            throw new Exception('The zip extension are required');
        }
		$this->position = 0;
		$this->stream = new ZipArchive();
		if($filename!=null) {
			$res = @$this->stream->open($filename, $flags);
			if($res!==true) {
				throw new Exception($this->stream->getStatusString(), $res);
			}
		}
	}
	
	public function seek($position)
	{
		$this->position = (int) $position;
	}
	
	public function count()
	{
		return $this->stream->numFiles;
	}

	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return new Shopware_Components_Zip_Item($this, $this->position);
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return $this->stream->numFiles>$this->position;
	}
	
	public function statIndex($position)
	{
		return $this->stream->statIndex($position);
	}
	
	public function getStream($name)
	{
		return $this->stream->getStream($name);
	}
	
	public function getFromName($name, $flags=null)
	{
		return $this->stream->getFromName($name, $flags);
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

class	Shopware_Components_Zip_Item
{
	protected $position;
	protected $stream;
	protected $stat;
	
	public function __construct($stream, $position)
	{
		$this->position = $position;
		$this->stream = $stream;
		$this->stat = $stream->statIndex($position);
	}
	
	public function __get($name)
	{
		return isset($this->stat[$name]) ? $this->stat[$name] : null;
	}
	
	public function getStream()
	{
		return $this->stream->getStream($this->name);
	}
	
	public function getContent()
	{
		return $this->stream->getFromName($this->name);
	}
	
	public function isDir()
	{
		return substr($this->name, -1)==='/';
	}
	
	public function isFile()
	{
		return substr($this->name, -1)!=='/';
	}
}