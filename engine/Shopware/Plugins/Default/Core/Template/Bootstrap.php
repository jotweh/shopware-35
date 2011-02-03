<?php
class Shopware_Plugins_Core_Template_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Template',
	 		'onInitResourceTemplate'
	 	);
		Shopware()->Subscriber()->subscribeEvent($event);
		
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public static function onInitResourceTemplate(Enlight_Event_EventArgs $args)
	{
		$template = Enlight_Class::Instance('Enlight_Template_TemplateManager');
		
		$template->setHelpers(Shopware()->Helpers());
		
   	    $template->setCompileDir(Shopware()->DocPath().'cache/templates/');
		$template->setCacheDir(Shopware()->DocPath().'cache/templates/');
		$template->setTemplateDir(array(
			Shopware()->DocPath().'templates/_local/',
			Shopware()->DocPath().'templates/_default/'
		));
		
		array_unshift($template->plugins_dir, dirname(__FILE__).'/plugins/');
				
		$config = Shopware()->getOption('template');
		foreach ($config as $key => $value) {
			$template->{'set'.$key}($value);
		}
		
		return $template;
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}