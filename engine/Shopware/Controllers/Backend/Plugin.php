<?php
/**
 * Shopware Plugin Manager
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Plugin extends Enlight_Controller_Action
{	
	/**
	 * The shopware db adapter instance.
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $db;

	/**
	 * Init resources
	 * 
	 * Set renderer for index / detail / skeleton action.
	 */
	public function preDispatch()
	{
		$this->db = Shopware()->Db();
		if(!in_array($this->Request()->getActionName(), array('index', 'detail', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}

	/**
	 * Load viewport for plugin manager and check system requirements.
	 */
	public function indexAction()
	{
		$this->View()->errorProxy = false;
		$this->View()->errorPluginPath = false;
		$this->View()->errorZip = false;

		if (!is_writeable(Shopware()->AppPath('Proxies'))) {
			$this->View()->errorProxy = true;
		}

		$pathes = array(
			'Plugins_Community_Backend',
			'Plugins_Community_Frontend',
			'Plugins_Community_Core'
		);
		foreach ($pathes as $path){
			if (!is_writeable(Shopware()->AppPath($path))) {
				$this->View()->errorPluginPath = true;
			}
		}
		
		if (!extension_loaded('zip')) {
			$this->View()->errorZip = true;
		}
	}

	/**
	 * Load window properties from skeleton template.
	 */
	public function skeletonAction ()
	{
		
	}

	/**
	 * Detail plugin action
	 * 
	 * Loads the plugin detail mask.
	 */
	public function detailAction()
	{
		$id = (int) $this->Request()->id;
		$select = $this->db->select()->from('s_core_plugins')->where('id=?', $id);
		
		$row = $this->db->fetchRow($select);
		
		$this->View()->callback = $this->Request()->callback;
		$this->View()->plugin = $row;
		
		$config = array();
		$select = $this->db->select()->from('s_core_plugin_configs')
			->where('pluginID=?', $id)
			->where('localeID=?', 1);
		$result = $this->db->query($select);
		while ($row = $result->fetch()) {
			$config[$row['shopID']][$row['name']] = unserialize($row['value']);
		}
		$this->View()->plugin_config = $config;
		
		$plugin = $this->getPluginById($id);
		$this->View()->form = $plugin->Form();
		
		$this->View()->shops = $this->getShops();
	}

	/**
	 * Saves the plugin details and the configuration.
	 */
	public function saveDetailAction()
	{
		$pluginId = (int) $this->Request()->id;
		$config = $this->Request()->getPost('config');
		$plugin = $this->getPluginById($pluginId);
		
		foreach ($config as $shopId => $shop_config) {
			foreach ($shop_config as $config_name => $config_value) {
				$sql = '
		    	 	INSERT INTO `s_core_plugin_configs` (
						`name`,
						`value`,
						`pluginID`,
						`localeID`,
						`shopID`
					) VALUES (
						?, ?, ?, ?, ?
					) ON DUPLICATE KEY UPDATE
						value=VALUES(value)
		    	';
				$result = $this->db->query($sql, array(
		    		$config_name,
		    		serialize(utf8_decode($config_value)),
		    		$pluginId,
		    		1,
		    		$shopId,
		    	));
			}
		}
		
		$active = $this->Request()->getParam('active');
		if($active!==null) {
			if(empty($active)) {
				$plugin->disable();
			} else {
				$plugin->enable();
			}
		}
	}

	/**
	 * Get plugin list to display in grid.
	 */
	public function getListAction()
	{
		$this->refreshList();

		$select = $this->db->select()->from(
			's_core_plugins',
			array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS id as fake_column'), '*')
		);
		
		if (!empty($this->Request()->sort)){
			$select->order(array('checkdate DESC'));
		} else {
			$select->order(array('added DESC','installation_date DESC','name'));
		}
		$limit = $this->Request()->getParam('limit', 25);
		$start = $this->Request()->getParam('start', 0);
		
		$select->limit($limit, $start);
	
		if($this->Request()->getParam('path')) {
			$path = explode('/', $this->Request()->getParam('path'));

			if(!empty($path[1])) {
				$select->where('namespace=?', $path[1]);
			}
			if(!empty($path[0])) {
				$select->where('source=?', $path[0]);
			}
		} elseif($this->Request()->getParam('search')) {
			$search = trim($this->Request()->getParam('search'));
			$search = '%'.$search.'%';
			$search = $this->db->quote($search); 
			
			$select->where('namespace LIKE '.$search);
			$select->orWhere('name LIKE '.$search);
			$select->orWhere('label LIKE '.$search);
			$select->orWhere('source LIKE '.$search);
			$select->orWhere('autor LIKE '.$search);
		}
		
		$rows = $this->db->fetchAll($select);
		
		foreach ($rows as $key=>$row) {
			$rows[$key]['path'] = $row['namespace'].'/'.$row['source'].'/'.$row['name'];
			$rows[$key]['active'] = (bool) $row['active'];
			$rows[$key]['update_date'] = empty($row['update_date']) ? null : strtotime($row['update_date']);
			$rows[$key]['installation_date'] = empty($row['installation_date']) ? null : strtotime($row['installation_date']);
		}
		
		$count = $this->db->fetchOne('select FOUND_ROWS()');

		echo Zend_Json::encode(array('data'=>$rows, 'count'=>$count));
	}
	
	/**
	 * Get plugin list for delete combo (deprecated)
	 */
	public function getDeleteListAction()
	{
		$list = array();
		
		$path = Shopware()->AppPath() . 'Plugins/Community';
		foreach (new DirectoryIterator($path) as $dir) {
		    if($dir->isDot()||strpos($dir->getFilename(), '.')===0){
		    	continue;
		    }
		    
		    $path2 = $dir->getPathname();
		    $file = $dir->getFilename();

		    if(in_array($file, array('Backend', 'Core', 'Frontend'))) {
		    	foreach (new DirectoryIterator($path2) as $dir2) {
		    		if($dir2->isDot()||strpos($dir2->getFilename(), '.')===0){
		    			continue;
		    		}

		    		$path3 = $dir2->getPathname();
		    		$file2 = $dir2->getFilename();
		    		
		    		if($this->isInstalled('Community', $file, $file2)) {
		    			continue;
		    		}

		    		$list[] = array('name'=>$file.'/'.$file2,'path'=>$path3);
		    	}
		    } else {
		    	$list[] = array('name'=>$file,'path'=>$path2);
		    }
		}

		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}

	/**
	 * Get plugin scopes for tree menu.
	 */
	public function getTreeAction()
	{
		$node = $this->Request()->node;
		
		$list = array();
		
		if(empty($node)) {
			$this->refreshList();
			
			$list = array(
					array('id'=>'Default', 'text'=>'Core Plugins', 'leaf'=>false, 'expended'=>true),
					array('id'=>'Community', 'text'=>'Community Plugins', 'leaf'=>false, 'expended'=>true),
					array('id'=>'Local', 'text'=>'Lokale Plugins', 'leaf'=>false, 'expended'=>true));
					
			foreach ($list as $key=>$row) {
				$list[$key]['leaf'] = false;
				$list[$key]['expanded'] = true;
				$select = $this->db->select()
				->distinct()
				->from('s_core_plugins', array('namespace as id', 'namespace as text'))
				->order(array('namespace'));
				$children = $this->db->fetchAll($select);
				foreach ($children as &$child){
					$child["leaf"] = true;
					$list[$key]['expanded'] = false;
					$child["id"] = $list[$key]["id"]."/".$child["id"];
				}
				
				$list[$key]['children'] = $children;
			}
		}
		
		echo Zend_Json::encode($list);
	}

	/**
	 * Enable plugin action
	 * 
	 * Enables a plugin based on the plugin id.
	 */
	public function enableAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		
		$result = $plugin->enable();

		echo Zend_Json::encode(array('success' => $result));		
	}

	/**
	 * Disable plugin action
	 * 
	 * Disables a plugin based on the plugin id.
	 */
	public function disableAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		
		$result = $plugin->disable();

		echo Zend_Json::encode(array('success'=>$result));
	}

	/**
	 * Install plugin action
	 * 
	 * Installs a plugin by id.
	 * Adds a license key if required.
	 */
	public function installAction()
	{
		$license = $this->Request()->license;
		$module = $this->Request()->license_module;
		$id = (int) $this->Request()->id;
			
		try {
			Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
				'Shopware_License'
			));
			
			if(!empty($license)) {
				if(Shopware()->License()->checkLicenseKey($license) !== true) {
					echo Zend_Json::encode(array(
						'success' => false,
						'license' => true,
						'license_module' => $module,
						'message' => utf8_encode('Die eingegebene Lizenz ist nicht gültig.')
					));
					return;
				}
				Shopware()->License()->addLicenseKey($license);
			}
			
			$plugin = $this->getPluginById($id);
			$result = $plugin->install();
			if($result) {
				$data = array(
					'installation_date' => new Zend_Db_Expr('NOW()'),
					'update_date' => new Zend_Db_Expr('NOW()'),
				);
				$this->db->update('s_core_plugins', $data, array('id=?'=>$id));
			}
		} catch (Shopware_Components_License_Exception $e) {
			echo Zend_Json::encode(array(
				'success' => false,
				'license' => true,
				'license_module' => $e->getModule(),
				'message' => utf8_encode('Für dieses Plugin wurde noch keine Lizenz hinterlegt.')
			));
			return;
		} catch (Exception $e) {
			echo Zend_Json::encode(array(
				'success' => false,
				'message' => $e->getMessage()
			));
			return;
		}
		
		echo Zend_Json::encode(array('success' => true));
	}

	/**
	 * Uninstall plugin action
	 * 
	 * Uninstall a given plugin by id.
	 */
	public function uninstallAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
			'Shopware_License', 'Shopware_Plugin'
		));
		
		if (!is_object($plugin)) {
			$sql = 'DELETE FROM `s_core_plugin_elements` WHERE `pluginID`=?';
			Shopware()->Db()->query($sql, $id);
			$sql = 'DELETE FROM `s_core_plugin_configs` WHERE `pluginID`=?';
			Shopware()->Db()->query($sql, $id);
			$sql = 'DELETE FROM `s_core_menu` WHERE `pluginID`=?';
			Shopware()->Db()->query($sql, $id);
			$sql = 'DELETE FROM `s_crontab` WHERE `pluginID`=?';
			Shopware()->Db()->query($sql, $id);
			$sql = 'DELETE FROM `s_core_plugins` WHERE `id`=?';
			Shopware()->Db()->query($sql, $id);
			$result = true;
		} else {
			$result = $plugin->disable();
			$result = $plugin->uninstall();
			if($result) {
				$data = array(
					'installation_date' => NULL,
					'update_date' =>  NULL
				);
				$this->db->update('s_core_plugins', $data, array('id=?'=>$id));
			}
		}
		echo Zend_Json::encode(array('success'=>$result));
	}

	/**
	 * Return all shop rows as array.
	 * 
	 * @return array
	 */
	public function getShops()
	{
		$sql = '
			SELECT `id`, `name`, `default`
			FROM `s_core_multilanguage`
			ORDER BY `default` DESC, `name`
		';
		$shops = $this->db->fetchAll($sql);
		return $shops;
	}

	/**
	 * Check if a certain plugin is already installed.
	 * 
	 * @param  $source
	 * @param  $namespace
	 * @param  $name
	 * @return bool
	 */
	public function isInstalled($source, $namespace, $name)
	{
		$select = $this->db->select()
			->from('s_core_plugins', 'id')
			->where('installation_date IS NOT NULL', $source)
			->where('source=?', $source)
			->where('name=?', $name)
			->where('namespace=?', $namespace);
		$id = $this->db->fetchOne($select);
		return !empty($id);
	}

	/**
	 * Returns a certain plugin by plugin id.
	 *
	 * @param int $id
	 * @return Shopware_Components_Plugin_Bootstrap
	 */
	public function getPluginById($id)
	{
		$select = $this->db->select()->from('s_core_plugins')->where('id=?', $id);
		$plugin =  $this->db->fetchRow($select);
		if(empty($plugin)) {
			return null;
		}
		$namespace = Shopware()->Plugins()->getNamespace($plugin['namespace']);
		if(empty($namespace)) {
			return null;
		}
		$plugin = $namespace->getPlugin($plugin['name']);
		if(empty($namespace)) {
			return null;
		}
		return $plugin;
	}

	/**
	 * Synchronizes the plugins in the database with the available plugins.
	 */
	public function refreshList()
	{
		foreach (Shopware()->Plugins() as $namespace_name => $namespace) {
			$namespace->loadAll();
			foreach ($namespace as $plugin_name => $plugin) {
				if(!$plugin instanceof Shopware_Components_Plugin_Bootstrap) {
					continue;
				}
				$info = $plugin->getInfo();
				$select = $this->db->select()
					->from('s_core_plugins', 'id')
					->where('name=?', $plugin_name)
					->where('namespace=?', $namespace_name);
				$id = $this->db->fetchOne($select);
				
				$data = array(
					'namespace' => $namespace_name,
					'name' => $plugin_name,
					'label' => !empty($info['label']) ? $info['label'] : $plugin_name,
					'version' => !empty($info['version']) ? $info['version'] : $plugin->getVersion(),
					'autor' => isset($info['autor']) ? $info['autor'] : 'shopware AG',
					'copyright' => isset($info['copyright']) ? $info['copyright'] : 'shopware AG',
					'description' => isset($info['description']) ? $info['description'] : '',
					'license' => isset($info['license']) ? $info['license'] : '',
					'support' => isset($info['support']) ? $info['support'] : '',
					'link' => isset($info['link']) ? $info['link'] : '',
					'changes' => isset($info['changes']) ? $info['changes'] : '',
					'source' => !empty($info['source']) ? $info['source'] : $plugin->getSource(),
				);
								
				if(empty($id)) {
					$data['added'] = new Zend_Db_Expr('NOW()');
					Shopware()->Db()->insert('s_core_plugins', $data);
				} else {
					$where = array('name=?'=>$plugin_name, 'namespace=?'=>$namespace_name);
					Shopware()->Db()->update('s_core_plugins', $data, $where);
				}
			}
		}
	}

	/**
	 * Upload plugin action
	 * 
	 * Saves the uploaded plugin in the plugins directory.
	 */
	public function uploadAction()
	{
		$upload = new Zend_File_Transfer_Adapter_Http();
		
		try {
			$upload->setDestination(Shopware()->DocPath() . 'files/downloads');
			$upload->addValidator('Extension', false, 'zip');
			if (!$upload->receive()) {
				$message = $upload->getMessages();
				$message = implode("\n", $message);
			} else {
				$this->decompressFile($upload->getFileName());
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		if($upload->getFileName()) {
			@unlink($upload->getFileName());
		}
		
		echo htmlspecialchars(Zend_Json::encode(array(
			'success'=>isset($message)?false:true,
			'message'=>isset($message)?$message:''
		)));
	}

	/**
	 * Decompress a given plugin zip file.
	 * 
	 * @param  $file
	 */
	public function decompressFile($file)
	{
		$target = Shopware()->AppPath('Plugins_Community');
		$filter = new Zend_Filter_Decompress( array(
			'adapter' => 'Zip',
			'options' => array(
				'target' => $target
		)));
		$filter->filter($file);
	}

	/**
	 * Direct download of a plugin zip file.
	 */
	public function downloadAction()
	{
		$url = $this->Request()->link;
		$tmp = @tempnam(Shopware()->DocPath().'files/downloads', 'plugins');
		
		try {
			$client = new Zend_Http_Client($url, array(
				'timeout' => 10,
				'useragent' => 'Shopware/'.Shopware()->Config()->Version
			));
			$client->setStream($tmp);
			$client->request('GET');
			
			$this->decompressFile($tmp);
			
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		@unlink($tmp);
		 
		echo Zend_Json::encode(array('success'=>isset($message)?false:true, 'message'=>isset($message)?$message:''));
	}

	/**
	 * Deletes a complete plugin directory structure.
	 */
	public function deleteAction()
	{
		$id = $this->Request()->id;
		
		$fetchPlugin = Shopware()->Db()->fetchRow('
			SELECT * FROM s_core_plugins WHERE id = ?
		',array($id));

		if (empty($fetchPlugin["id"]) || empty($fetchPlugin["name"])){
			throw new Enlight_Exception("Could not delete plugin with id $id");
		}

		$deletePath = Shopware()->AppPath(implode('_', array(
			'Plugins', $fetchPlugin['source'], $fetchPlugin['namespace'], $fetchPlugin['name']
		)));

		if(!file_exists($deletePath)) {
			throw new Enlight_Exception("Invalid path $deletePath");
		}

		// Remove plugin from database
		Shopware()->Db()->query("
			DELETE FROM s_core_plugins WHERE id = ?
		", array($id));

		// Remove plugin in filesystem
		if(is_dir($deletePath)) {
			$dirIterator = new RecursiveDirectoryIterator($deletePath);
	        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
	        foreach ($iterator as $file) {
	        	$path = $file->getPathname();
	            if (strpos($path, '.svn') !== false) {
	            	continue;
	            }
	            if ($file->isDir()) {
	                if (!$iterator->isDot()) {
	                    @rmdir($path);
	                } 
	            } else {
	                @unlink($path);
	            }
	        }
	        @rmdir($deletePath);
		} elseif(is_file($deletePath)) {
			@unlink($deletePath);
		}
		 
		echo Zend_Json::encode(array(
			'success' => empty($message),
			'message' => isset($message)?$message:''
		));
	}

	/** 
	 * ----------------- UPDATE SERVICE -----------------
	 */
	
	/**
	 * Search for updates for all community plugins
	 * through webservice
	 * @return void
	 */
	public function searchUpdatesAction()
	{
		$this->View()->setTemplate();
		$select = $this->db->select()->from('s_core_plugins', array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS id as fake_column'),'*'))->order(array('added DESC','installation_date DESC','name'));
		$select->where('source=?', 'Community');
		$rows = $this->db->fetchAll($select);
		if (empty($rows)){
			$this->Response()->setHttpResponseCode(500);
			throw new Enlight_Exception("The update-script only works for community plugins - there are no community plugins installed yet.");
		}

		$localPlugins = array();
		foreach ($rows as $row){
			$localPlugins[$row["name"]] = $row;
			$searchQuery[] = $row["name"];
		}


		$searchQuery[] = "";

		$checkedPlugins = $this->getPluginInfo($searchQuery);

		$foundPlugins = false;
		
		foreach ($checkedPlugins["articles"] as $plugin){
			$foundPlugins = true;
			if (isset($localPlugins[$plugin["pluginname"]])){
				$changelog = $plugin["changelog"];
				Shopware()->Db()->query("
				UPDATE s_core_plugins SET checkversion = ?, checkdate = ?, changes = ?
				WHERE id = ?
				",array($plugin["version"],$plugin["change_date"],$changelog,$localPlugins[$plugin["pluginname"]]["id"]));
				// Update plugin information
				//throw new Enlight_Exception("Update for {$plugin["ordernumber"]} found ... ");
			}
		}

		if ($foundPlugins == false){
			throw new Enlight_Exception("No Updates found");
		}
	}
	
	/**
	 * Get a list of available downloads for a certain plugin / shopware id connection
	 */
	public function getUpdateDownloadsAction()
	{
		$this->View()->setTemplate();
		$plugin = $this->Request()->plugin;
		$domain = $this->Request()->host;
		$shopwareID = $this->Request()->user;
		$password = $this->Request()->password;
		if (empty($plugin)
		  || empty($domain)
		  || empty($shopwareID)
		  || empty($password)
		){
			throw new Enlight_Exception("Missing Parameter");
		}

		$result = $this->getDownloadInfo($shopwareID, $password, $domain, $plugin);
		if (empty($result["success"]) || empty($result["downloads"])){
			throw new Enlight_Exception($result["errorcode"]." ".$result["errormsg"]);
		}
		$downloads = array();
		foreach ($result["downloads"] as $kind => $download){
			$downloads[] = array("typ" => $kind,"download"=>$download["downloadlink"],"filename"=>$download["filename"]);
		}

		echo Zend_Json::encode(array("count"=>count($download), "downloads"=>$downloads));
	}
	
	/**
	 * Hiftsmethode für die veralteten Service Funktionen.
	 *
	 * @param unknown_type $result
	 * @return unknown
	 */
	private function getReturn($result)
	{
		if($result->isSuccess()){
			return Zend_Json::decode($result);
		} else{
			return false;
		}
	}

	/**
	 * Liest von allen Plugins die Bestellnummer, den Namen,
	 * die aktuelle Version und den Changelog aus
	 *
	 * Es werden nur Plugins zurückgegeben, bei denen ein Download
	 * hinterlegt ist
	 *
	 * Mögliche Fehler:
	 * 100: Falscher Authcode
	 *
	 */
	public function getPluginInfo($filter=null)
	{
		$authcode = 'f0Dbh1jL9RoddLD8lqhYHKYWyUqova';
		$clientObj = new Zend_Rest_Client('http://store.shopware.de/restfulServer');
		$result = $clientObj->getPluginInfo($authcode, json_encode($filter))->post();
		return $this->getReturn($result);
	}

	/**
	 * Ermittelt die Downloadinformationen / -links
	 *
	 * 1) Überprüft, ob ein Artikel nach diesem Suchmuster existiert (Bestellnummer = $article_match || Artikelname = $article_match)
	 * 		> Ansonsten Fehlercode 101 > Kein Artikel gefunden
	 * 2) Führt einen Login am ShopwareID-Server durch
	 * 		> Schlägt dieser fehl wird der Fehlercode 102 zurückgegeben
	 * 3) Liest alle Module der Domain aus. Und überprüft, ob der Artikel lizenziert ist
	 * 		> Ist der Artikel nicht lizenziert wird der Fehlercode 103 zurückgegeben
	 * 4) Downloads werden ermittelt
	 * 		> Sollte kein Download ermittelt werden können wird der Fehlercode 104 zurückgegeben
	 * 5) Für den Download werden Downloadtokens erstellt, die 30 min gültig sind
	 *
	 * @param string $shopwareID
	 * @param string $password
	 * @param string $domain
	 * @param string $article_match
	 * @return unknown (Bestellnummer oder Artikelname)
	 */
	public function getDownloadInfo($shopwareID, $password, $domain, $article_match)
	{
		$authcode = 'f0Dbh1jL9RoddLD8lqhYHKYWyUqova';
		$clientObj = new Zend_Rest_Client('http://store.shopware.de/restfulServer');
		$result = $clientObj->getDownloadInfo($authcode, $shopwareID, $password, $domain, $article_match)->post();
		return $this->getReturn($result);
	}
}