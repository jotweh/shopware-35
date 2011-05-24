<?php
class	Shopware_Components_File_Adapter_Zip extends Shopware_Components_File_Adapter
{
	protected $stream;
	
	public function __construct($fileName=null, $flags=null)
	{
		if (!extension_loaded('zip')) {
            throw new Exception('The zip extension are required');
        }
		$this->position = 0;
		$this->stream = new ZipArchive();
		if($fileName!=null) {
			$res = @$this->stream->open($fileName, $flags);
			if($res !== true) {
				throw new Exception($this->stream->getStatusString());
			}
		}
		$this->count = $this->stream->numFiles;
	}
	
	public function current()
	{
		return new Shopware_Components_Zip_Item($this, $this->position);
	}
	
	public function getStream($name)
	{
		return $this->stream->getStream($name);
	}
	
	public function getContent($name)
	{
		return $this->stream->getFromName($name);
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