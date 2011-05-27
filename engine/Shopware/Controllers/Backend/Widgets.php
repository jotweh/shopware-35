<?php
/**
 * Backend widget controller
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Backend\Controllers
 */
class Shopware_Controllers_Backend_Widgets extends Enlight_Controller_Action
{
	public $widgetsXml;
	public $widgetsApi;
	public $panelApi;
	protected $authCode;
	
	/**
	 * Create reference to widget-model
	 * @return void
	 */
	public function preDispatch(){
		$this->widgetsXml = Shopware()->DocPath()."/files/config/Widgets.xml";
		$this->widgetsApi = new Shopware_Models_Widgets_Widgets(null,$this->widgetsXml);
		$this->authCode = 'f0Dbh1jL9RoddLD8lqhYHKYWyUqova'; // Shopware Update-Service Rest-Code
		parent::preDispatch();
	}

	/**
	 * Load all widgets from a certain user panel
	 * Panels where saved in DocRoot\files\config\Panels.xml with
	 * the hash of the user id as unique key.
	 * @return void
	 */
	public function indexAction()
	{
		$config = Shopware()->Plugins()->Backend()->Widgets()->Config();
		$numberCols = $config->columns;
		if (empty($numberCols)) $numberCols = 4;
		
		$dir = Shopware()->DocPath()."/files/config";

		$error = "";
		// Check permissions
		if (!is_dir($dir)){
			$error = "Directory $dir not found. Please create!";
		}
		if (!is_writeable($dir) && empty($error)){
			$error = "Directory $dir does not have sufficient rights (0777)";
		}
		if (!is_file($dir."/.htaccess") && empty($error)){
			$error = "File .htaccess in directory $dir does not exists!";
		}
		if (strpos(file_get_contents($dir."/.htaccess"),"Deny from all")===false && empty($error)){
			$error = "File .htaccess in directory $dir must have \"deny from all\" option set!";
		}

		if (!empty($error)){
			$this->View()->error =$error;
			$this->View()->loadTemplate("backend/widgets/error.tpl");
		}else {
			$this->View()->UserName = $_SESSION['sName'];
			$this->View()->isAdmin = $_SESSION["sAdmin"];

			// Load panel and widgets
			$userID =  $_SESSION["Shopware"]["Auth"]->id;
			$userID = md5($userID);

			$firstUse = false;
			if (!is_file($dir."/Panels.xml")){
				$firstUse = true;
			}elseif (strpos(file_get_contents($dir."/Panels.xml"),$userID)===false){
				$firstUse = true;
			}

			if ($firstUse){
				$this->View()->firstUse = true;

			}else {
				$this->View()->firstUse = false;
				$panels = array(0=>array("id"=>$userID));

				foreach ($panels as $panel){
					$PanelModel = new Shopware_Models_Widgets_Panel($panel["id"]);
					$WidgetModel = new Shopware_Models_Widgets_Widgets($panel["id"],'');

					$PanelConfiguration = $PanelModel->get($panel["id"]);

					foreach ($PanelConfiguration["widgets"] as &$widget){
						$widget["label"] = 'Test';
						//$widgetData = $WidgetModel->get($widget["widgetType"]);
						//$widget["object"] = $widgetData;

						$widget["configuration"]["widgetLabel"] = utf8_decode($widget["configuration"]["widgetLabel"]);
						//print_r($widget["configuration"]);exit;
						$widgets[] = $widget;
					}


					$first = true;
					for ($i=0;$i<$numberCols;$i++){
						if ($first){
							$PanelConfiguration["cols"][$i] = array("id"=>md5(uniqid(rand())),"flex"=>0,"width"=>"150","items"=>array());
							$first = false;
						}else {
							$PanelConfiguration["cols"][$i] = array("id"=>md5(uniqid(rand())),"flex"=>1,"items"=>array());
						}
					}
					//print_r($PanelConfiguration["widgets"]);exit;
					foreach ($PanelConfiguration["widgets"] as $tempWidget){

						if (!isset($tempWidget["position"])){
							$tempWidget["position"] = 0;
						}
						if (!isset($tempWidget["column"])){
							$tempWidget["column"] = 0;
						}

						$PanelConfiguration["cols"][$tempWidget["column"]]["items"][] = $tempWidget;
					}


					for ($i=0;$i<$numberCols;$i++){
						if (isset($PanelConfiguration["cols"][$i]["items"][0])){
							$this->multiArraySort($PanelConfiguration["cols"][$i]["items"],"position");
						}
					}

					$panelData[] = $PanelConfiguration;
				}
			}

			$this->View()->panel = $panelData[0];
		}
	}

