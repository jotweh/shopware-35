<?php
/**
 * Update controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Update extends Enlight_Controller_Action
{
	protected $downloadChannel = 'http://www.shopware.de/install/files/download.php';
	protected $versionChannel = 'http://www.shopware.de/install/version.php';
	
	/**
	 * Init controller method
	 */
	public function init()
	{
		Shopware()->Loader()->registerNamespace('Shopware_Models', dirname(dirname(__FILE__)).'/Models/');
		Shopware()->Loader()->registerNamespace('Shopware_Components', dirname(dirname(__FILE__)).'/Components/');
		$this->View()->addTemplateDir(dirname(dirname(__FILE__)).'/Views/');
	}
	
	/**
	 * Pre dispatch method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'detail', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}

	/**
	 * Index action method
	 */
	public function indexAction()
	{
		$this->View()->TableCounts = $this->getTableCounts();
		$this->View()->VersionConfig = $this->getVersionConfig();
	}
	
	/**
	 * Returns table counts
	 *
	 * @return array
	 */
	public function getTableCounts()
	{
		$tables = array();
		$rows = Shopware()->Db()->listTables();
		natsort($rows);
		foreach ($rows as $table) {
			$sql = 'SELECT COUNT(*) FROM '.Shopware()->Db()->quoteIdentifier($table);
			$count = Shopware()->Db()->fetchOne($sql);
			$tables[$table] = $count;
		}
		return $tables;
	}
	
	/**
	 * Returns version config
	 *
	 * @return unknown
	 */
	public function getVersionConfig()
	{
		$config = new Zend_Config_Xml($this->versionChannel, 'update');
		return $config;
	}
	
	/**
	 * Index action method
	 */
	public function skeletonAction()
	{
	}
	
	/**
	 * Returns ftp adapter
	 *
	 * @return Shopware_Components_File_Adapter_Ftp
	 */
	public function Ftp()
	{
		if(empty($_REQUEST['ftp_user'])||!isset($_REQUEST['ftp_password'])
    		||!isset($_REQUEST['ftp_host'])||!isset($_REQUEST['ftp_port'])||!isset($_REQUEST['ftp_path'])) {
			throw new Exception('FTP config missing');
		}
    	
		if(empty($_REQUEST['ftp_host']) || $_REQUEST['ftp_host']=='default') {
			$ftp_host = 'localhost';
		} else {
			$ftp_host = $_REQUEST['ftp_host'];
		}
		if(empty($_REQUEST['ftp_port'])||$_REQUEST['ftp_port']=='default') {
			$ftp_port = Shopware_Components_File_Adapter_Ftp::DEFAULT_PORT;
		} else {
			$ftp_port = $_REQUEST['ftp_port'];
		}
		
		$ftp = new Shopware_Components_File_Adapter_Ftp($ftp_host, $ftp_port, 10);
		$ftp->login($_REQUEST['ftp_user'], $_REQUEST['ftp_password']);
		
		if(empty($_REQUEST['ftp_path'])||$_REQUEST['ftp_path']=='default') {
			$ftp_path = '.';
		} elseif($ftp->isFile($_REQUEST['ftp_path'])) {
			$ftp_path = dirname($_REQUEST['ftp_path']);
		} else {
			$ftp_path = $_REQUEST['ftp_path'];
		}
		$ftp_path = rtrim($ftp_path, "/ \t");
		
		$ftp->chdir($ftp_path);
		
		return $ftp;
	}
	
	/**
	 * Index action method
	 */
	public function ftpPathListAction()
	{
		try {
			$ftp = $this->Ftp();
		} catch (Exception $e) {
			return;
		}
		
		$path = $this->Request()->getParam('node');
		if(!$path || $path=='.') {
			$path = $ftp->pwd();
		}
		$path = rtrim($path, '/') . '/';
		$rows = array(
			array('id'=>$path, 'text'=>'.', 'leaf'=>true)
		);
		
		$list = $ftp->nlist($path);
		if (!empty($list)) {
			natsort($list);
			foreach($list as $value) {
				$value = basename($value);
				if(in_array($value, array('', '.', '..'))) {
					continue;
				}
				$isDir = $ftp->isDir($path.$value);
				$row = array(
					'id' => $path.$value,
					'text' => $value,
					'leaf' => !$isDir
				);
				$rows[] = $row;
			}
		}
		echo Zend_Json::encode($rows);
	}
	
	/**
	 * Index action method
	 */
	public function packageListAction()
	{
		$list = array();
		if (extension_loaded('Zend Optimizer')) {
			$list[] = array('id'=>'zend', 'name'=>'Zend Optimizer');
		}
		if (extension_loaded('ionCube Loader')) {
			$list[] = array('id'=>'ioncube', 'name'=>'ionCube Loader');
		}
		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function formatListAction()
	{
		$list = array();
		if (extension_loaded('zip')) {
			$list[] = array('id'=>'zip', 'name'=>'zip');
		}
		if (extension_loaded('zlib')) {
			$list[] = array('id'=>'tar.gz', 'name'=>'tar.gz');
		}
		$list[] = array('id'=>'tar', 'name'=>'tar');
		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function methodListAction()
	{
		$list = array();
		if(extension_loaded('ftp')) {
			$list[] = array('id'=>'ftp', 'name'=>'FTP');
		}
		if(!ini_get('safe_mode') 
		  && is_writable(Shopware()->OldPath())
		  && is_writable(Shopware()->AppPath())) {
			$list[] = array('id'=>'direct', 'name'=>'Direkt');
		}	
		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}
	
	/**
	 * Returns request format
	 *
	 * @return unknown
	 */
	public function getRequestFormat()
    {
    	$format = $this->Request()->getParam('format');
    	if(empty($format) 
    	  || !in_array($format, array('zip', 'tar.gz', 'tar'))) {
    	  	if (extension_loaded('zip')) {
				$format = 'zip';
			} elseif (extension_loaded('zlib')) {
				$format = 'tar.gz';
			} else {
				$format = 'tar';
			}
    	}
		return $format;
    }
    
    /**
	 * Returns request method
	 *
	 * @return unknown
	 */
    public function getRequestMethod()
    {
    	$format = $this->Request()->getParam('format');
    	if(empty($format) 
    	  || !in_array($format, array('ftp', 'direct'))) {
    	  	if (extension_loaded('ftp')) {
				$format = 'ftp';
			} elseif(!ini_get('safe_mode') 
			  && is_writable(Shopware()->OldPath())
			  && is_writable(Shopware()->AppPath())) {
				$format = 'direct';
			}
    	}
		return $format;
    }
    
    /**
	 * Returns request package
	 *
	 * @return unknown
	 */
    public function getRequestPackage()
    {
    	$package = $this->Request()->getParam('format');
    	if(empty($package) 
    	  || !in_array($format, array('zend', 'ioncube'))) {
    	  	if (extension_loaded('Zend Optimizer')) {
				$package = 'zend';
			} elseif (extension_loaded('ionCube Loader')) {
				$package = 'ioncube';
			}
    	}
		return $package;
    }
	
    /**
	 * Index action method
	 */
	public function backupListAction()
	{
		$backupDir = Shopware()->DocPath('files/backup');
		
		if($delete = $this->Request()->getParam('delete')) {
			$delete = basename($delete);
			if(file_exists($backupDir.$delete)) {
				unlink($backupDir.$delete);
			}
		}
		
		$list = array();
		$iterator = new GlobIterator($backupDir.'database_*.php', FilesystemIterator::SKIP_DOTS);
		foreach ($iterator as $file) {
			$list[] = array(
				'file' => $file->getFilename(),
				'name' => 'Datenbank-Backup',
				'size' => $file->getSize(),
				'added' => $file->getMTime(),
			);
		}
		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function downloadBackupAction()
	{
		$backupDir = Shopware()->DocPath('files/backup');
		$file = $this->Request()->getParam('file');
		$file = basename($file);
		if(!file_exists($backupDir.$file)) {
			return;
		}
		
    	$fp = fopen($backupDir.$file, 'r');
		$size = filesize($backupDir.$file)-strlen(fgets($fp));
		$file = basename($file, '.php').'.sql';
				
		$this->Response()->setHeader('Pragma', 'public');
		$this->Response()->setHeader('Expires', '0');
		$this->Response()->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$this->Response()->setHeader('Cache-Control', 'private', false);
		$this->Response()->setHeader('Content-Type', 'application/force-download');
		$this->Response()->setHeader('Content-Disposition', 'attachment; filename="'.$file.'";');
		$this->Response()->setHeader('Content-Transfer-Encoding', 'binary');
		$this->Response()->setHeader('Content-Length', $size);
		
		echo stream_get_contents($fp);
	}
	
	/**
	 * Index action method
	 */
	public function backupAction()
	{
		$requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$offset = (int) $this->Request()->getParam('offset', 0);
		$backupDir = Shopware()->DocPath('files/backup');
		if(!$tables = $this->Request()->getParam('tables')) {
			$tables = Shopware()->Db()->listTables();
		}
		if(is_string($tables)) {
			$tables = explode(',', $tables);
		}
		if(!$file = $this->Request()->getParam('file')) {
			$file = 'database_'.date('Y-m-d_H-i-s').'.php';
			$fp = fopen($backupDir.$file, 'wb');
			$content = '/*<?php exit(); __halt_compiler(); ?>*/'."\n";
			fwrite($fp, $content);
		} else {
			$fp = fopen($backupDir.$file, 'ab');
		}
		
		$skipTables = array(
			's_articles_translations',
			's_search_index',
			's_search_keywords',
			's_core_log',
			's_core_sessions'
		);
		
		while($table = array_shift($tables)) {

			$export = new Shopware_Components_Db_Export_Sql(Shopware()->Db(), $table);
			$export->seek($offset); $offset = 0;
			while (list($position, $data) = $export->each()) {
				fwrite($fp, $data);
				if(in_array($table, $skipTables)) {
					continue;
				}
				if(time()-$requestTime >= 25 || ($position && $position%10 == 0)) {
					echo Zend_Json::encode(array(
						'message' => 'Exportiere Tabelle "'.$table.'".',
						'success' => true,
						'tables' => $tables + array($table),
						'file' => $file,
						'offset' => $position+1,
					));
					return;
				}
			}
		}
		
		echo Zend_Json::encode(array(
			'message' => 'Datenbank-Export abgeschlossen!',
			'success' => true
		));
	}
	
	/**
	 * Index action method
	 */
	public function downloadAction()
	{
		$offset = (int) $this->Request()->getParam('offset', 0);
		
		$url = $downloadChannel.'?package='.urlencode($package).'&format='.urlencode($format);
		if(!empty($offset)) {
			$url .= '&offset='.(int) $offset;
		}
		
		$options = array(
			'http'=>array(
			'method' => 'GET',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'header' => 'Referer: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\r\n"
		));
		$context = stream_context_create($options);
		
		$source = @fopen($url, 'r', false, $context);
		if(!$source) {
			echo json_encode(array('message'=>'&Ouml;ffnen der Downloaddatei ist fehlgeschlagen.', 'success'=>false));
			return;
		}
		
		if(!empty($offset)) {
			$target = @fopen($downloadFile, 'ab');
		} else {
			$target = @fopen($downloadFile, 'wb+');
		}
		
		while (!feof($source)) {
			
			fwrite($target, fread($source, 8192));

			if(time()-$requestTime >= 10) {
				$offset += ftell($source);
				echo json_encode(array(
					'message'=>' heruntergeladen.',
					'size'=>$offset,
					'success'=>true,
					'step'=>$step,
					'offset'=>$offset,
					'progress'=>0.5
				));
				return;
			}
		}
		
		echo json_encode(array('message'=>'Entpacken der Downloaddatei.', 'success'=>true, 'step'=>$step+1, 'offset'=>0));
	}
	
	/**
	 * Index action method
	 */
	public function prepareAction()
	{
		try {
			$ftp->putContents($this->Config()->downloadFile, '');
			$ftp->changeMode($this->Config()->downloadFile, 0777);
			$ftp->makeDir($this->Config()->backupDir);
			$ftp->changeMode($this->Config()->backupDir, 0777);
			$ftp->makeDir($this->Config()->updateDir);
		} catch (Exception $e) {
			echo json_encode(array('message'=>'Updatedateien konnten nicht angelegt werden.<br />'.htmlentities($e->getMessage()), 'success'=>false));
			return;
		}
		
		echo json_encode(array('message'=>'Herunterladen der Updatedateien.', 'success'=>true, 'step'=>1, 'progress'=>0));
	}
	
	/**
	 * Index action method
	 */
	public function unpackAction()
	{
		$offset = empty($_REQUEST['offset']) ? 0 : (int) $_REQUEST['offset'];

		$zip = new Shopware_Components_Zip($this->Config()->downloadFile);
		$zip->seek($offset);
		
		while (list($position, $entry) = $zip->each()) {
			$name = $entry->name;
													
			if(in_array(basename($name), array('', '.', 'readme.txt'))) {
				continue;
			}

			if($entry->isDir()) {
				if(!file_exists($name)) {
					$ftp->makeDir($name);
					//mkdir($name);
				}
			} else {
				$ftp->put($name, $entry->getStream());
				//file_put_contents($name, $entry->getStream());
			}
			
			if(time()-$requestTime >= 25 || ($position+1)%500==0) {
				
				$count = $zip->count();
				echo json_encode(array(
					'message'=>($position+1).' von '.$count.' Dateien entpackt.',
					'success'=>true,
					'step'=>2,
					'offset'=>$position+1,
					'progress'=>($position+1)/$count
				));
				return;
			}
		}
		echo json_encode(array('message'=>'Backup der Datenbank.', 'success'=>true, 'step'=>3, 'progress'=>0, 'offset'=>0));
	}
	
	/**
	 * Index action method
	 */
	public function importAction()
	{
		$dump = new Shopware_Components_Db_Import_Sql($this->Config()->databaseFile);
		foreach ($dump as $line) {
			try {
				Shopware()->Db()->exec($line);
			} catch (Exception $e) {
				if(in_array($e->getCode(), array(1060, 1061, 1062, 1091))) {
	        		continue;
	        	}
	        	$msg = "Es ist ein Fehler beim Import der Datenbank aufgetreten:<br />\n";
	        	$msg .= htmlentities($e->getMessage())."<br />\n";
	        	$msg .= htmlentities($line);
	        	echo Zend_Json::encode(array('message'=>$msg, 'success'=>false));
				return;
	        }
		}
	}
	
	/**
	 * Index action method
	 */
	public function copyAction()
	{
		try {				
			foreach($this->Config()->backupFiles as $file) {
				if(file_exists($file) && !file_exists($this->Config()->backupDir.$file)) {
					$ftp->rename($file, $this->Config()->backupDir.$file);
				}
			}
			if(!empty($basefile)) {
				if(file_exists($basefile) && !file_exists($this->Config()->backupDir.$basefile)) {
					$ftp->rename($basefile, $this->Config()->backupDir.$basefile);
				}
			}
		} catch (Exception $e) {
			echo json_encode(array('message'=>'Backup ist fehlgeschlagen.<br />'.$e->getMessage(), 'success'=>false));
			return;
		}
		
		try {				
			foreach($this->Config()->updateFiles as $file) {
				if(!file_exists($this->Config()->updateDir.$file)) {
					continue;
				}
				$ftp->rename($this->Config()->updateDir.$file, $file);
			}
		} catch (Exception $e) {
			echo json_encode(array('message'=>'Update ist fehlgeschlagen.<br />'.$e->getMessage(), 'success'=>false));
			return;
		}
	}
	
	/**
	 * Index action method
	 */
	public function finishAction()
	{
		$files = array(
			$this->Config()->downloadFile,
			$this->Config()->databaseFile
		);
		foreach ($files as $file) {
			if(file_exists($file)) {
				@unlink($file);
			}
		}
		echo json_encode(array('success'=>true, 'progress'=>1));
	}
	
	/**
	 * Index action method
	 */
	public function updateAction()
    {
    	$requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
    	$db = $this->Db();
    	
		try {
			$ftp = $this->Ftp();
		} catch (Exception $e) {
			echo json_encode(array('message'=>'Es konnte keine FTP-Verbindung aufgebaut werden.<br />'.htmlentities($e->getMessage()), 'success'=>false));
			return;
		}
    	    	
    	$step = empty($_REQUEST['step']) ? 0 : (int) $_REQUEST['step'];
    	switch ($step) {
			case 0:
				return $this->prepareAction();
			case 1:
				return $this->downloadAction();
			case 2:
				return $this->unpackAction();
			case 3:
				return $this->backupAction();
				echo json_encode(array('message'=>'Update der Datenbank.', 'success'=>true, 'step'=>4, 'progress'=>0));
				return;
			case 4:
				return $this->copyAction();
				echo json_encode(array('message'=>'Update der Dateien.', 'success'=>true, 'step'=>5, 'progress'=>0.9));
				return;
			case 5:
				return $this->unpackAction();					
				echo json_encode(array('message'=>'L&ouml;schen der Updatedateien.', 'success'=>true, 'step'=>6, 'progress'=>0.8));
				return;
			case 6:
				return $this->finishAction();
			default:
				break;
		}
    }
}