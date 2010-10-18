<?php
function smarty_modifier_rewrite($content, $title=null)
{
	$core = Shopware()->Modules()->Core();
	return $core->sRewriteLink($content, $title);
}