	/**
	 * Helper function to easily sort multidimensional arrays
	 * @param  $data
	 * @param  $sortby
	 * @return void
	 */
	protected function multiArraySort(&$data, $sortby)
	{
	   static $sort_funcs = array();

	   if (empty($sort_funcs[$sortby])) {
	       $code = "\$c=0;";
	       foreach (explode(',', $sortby) as $key) {
	         $array = array_pop($data);
	         array_push($data, $array);
	         if(is_numeric($array[$key]))
	           $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) );";
	         else
	           $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
	       }
	       $code .= 'return $c;';
	       $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	   } else {
	       $sort_func = $sort_funcs[$sortby];
	   }

	  $sort_func = $sort_funcs[$sortby];
      uasort($data, $sort_func);
	}

	/**
	 * Skeleton to define window properties
	 * @return void
	 */
	public function skeletonAction ()
	{

	}

	/**
	 * Support Drag & Drop to define widget position in column grid
	 * @return void
	 */
	public function savePositionAction(){
		$this->View()->setTemplate();
		
		$position = $this->Request()->position;
		$widget = $this->Request()->widget;
		$column = $this->Request()->column;

		$panelModel = new Shopware_Models_Widgets_Panel(md5($_SESSION["Shopware"]["Auth"]->id));

		$panelModel->updateWidget($widget,"position",$position,null);
		$panelModel->updateWidget($widget,"column",$column,null);
				
	}

	/**
	 * Load widget javascript component asynchron into panel
	 * Each component has his own smarty template under Shopware\Plugins\Default\Backend\Widgets\Views
	 * The Template gets parsed dynamic by smarty
	 * This function required one parameter that holds the widget-type, widget/panel-uid and a temporary value
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function getWidgetItemAction(){
		$this->View()->setTemplate();
		$item = $this->Request()->load;

		if (strpos($item,"_")===false){
			throw new Enlight_Exception("Wrong format - parameter load required");
		}
		$temp = explode("_",$item);
		$widgetType = $temp[0];
		$widgetUid = $temp[1];
		$userID =  $_SESSION["Shopware"]["Auth"]->id;
		$userID = md5($userID);
		$panelModel = new Shopware_Models_Widgets_Panel($userID);
		$valueSetInPanel = $panelModel->getWidgetConfiguration($widgetUid);
		$widget = $this->widgetsApi->get($widgetType);
		$widget["configuration"] = $valueSetInPanel;
		$this->View()->widget = $widget;
		
		$this->View()->item = $item;
		$this->View()->widgetType = $widgetType;
		$this->View()->widgetUid = $widgetUid;
		if (!is_file(Shopware()->DocPath().$widget["views"].$widget["template"])){
			$widget["views"] = str_replace("\\","/",$widget["views"]);
		}
		$this->View()->addTemplateDir(Shopware()->DocPath().$widget["views"]);
		$this->View()->loadTemplate($widget["template"]);
	}

	/**
	 * Helper function to load stylesheets dynamicly at runtime
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function getStyleSheetAction(){
		$this->View()->setTemplate();
		$this->Response()->setHeader('Content-Type', 'text/css;charset=iso-8859-1');

		$file = $this->Request()->css;
		$widgetType = $this->Request()->widget;
		$widget = $this->widgetsApi->get($widgetType);
		$path = Shopware()->DocPath().$widget["views"]."backend/plugins/widgets/_resources/".$widget["name"].".css";
		if (!is_file($path)){
			throw new Enlight_Exception("File $path not exists");
		}
		echo file_get_contents($path);
	}


	/**
	 * Load all widgets that are general available or currently assigned to the user panel
	 * Ignore widgets that have permission rules defined that permit the access by this user
	 * @return void
	 */
	public function getWidgetsAction(){
		$this->View()->setTemplate();
		$node = $this->Request()->node;

		if ($node == "src"){
			$widgets[] = array("id"=>"available","text"=>utf8_encode("Verfügbar"),"leaf"=>false);
			$widgets[] = array("id"=>"active","text"=>"Aktiv","leaf"=>false);
			echo Zend_Json::encode($widgets);
		}elseif($node=="available") {
			$widgets = $this->widgetsApi->getAll();

			$temp = array();


			foreach ($widgets as $widget){
				// Check widget permissions
				if (isset($widget["permissions"])){
					$mode = $widget["permissions"]["aclGroup"];

					if ($mode == 1 && !$_SESSION["Shopware"]["Auth"]->admin){
						// Only admins
						continue;
					}

					if ($mode == 2){
						// User based permissions
						$users = $widget["permissions"]["Users"];
						
						$validUser = false;
						foreach ($users as $checkUserId){
							if ($checkUserId["id"] == $_SESSION["Shopware"]["Auth"]->id){
								$validUser = true;
							}
						}
						if (!$validUser) continue;
					}
				}

				$temp[] = array("id"=>md5(uniqid(rand())),"widgetType"=>$widget["name"],"text"=>$widget["name"],"label"=>$widget["label"],"leaf"=>true);
			}
			
			echo Zend_Json::encode($temp);
		}else {
			$userID =  $_SESSION["Shopware"]["Auth"]->id;
			$userID = md5($userID);
			$panelModel = new Shopware_Models_Widgets_Panel($userID);
			$widgets = $panelModel->getAllWidgets();
			foreach ($widgets as $widget){
				$temp[] = array("id"=>md5(uniqid(rand())),"widgetType"=>$widget["widgetType"],"text"=>$widget["widgetType"]." (".$widget["configuration"]["widgetLabel"].")","widgetUid"=>$widget["uid"],"leaf"=>true);
			}
			echo Zend_Json::encode($temp);
		}
	}

	/**
	 * Delete a widget from the user panel
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function deleteWidgetAction(){
		$this->View()->setTemplate();
		if (empty($this->Request()->widgetUid)){
			throw new Enlight_Exception("Empty widget id given");
		}

		$userID =  $_SESSION["Shopware"]["Auth"]->id;
		$userID = md5($userID);
		$panelModel = new Shopware_Models_Widgets_Panel($userID);
		$panelModel->deleteWidget($this->Request()->widgetUid);
	}

	/**
	 * Load Widget configuration dialog
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function getWidgetSettingsAction(){
		$this->View()->setTemplate();
		$widgetType = $this->Request()->load;
		if (empty($widgetType)){
			throw new Enlight_Exception("Empty widget - parameter load required");
		}
		if (strpos($widgetType,"_")===false){
			throw new Enlight_Exception("Wrong format - parameter load required");
		}
		$temp = explode("_",$widgetType);
		$widgetType = $temp[0];
		$widgetUid = $temp[1];

		$widget = $this->widgetsApi->get($widgetType);

		$userID =  $_SESSION["Shopware"]["Auth"]->id;
		$userID = md5($userID);
		$panelModel = new Shopware_Models_Widgets_Panel($userID);
		$widget["configuration"]["widgetLabel"] =
		array("type"=>"text","name"=>"widgetLabel","label"=>"Widget label","isRequired"=>true);

		$widget["configuration"] = array_reverse($widget["configuration"],true);
		
		if (!empty($widgetUid)){
			foreach ($widget["configuration"] as &$element){
				$valueSetInPanel = $panelModel->getWidgetConfiguration($widgetUid);
				if (isset($valueSetInPanel[$element["name"]])){
					$element["value"] = $valueSetInPanel[$element["name"]];
				}else {

					$element["value"] = isset($element["default"]) ? $element["default"] : "";
				}
			}
		}
		
		$widget["uid"] = $widgetUid;
		$this->View()->widget = $widget;


		$this->View()->load = $this->Request()->load;
		$this->View()->loadTemplate("backend/widgets/settings.tpl");
	}

	/**
	 * Load widget permission assign mask
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function getWidgetAdminAction(){
		$this->View()->setTemplate();
		$component = $this->Request()->load;
		if (strpos($component,"_")===false){
			throw new Enlight_Exception("Empty component - parameter load required");
		}
		$component = explode("_",$component);
		$component = $component[0];
		
		$component = $this->widgetsApi->get($component);
		$users = array();
		
		foreach ($component["permissions"]["Users"] as $user){
			$id = $user["id"];
			$users[] = $id;
		}
		$this->View()->selectedUsers = $users;
		
		$this->View()->widget = $component;

		if (empty($component)){
			throw new Enlight_Exception("Empty component - parameter load required");
		}
		$this->View()->load = $this->Request()->load;
		$this->View()->users = $this->getUsers();
		$this->View()->loadTemplate("backend/widgets/admin.tpl");
	}

	/**
	 * Save widget settings / configuration and add to panel
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function saveSettingsAction(){
		$this->View()->setTemplate();
		// User-ID to define panel filename
		$userID =  $_SESSION["Shopware"]["Auth"]->id;
		// Widget configuration
		$config = $this->Request()->config;
		// Widget name
		$widget = $this->Request()->widgetName;
		// Widget unique id
		$widgetId = $this->Request()->widgetUid;
		// Build panel filename
		$panel = md5($userID);

		if (empty($userID)||empty($widget)){
			throw new Enlight_Exception("Empty userId or widget");
		}

		$createNewPanel = false;
		if (!is_file(Shopware()->DocPath()."/files/config/Panels.xml")){
			$createNewPanel = true;
		}else {
			$XML = new Shopware_Components_Xml_SimpleXml();
			$XML->loadFile(Shopware()->DocPath()."/files/config/Panels.xml");
			$xpath = '//Panel[@name="'.$panel.'"]';
			if (!$XML->SimpleXML->firstOf($xpath)){
				$createNewPanel = true;
			}
		}
		$panelModel = new Shopware_Models_Widgets_Panel($panel);

		// Create panel
		if ($createNewPanel == true){
			$panelModel->create($panel);
		}

		// Put Widget into panel
		$widgetId = $panelModel->updateWidget($widgetId,"date",date("Y-m-d H:i:s"),'');
		$widgetId = $panelModel->updateWidget($widgetId,"widgetType",$widget,'');
		// Save widget configuration
		$panelModel->updateWidgetConfiguration($widgetId,$config);

		echo Zend_Json::encode(array("success"=>true,"data"=>array("widgetUid"=>$widgetId,"widgetType"=>$widget)));
	}

	/**
	 * Save widget permission rules
	 * @throws Enlight_Exception
	 * @return void
	 */
	public function saveAdminAction(){
		$this->View()->setTemplate();
		$name = $this->Request()->name;
		$aclGroup = $this->Request()->usergroup;
		$users = $this->Request()->users;
		
		if (empty($name)){
			throw new Enlight_Exception ("Empty name given");
		}

		$rights = array("aclGroup"=>$aclGroup,"users"=>$users);
		$this->widgetsApi->updatePermissions($name,$rights);

		echo Zend_Json::encode(array("success"=>true));
	}

	/**
	 * Helper function to get all active users from database
	 * @return array
	 */
	protected function getUsers(){
		return Shopware()->Db()->fetchAll("
		SELECT id, username FROM s_core_auth WHERE active = 1
		");
	}

	/**
	 * Test-function for plugin documentation to demonstrate remote combos
	 * @return void
	 */
	public function getUsersAction(){
		$this->View()->setTemplate();
		$sql = "SELECT id, username FROM s_core_auth ORDER BY id";
		$result = Shopware()->Db()->fetchAll($sql);
		echo Zend_Json::encode(array("data"=>$result,"count"=>count($result)));
	}

	/**
	 * Hiftsmethode der Klasse restfulClient
	 *
	 * @param unknown_type $result
	 * @return unknown
	 */
	private function getReturn($result){
		if($result->isSuccess()){
			$newArr = array();
			$result = json_decode($result);

			$newArr = $this->getArray($result);
			return $newArr;
		}else{
			return false;
		}
	}

	/**
	 * Hiftsmethode der Klasse restfulClient
	 *
	 * @param unknown_type $result
	 * @return unknown
	 */
	private function getArray($result)
	{
		$newArr = array();
		if($result instanceof stdClass || is_array($result)){
			foreach ($result as $key=>$value) {
				$newArr[$key] = $this->getArray($value);
			}
			return $newArr;
		}else{
			return $result;
		}
	}

	/* ----------------- SERVICES ----------------- */

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
	public function getPluginInfo()
	{
		$result = $this->clientObj->getPluginInfo($this->authCode)->post();
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
		$result = $this->clientObj->getDownloadInfo($this->authCode, $shopwareID, $password, $domain, $article_match)->post();
		return $this->getReturn($result);
	}

 }