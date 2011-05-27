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
	/**
	 * Init controller method
	 */
	public function init()
	{
		Shopware()->Loader()->registerNamespace('Shopware_Models', dirname(dirname(__FILE__)) . '/Models/');
		Shopware()->Loader()->registerNamespace('Shopware_Components', dirname(dirname(__FILE__)) . '/Components/');
		$this->View()->addTemplateDir(dirname(dirname(__FILE__)) . '/Views/');
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
			$sql = 'SELECT COUNT(*) FROM ' . Shopware()->Db()->quoteIdentifier($table);
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
		$url = $this->Config()->versionChannel;
		$url .= '?version='.urlencode(Shopware()->Config()->Version);
		$url .= '&host='.urlencode(Shopware()->Config()->Host);
		$config = new Zend_Config_Xml($url, 'update');
		return $config;
	}
	
	/**
	 * Index action method
	 */
	public function skeletonAction()
	{
	}
	
	/**
	 * Index action method
	 */
	public function checkVersionAction()
	{
		$config = $this->getVersionConfig();
		echo Zend_Json::encode($config->toArray());
	}
	
	public function FileAdapter()
	{
		static $fileAdapter;
		if(!isset($fileAdapter)) {
			if($this->getRequestMethod() == 'ftp') {
				$fileAdapter = $this->Ftp();
			} elseif(ini_get('safe_mode')
			  || !is_writable(Shopware()->OldPath())
			  || !is_writable(Shopware()->AppPath())) {
				throw new Enlight_Exception('Keine genügenden Rechte für die direkte Update-Methode.');
			} else {
				$fileAdapter = new Shopware_Components_File_Adapter_Direct();
			}
		}
		return $fileAdapter;
	}
	
	/**
	 * Returns ftp connection
	 *
	 * @return Shopware_Components_File_Adapter_Ftp
	 */
	public function Ftp()
	{
		static $ftp;
		if(isset($config)) {
			return $ftp;
		}
		
		if(empty($this->Request()->ftp_user)) {
			throw new Enlight_Exception('Die FTP-Einstellungen fehlen.');
		}
    	
		if(empty($this->Request()->ftp_host) || $this->Request()->ftp_host=='default') {
			$ftpHost = 'localhost';
		} else {
			$ftpHost = $this->Request()->ftp_host;
		}
		if(empty($this->Request()->ftp_port)||$this->Request()->ftp_port=='default') {
			$ftpPort = Shopware_Components_File_Adapter_Ftp::DEFAULT_PORT;
		} else {
			$ftpPort = $this->Request()->ftp_port;
		}
		
		$ftp = new Shopware_Components_File_Adapter_Ftp($ftpHost, $ftpPort, 10);
		$ftp->login($this->Request()->ftp_user, $this->Request()->ftp_password);
		
		if(empty($this->Request()->ftp_path) || $this->Request()->ftp_path == 'default') {
			$ftpPath = '.';
		} elseif($ftp->isFile($this->Request()->ftp_path)) {
			$ftpPath = dirname($this->Request()->ftp_path);
		} else {
			$ftpPath = $this->Request()->ftp_path;
		}
		$ftpPath = rtrim($ftpPath, "/ \t");
		
		$ftp->chdir($ftpPath);
		
		return $ftp;
	}
	
	/**
	 * Returns update config
	 *
	 * @return Enlight_Config
	 */
	public function Config()
	{
		static $config;
		if(!isset($config)) {
			$config = new Enlight_Config(array(
				'downloadChannel' => 'https://update.shopware.de/download.php',
				'versionChannel' => 'https://update.shopware.de/version.php',
				'downloadFile' => 'files/update/update.tmp',
				'backupDir' => 'files/backups/',
				'updateDir' => 'files/update/',
				'databaseFile' => 'files/update/update.sql',
				'updateFiles' => array(
					'templates/_default/',
					'engine/',
				),
				'restoreFiles' => array(
					'engine/Shopware/Plugins/Community/',
					'engine/Shopware/Plugins/Local/',
					'engine/local_old/',
					'engine/core/class/viewports/',
					'engine/core/class/inherit/',
				)
			));
		}
		return $config;
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
		if(!$path || $path == '.') {
			$path = $ftp->pwd();
		}
		$path = rtrim($path, '/') . '/';
		$rows = array(
			array('id' => $path, 'text' => '.', 'leaf' => true)
		);
		
		$list = $ftp->nlist($path);
		if (!empty($list)) {
			natsort($list);
			foreach($list as $value) {
				$value = basename($value);
				if(in_array($value, array('', '.', '..'))) {
					continue;
				}
				$isDir = $ftp->isDir($path . $value);
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
			$list[] = array('id' => 'zend', 'name' => 'Zend Optimizer');
		}
		if (extension_loaded('ionCube Loader')) {
			$list[] = array('id' => 'ioncube', 'name' => 'ionCube Loader');
		}
		echo Zend_Json::encode(array('data' => $list, 'count' => count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function formatListAction()
	{
		$list = array();
		if (extension_loaded('zip')) {
			$list[] = array('id' => 'zip', 'name' => 'zip');
		}
		if (extension_loaded('zlib')) {
			$list[] = array('id' => 'tar.gz', 'name' => 'tar.gz');
		}
		$list[] = array('id' => 'tar', 'name' => 'tar');
		echo Zend_Json::encode(array('data' => $list, 'count' => count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function methodListAction()
	{
		$list = array();
		if(extension_loaded('ftp')) {
			$list[] = array('id' => 'ftp', 'name' => 'FTP');
		}
		if(!ini_get('safe_mode') 
		  && is_writable(Shopware()->DocPath())
		  && is_writable(Shopware()->AppPath())) {
			$list[] = array('id' => 'direct', 'name' => 'Direkt');
		}
		echo Zend_Json::encode(array('data' => $list, 'count' => count($list)));
	}
	
	/**
	 * Returns request format
	 *
	 * @return string
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
	 * @return string
	 */
    public function getRequestMethod()
    {
    	$method = $this->Request()->getParam('method');
    	if(empty($method) 
    	  || !in_array($method, array('ftp', 'direct'))) {
    	  	if (extension_loaded('ftp')) {
				$method = 'ftp';
			} elseif(!ini_get('safe_mode') 
			  && is_writable(Shopware()->OldPath())
			  && is_writable(Shopware()->AppPath())) {
				$method = 'direct';
			}
    	}
		return $method;
    }
    
    /**
	 * Returns request package
	 *
	 * @return string
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
			} else {
				$package = null;
			}
    	}
		return $package;
    }
    
    /**
	 * Test config method
	 */
    public function testConfigAction()
    {
    	try {
			$ftp = $this->FileAdapter();
		} catch (Exception $e) {
			echo Zend_Json::encode(array(
				'success' => false,
				'message' => 'Update-Konfiguration fehlerhaft!<br />' . $e->getMessage()
			));
			return;
		}
    	echo Zend_Json::encode(array('success' => true));
    }
	
    /**
	 * Clean cache method
	 */
    public function cleanCacheAction()
    {
    	Shopware()->Cache()->clean();
    	echo Zend_Json::encode(array('success' => true));
    }
    
    /**
	 * Index action method
	 */
	public function backupListAction()
	{
		$backupDir = Shopware()->DocPath('files_backups');
		
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
		echo Zend_Json::encode(array('data' => $list, 'count' => count($list)));
	}
	
	/**
	 * Index action method
	 */
	public function downloadBackupAction()
	{
		$backupDir = Shopware()->DocPath('files_backups');
		$file = $this->Request()->getParam('file');
		$file = basename($file);
		if(!file_exists($backupDir . $file)) {
			return;
		}
		
    	$fp = fopen($backupDir.$file, 'r');
		$size = filesize($backupDir.$file) - strlen(fgets($fp));
		$file = basename($file, '.php') . '.sql';
				
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
	public function backupDatabaseAction()
	{
		$requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$offset = (int) $this->Request()->getParam('offset', 0);
		$backupDir = Shopware()->DocPath('files_backups');
		if(!$tables = $this->Request()->getParam('tables')) {
			$tables = Shopware()->Db()->listTables();
		}
		
		if(!$file = $this->Request()->getParam('file')) {
			$file = 'database_' . date('Y-m-d_H-i-s') . '.php';
			$fp = fopen($backupDir . $file, 'wb');
			$content = '/*<?php exit(); __halt_compiler(); ?>*/'."\n";
			fwrite($fp, $content);
		} else {
			$fp = fopen($backupDir . $file, 'ab');
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
					break;
				}
				if(time()-$requestTime >= 25
				  || ($position && $position%10 == 0)) {
					array_unshift($tables, $table);
					echo Zend_Json::encode(array(
						'message' => 'Exportiere Tabelle "' . $table . '".',
						'success' => true,
						'tables[]' => $tables,
						'file' => $file,
						'action' => 'backupDatabase',
						'offset' => $position + 1
					));
					return;
				}
			}
		}
		
		echo Zend_Json::encode(array(
			'message' => 'Datenbank-Export abgeschlossen!',
			'success' => true, 'action' => 'updateDatabase',
			'progress' => 0
		));
	}
	
	/**
	 * Index action method
	 */
	public function prepareAction()
	{
		try {
			$ftp = $this->FileAdapter();
			
			$ftp->putContents($this->Config()->downloadFile, '');
			$ftp->changeMode($this->Config()->downloadFile, 0777);
			$ftp->makeDir($this->Config()->backupDir);
			$ftp->changeMode($this->Config()->backupDir, 0777);
			$ftp->makeDir($this->Config()->updateDir);
		} catch (Exception $e) {
			echo Zend_Json::encode(array('message' => 'Updatedateien konnten nicht angelegt werden.<br />' . utf8_encode($e->getMessage()), 'success'=>false));
			return;
		}
		
		$hash = $this->Request()->getParam('hash');
		$package = $this->getRequestPackage();
		$format = $this->getRequestFormat();
		
		$downloadConfig = $this->Config()->downloadChannel
		     . '?package=' . urlencode($package)
		     . '&format=' . urlencode($format)
		     . '&hash=' . urlencode($hash)
		     . '&host=' . urlencode(Shopware()->Config()->host);
		
		try {	
			$downloadConfig = new Zend_Config_Xml($downloadConfig, 'download');
		} catch (Exception $e) {
			echo Zend_Json::encode(array('message' => 'Download-Konfiguration konnten nicht gelesen werden.<br />' . htmlentities($e->getMessage()), 'success'=>false));
			return;
		}
		
		$downloadConfig = $downloadConfig->toArray();
		$downloadConfig['message'] = 'Vorbereiten des Updates abgeschlossen.';
		$downloadConfig['success'] = true;
		$downloadConfig['action'] = 'download';

		echo Zend_Json::encode($downloadConfig);
	}
	
	/**
	 * Index action method
	 */
	public function downloadAction()
	{
		$requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$offset = (int) $this->Request()->getParam('offset', 0);
		$url = $this->Request()->getParam('url');
		$size = (int) $this->Request()->getParam('size', 0);
		
		$options = array(
			'http' => array(
			'method' => 'GET',
				'user_agent' => 'Shopware/' . Shopware()->Config()->Version
			)
		);
		$context = stream_context_create($options);
		
		$source = @fopen($url . '&offset=' . $offset, 'r', false, $context);
		if(!$source) {
			echo Zend_Json::encode(array('message'=>'Starten des Downloads ist fehlgeschlagen.', 'success'=>false));
			return;
		}
		
		if(!empty($offset)) {
			$target = @fopen($this->Config()->downloadFile, 'ab');
		} else {
			$target = @fopen($this->Config()->downloadFile, 'wb+');
		}
		
		while (!feof($source)) {
			
			fwrite($target, fread($source, 8192));
			if(time() - $requestTime >= 10) {
				$offset += ftell($source);
				echo Zend_Json::encode(array(
					'message' => round($offset / $size * 100, 2) . '% der Updatedateien heruntergeladen.',
					'size' => $size,
					'success' => true,
					'action' => 'download',
					'url' => $url,
					'step' => $step,
					'offset' => $offset,
					'progress' => $offset / $size
				));
				return;
			}
		}
		
		echo Zend_Json::encode(array(
			'message' => 'Herunterladen der Updatedateien abgeschlossen.',
			'success' => true,
			'action' => 'unpack',
			'progress' => 0
		));
	}
	
	/**
	 * Index action method
	 */
	public function unpackAction()
	{
		$requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$offset = (int) $this->Request()->getParam('offset', 0);
		
		try {
			$ftp = $this->FileAdapter();
	
			$zip = new Shopware_Components_File_Adapter_Zip($this->Config()->downloadFile);
			$zip->seek($offset);
			$count = $zip->count();
			
			while (list($position, $entry) = $zip->each()) {
				$name = $entry->getName();
				
				if($position !== $offset
				  && (time() - $requestTime >= 25 || $position % 500 == 0)) {
				  	
					echo Zend_Json::encode(array(
						'message' => $position  . ' von ' . $count . ' Dateien entpackt.',
						'success' => true,
						'action' => 'unpack',
						'offset' => $position,
						'progress' => $position / $count
					));
					return;
				}
				
				if(in_array(basename($name), array('', '.'))) {
					continue;
				}
				
				if($entry->isDir()) {
					$ftp->makeDir($this->Config()->updateDir . $name);
				} elseif($entry->isFile()) {
					$ftp->put($this->Config()->updateDir . $name, $entry->getStream());
				}
			}
		} catch (Exception $e) {
			echo Zend_Json::encode(array(
				'message' => 'Updatedateien konnten nicht entpackt werden.<br />' . htmlentities($e->getMessage()),
				'success'=>false
			));
			return;
		}
			
		echo Zend_Json::encode(array(
			'message' => 'Entpacken der Downloaddatei abgeschlossen.',
			'success' => true, 'action' => 'backupDatabase'
		));
	}
	
	/**
	 * Index action method
	 */
	public function updateDatabaseAction()
	{
		$dump = new Shopware_Components_Db_Import_Sql($this->Config()->databaseFile);
		foreach ($dump as $line) {
			try {
				Shopware()->Db()->exec($line);
			} catch (Zend_Db_Adapter_Exception $e) {
				if(in_array($e->getCode(), array('42S21', 1061, '23000', 1091))) {
	        		continue;
	        	}
	        	$msg = "Es ist ein Fehler beim Import der Datenbank aufgetreten:<br />\n";
	        	$msg .= htmlentities($e->getMessage())."<br />\n";
	        	$msg .= htmlentities($line);
	        	echo Zend_Json::encode(array('message' => $msg, 'success' => false));
				return;
	        }
		}
		
		echo Zend_Json::encode(array(
			'message' => 'Datenbank-Export abgeschlossen!',
			'success' => true, 'action' => 'rename',
			'progress' => 0
		));
	}
	
	/**
	 * Index action method
	 */
	public function renameAction()
	{
		try {
			$ftp = $this->FileAdapter();
			
			foreach($this->Config()->updateFiles as $file) {
				if(file_exists($file) && !file_exists($this->Config()->backupDir . $file)) {
					$ftp->makeDir(dirname($this->Config()->backupDir . $file));
					$ftp->rename($file, $this->Config()->backupDir . $file);
				}
			}
		} catch (Exception $e) {
			echo Zend_Json::encode(array('message' => 'Backup der Anpassung ist fehlgeschlagen.<br />'.$e->getMessage(), 'success' => false));
			return;
		}
		
		try {				
			foreach($this->Config()->updateFiles as $file) {
				if(!file_exists($this->Config()->updateDir . $file)) {
					continue;
				}
				$ftp->rename($this->Config()->updateDir . $file, $file);
			}
		} catch (Exception $e) {
			echo Zend_Json::encode(array('message' => 'Update der Dateien ist fehlgeschlagen.<br />'.$e->getMessage(), 'success' => false));
			return;
		}
		
		try {
			foreach($this->Config()->restoreFiles as $file) {
				if(!file_exists($this->Config()->backupDir . $file)) {
					continue;
				}
				$ftp->rename($file, $this->Config()->backupDir . $file);
			}
		} catch (Exception $e) {
			echo Zend_Json::encode(array('message' => 'Wiederherstellung der Anpassung ist fehlgeschlagen.<br />'.$e->getMessage(), 'success' => false));
			return;
		}
		
		echo Zend_Json::encode(array(
			'message' => 'Update der Dateien abgeschlossen!',
			'success' => true,
			'action' => 'finish',
			'progress' => 0
		));
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
		
		echo Zend_Json::encode(array(
			'message' => 'Update wurde erfolgreich abgeschlossen.',
			'success' => true,
			'progress' => 1
		));
	}
	
	/**
	 * Index action method
	 */
	public function updateAction()
    {
    	echo Zend_Json::encode(array('message' => 'Update wird gestartet.', 'success' => true, 'action' => 'prepare'));
    }
}