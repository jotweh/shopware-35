<?php
/**
 * Stellt die Shopwware API zur Verfügung
 *
 */
class Shopware_Plugins_Core_Api_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{	
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Api',
	 		'onInitResourceApi'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
	
	public static function onInitResourceApi(Enlight_Event_EventArgs $args)
	{
		$api = new sAPI();
		$api->sSystem = Shopware()->System();
		$api->sDB = Shopware()->Adodb();
		$api->sPath = Shopware()->OldPath();
		
		return $api;
	}
}

class sAPI
{
    /**
     * Zugriff auf adoDB-Objekt
     * @access public
     * @var object
     */
	var $sDB;
	 /**
     * Enthält absoluten Pfad zur Shopware Installation
     * @access public
     * @var string
     */
	var $sPath;
	 /**
     * ???
     * @access public
     * @var string
     */
	var $sFiles;
	 /**
     * Zugriff auf Shopware System-Klasse
     * @access public
     * @var object
     */
	var $sSystem;
	 /**
     * Enthält Fehlermeldungen
     * @access public
     * @var array
     */
	var $sErrors = array();
	 /**
     * Zugriff auf verschiedene Sub-Objekte
     * @access public
     * @var array
     */
	var $sResource = array();

	function Import(){
		return $this->import->shopware;
	}
	
	function Export(){
		return $this->export->shopware;
	}
	/**
	  * Lädt externe Daten und speichert diese in einem File-Cache
	  * Derzeit wird ausschließlich das HTTP Protokoll unterstützt
	  * @param string $url Der Pfad (inkl. Protokoll) zur Datei
	  * @access public
	  */
	function load ($url)
	{
		$url_array = parse_url($url);
		$url_array['path'] = explode("/",$url_array['path']);
		switch ($url_array['scheme']) {
			case "ftp":
			case "http":
			case "file":
				$hash = "";
				$dir = $this->sPath."/engine/connectors/api/tmp";
				while (empty($hash)) {
					$hash = md5(uniqid(rand(), true));
					if(file_exists("$dir/$hash.tmp"))
						$hash = "";
				}
				if (!$put_handle = fopen("$dir/$hash.tmp", "w+")) {
					return false;
				}
				if (!$get_handle = fopen($url, "r")) {
					return false;
				}
				while (!feof($get_handle)) {
					fwrite($put_handle, fgets($get_handle, 4096));
				}
				fclose($get_handle);
				fclose($put_handle);
				$this->sFiles[] = $hash;
				return "$dir/$hash.tmp";
			default:
				break;
		}
	}
	/**
	  * Garbage-Collector
	  * Nach dem Beenden der API werden temporäre Dateien gelöscht
	  * @access public
	  */
	function __destruct  ()
	{
		if(!empty($this->sFiles))
		foreach ($this->sFiles as $hash) {
			if(file_exists($this->sPath."/engine/connectors/api/tmp/$hash.tmp"))
				@unlink($this->sPath."/engine/connectors/api/tmp/$hash.tmp");
		}
		
	}
	
	/**
	  * Lokales Speichern von Daten - derzeit ohne Funktion
	  * @param string $url Der Pfad (inkl. Protokoll) zur Datei
	  * @access public
	  */
	function save ($url)
	{
		$url_array = parse_url($url);
		$url_array['path'] = explode("/",$url_array['path']);
		switch ($url_array['scheme']) {
			case "ftp":
			case "http":
			case "file":
				break;
			case "post":
				break;
			case "shopware":
				break;
			case "mail":
			case "tcp":
			case "udp":
			case "php":
			default:
				break;
		}
	}
	
	protected $throwError = false;

	function sSetError($message, $code)
	{
		$this->sErrors[] = array('message'=>$message, 'code'=>$code);
    }
    
    function sGetErrors()
    {
    	return $this->sErrors;
    }
    
    function sGetLastError()
    {
    	return end($this->sErrors);
    }
    
    /**
	  * Einbinden von externen Klassen / Objekten
	 * <code>
	 * <?php
	 *	$api = new sAPI();
	 *	$export =& $api->export->shopware;	// Lädt Klasse /api/export/shopware.php
	 *	$xml =& $api->convert->xml;			// Lädt Klasse /api/convert/xml.php
	 *  $mapping =& $api->convert->mapping;	// Lädt Klasse /api/convert/mapping.php
	 *	$xml->sSettings['encoding'] = "ISO-8859-1";
	 * ?>
	 * </code>
	  * @param string $res Enthält den Pfad / Dateinamen des einzubindenen Objekts
	  * @access public
	  */
    function __get ($res)
    {
    	switch ($res)
	   	{
	    	case "sConvert":
	    	case "convert":
	    		$res = "convert"; break;
	    	case "sSave":
	    	case "save":
	    	case "import":
	    		$res = "import"; break;
	    	case "sLoad":
	    	case "load":
	    	case "export":
	    		$res = "export"; break;
	    		break;
	    	default:
	    		return false;
	    }
    	if(!isset($this->sResource[$res]))
		{
	    	$this->sResource[$res] = new sClassHandler($this, $res);
		}
		return $this->sResource[$res];
    }
}

class sClassHandler
{
	private $sAPI = null;
	private $sType = null;
	protected $sClass = array();
	
	function __construct ($sAPI, $sType)
	{
		$this->sType = $sType;
		$this->sAPI = $sAPI;
	}
	function __get  ($class)
	{
		if(!isset($this->sClass[$class]))
		{
			if(!file_exists($this->sAPI->sPath."/engine/connectors/api/{$this->sType}/$class.php"))
				return false;
			include($this->sAPI->sPath."/engine/connectors/api/{$this->sType}/$class.php");
			$name = "s".ucfirst($class).ucfirst($this->sType);
			if(class_exists($name))
				$this->sClass[$class] = new $name;
			elseif(class_exists($class)) 
				$this->sClass[$class] = new $class;
			else 
				return false;
			$this->sClass[$class]->sSystem =& $this->sAPI->sSystem;
			$this->sClass[$class]->sDB =& $this->sAPI->sDB;
			$this->sClass[$class]->sPath =& $this->sAPI->sPath;
			$this->sClass[$class]->sAPI =& $this->sAPI;
		}
		return $this->sClass[$class];
	}

}