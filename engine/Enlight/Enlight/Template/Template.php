<?php
class Enlight_Template_Template extends Smarty_Internal_Template
{
    /*
    public $compiler_class = 'Enlight_Template_TemplateCompiler';
    
    public function compileTemplateSource()
    {
    	Enlight()->Events()->notify(__CLASS__.'_CompileTemplate');
    	
    	if (!is_object($this->compiler_object))
		{
            $this->compiler_object = new $this->compiler_class($this->resource_object->template_lexer_class, $this->resource_object->template_parser_class, $this->smarty);
        }
        
    	return parent::compileTemplateSource();
    }
    */
    
    public function extendsBlock($spec, $content, $mode = 'replace')
    {
    	if (strpos($content, $this->smarty->left_delimiter . '$smarty.block.child' . $this->smarty->right_delimiter) !== false) {
    		if (isset($this->block_data[$spec])) {
    			$content = str_replace($this->smarty->left_delimiter.'$smarty.block.child'.$this->smarty->right_delimiter, $this->block_data[$spec]['source'], $content);
    			unset($this->block_data[$spec]);
    		} else {
    			$content = str_replace($this->smarty->left_delimiter.'$smarty.block.child'.$this->smarty->right_delimiter, '', $content);
    		}
    	}
    	if (isset($this->block_data[$spec])) {
    		if (strpos($this->block_data[$spec]['source'], '%%%%SMARTY_PARENT%%%%') !== false) {
    			$content = str_replace('%%%%SMARTY_PARENT%%%%', $content, $this->block_data[$spec]['source']);
    		} elseif ($this->block_data[$spec]['mode'] == 'prepend') {
    			$content = $this->block_data[$spec]['source'].$content;
    		} elseif ($this->block_data[$spec]['mode'] == 'append') {
    			$content .= $this->block_data[$spec]['source'];
    		}
    	}
    	$this->block_data[$spec] = array('source'=>$content, 'mode'=>$mode);
    }
    
    public function renderTemplate()
    {
    	$obLevel = ob_get_level();
    	try {
    		return parent::renderTemplate();
    	} catch (Exception $e) {
			while (ob_get_level() > $obLevel) {
				ob_get_clean();
				$curObLevel = ob_get_level();
			}
    		throw $e;
    	}
    }
    
    /*
    public function compileTemplateSource ()
    {
    	$org = $this->smarty->force_compile;
    	$this->smarty->force_compile = true;
    	var_dump($this->resource_name); die();
    	parent::compileTemplateSource();
    	$this->smarty->force_compile = $org;
    }
    */
    
    protected function loadTemplateResourceHandler ($resource_type)
    {
    	if($resource_type=='extends')
    	{
    		return new Enlight_Template_TemplateResource($this->smarty);
    	}
    	return parent::loadTemplateResourceHandler($resource_type);
    }
    
    /*
    public function fetch()
    {
    	$template = new $this->smarty->template_class(
    		$this->template_resource,
    		$this->smarty,
    		$this,
    		$this->cache_id,
    		$this->compile_id,
    		$this->caching,
    		$this->cache_lifetime
    	);
    	return $this->smarty->fetch($template);
    }
    
    public function display()
    {
    	$template = new $this->smarty->template_class(
    		$this->template_resource,
    		$this->smarty,
    		$this,
    		$this->cache_id,
    		$this->compile_id,
    		$this->caching,
    		$this->cache_lifetime
    	);
    	return $this->smarty->display($template);
    }
    */
}