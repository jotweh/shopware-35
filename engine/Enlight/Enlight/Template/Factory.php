<?php
class Enlight_Template_Factory extends Enlight_ClassPattern
{
	public function init()
	{
		require_once(Enlight::Instance()->Path().'Vendor/Smarty/Smarty.class.php'); 
	}
	
	public function create()
	{
        $smarty = new Enlight_Template_Engine();
        
		//$smarty->addPluginsDir(Enlight::Instance()->Path().'Components/Smarty/Plugins//');
		$smarty->addPluginsDir(Enlight::Instance()->AppPath().'Plugins/Smarty/');
		$smarty->setTemplateDir(Enlight::Instance()->AppPath().'Templates/Default/');
		$smarty->setCompileDir(Enlight::Instance()->AppPath().'Files/CompileTemplates/');
		$smarty->setCacheDir(Enlight::Instance()->AppPath().'Files/CacheTemplates/');

		return $smarty;
	}
}