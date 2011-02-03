<?php
class Shopware_Components_Menu_Item extends Zend_Navigation_Page_Uri
{
	public static function factory($options)
    {
    	if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        $options['type'] = __CLASS__;
        return parent::factory($options);
    }
    
    public function addItem($page)
	{
		return $this->addPage($page);
	}
	
	public function addItems($pages)
	{
		return $this->addPages($pages);
	}
}