<?php

class Enlight_Template_BlockCompiler extends Smarty_Internal_Compile_Block
{

} 


class Enlight_Template_BlockCloseCompiler extends Smarty_Internal_Compile_Blockclose
{
	public function compile($args, $compiler)
    {
    	if(!empty($compiler->smarty->block_manager)&&!empty($compiler->_tag_stack))
    	{
    		list($_open_tag, $_data) = end($compiler->_tag_stack);
    		$_name = trim($_data[0]['name'], '"\'');
    		$compiler->smarty->block_manager->loadBlock($_name, $compiler->smarty);
    	}
    	return parent::compile($args, $compiler);
    }
}