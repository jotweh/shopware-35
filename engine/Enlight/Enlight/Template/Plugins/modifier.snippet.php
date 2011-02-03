<?php
function smarty_modifier_snippet($content, $name=null, $force=false)
{	
	if(!Enlight()->Bootstrap()->hasResource('Snippets')) return $content;
	
    $snippet = Enlight()->Snippets()->getSnippet();
        
    $content = html_entity_decode($content, ENT_QUOTES, mb_internal_encoding());
    $name = isset($name) ? $name : $content;
    
    $result = $snippet->get($name);
	if($result===null||$force)
	{
		$snippet->set($name, $content); 
	}
	else
	{
		$content = $result;
	}
	return $content;
}