<?php
class Enlight_Hook_ProxyFactory extends Enlight_Class
{
    protected $proxyNamespace;
    protected $proxyDir;
    protected $fileExtension = '.php';
    
    public function __construct($proxyNamespace=null, $proxyDir=null)
    {
        $proxyNamespace = Enlight::Instance()->App().'_Proxies';
        $proxyDir = Enlight::Instance()->AppPath().'Proxies'.Enlight::Instance()->DS();
        $this->proxyNamespace = $proxyNamespace;
        $this->proxyDir = $proxyDir;
    }
        
    public function getProxy($class)
    {
    	//if(!Enlight::Instance()->Hooks()->hasHooks($class))	{
    	//	return $class;
    	//}
    	$file = $this->getProxyFileName($class);
    	
        if(!file_exists($file)) {
        	if(!is_writable($this->proxyDir)) {
        		return $class;
        	}
            $this->generateProxyClass($class);
        }
        
    	$proxy = $this->getProxyClassName($class);
    	
    	$hooks = array_keys(Enlight::Instance()->Hooks()->getHooks($class));
    	$methodes = call_user_func($proxy.'::getHookMethods');
    	$diff = array_diff($hooks, $methodes);
    	
    	if(!empty($diff)) {
    		@unlink($file);
    	}
        
        return $proxy;
    }
    
    public function getProxyClassName($class)
    {
        return $this->proxyNamespace.'_'.$this->formatClassName($class);
    }
    
    public function formatClassName($class)
    {
        return str_replace(array('_','\\'), '', $class) . 'Proxy';
    }
    
    public function getProxyFileName($class)
    {
        $proxyClassName = $this->formatClassName($class);
        return $this->proxyDir.$proxyClassName.$this->fileExtension;
    }
    
    private function generateProxyClass($class)
    {
        $methods = $this->generateMethods($class);
        $fileName = $this->getProxyFileName($class);
        $proxyClassName = $this->formatClassName($class);

        $search = array(
            '<namespace>',
            '<proxyClassName>', '<className>',
            '<methods>',
            '<arrayHookMethods>'
        );
        $replace = array(
            $this->proxyNamespace,
            $proxyClassName, $class,
            $methods['methods'],
            str_replace("\n", '', var_export($methods['array'], true))
        );
        
        $file = $this->proxyClassTemplate;
        $file = str_replace($search, $replace, $file);

        file_put_contents($fileName, $file);
    }
        
    private function generateMethods($class)
    {
        $rc = new ReflectionClass($class);
        $methodsArray = array();
    	$methods = '';
    	foreach ($rc->getMethods() as $rm)
    	{
    		if($rm->isFinal()||$rm->isStatic()||$rm->isPrivate()){
    			continue;
    		}
    		if(substr($rm->getName(), 0, 2)=='__'){
    			continue;
    		}
    		if(!Enlight::Instance()->Hooks()->hasHooks($class, $rm->getName())){
    			continue;
    		}
    		$methodsArray[] = $rm->getName();
    		$params = '';
    		$proxy_params = '';
    		$array_params = '';
    		foreach ($rm->getParameters() as $rp)
    		{
    			if($params)
    			{
    				$params .= ', ';
    				$proxy_params .= ', ';
    				$array_params .= ', ';
    			}
    			if ($rp->isPassedByReference())
    			{
    				$params .= '&';
    			}
    			$params .= '$'.$rp->getName();
    			$proxy_params .= '$'.$rp->getName();
    			$array_params.= '\''.$rp->getName().'\'=>$'.$rp->getName();
    			if ($rp->isOptional())
    			{
    				$params .= '='.str_replace("\n",'',var_export($rp->getDefaultValue(), true));
    			}
    		}
    		$modifiers = Reflection::getModifierNames($rm->getModifiers());
    		$modifiers = implode(' ', $modifiers);
    		$search = array('<methodName>', '<methodModifiers>', '<methodParameters>', '<proxyMethodParameters>', '<arrayMethodParameters>', '<className>');
    		$replace = array($rm->getName(), $modifiers, $params, $proxy_params, $array_params, $class);
    		$method = $this->proxyMethodTemplate;
    		$method = str_replace($search, $replace, $method);
    		$methods .= $method;
    	}
    	return array('array'=>$methodsArray, 'methods'=>$methods);
    }
    
    private $proxyClassTemplate =
'<?php
class <namespace>_<proxyClassName> extends <className> implements Enlight_Hook_Proxy
{
	public function excuteParent($method, $args=null)
	{
		return call_user_func_array(array($this, \'parent::\'.$method), $args); 
	}
	
	public static function getHookMethods()
	{
		return <arrayHookMethods>;
	}
    <methods>
}
';
    private $proxyMethodTemplate =
'
    <methodModifiers> function <methodName>(<methodParameters>)
    {
        if(!Enlight::Instance()->Hooks()->hasHooks(\'<className>\', \'<methodName>\'))
        {
            return parent::<methodName>(<proxyMethodParameters>);
        }
        
        $obj_args = new Enlight_Hook_HookArgs($this, \'<methodName>\', array(<arrayMethodParameters>));
        
        return Enlight::Instance()->Hooks()->executeHooks($obj_args);
    }
';
}