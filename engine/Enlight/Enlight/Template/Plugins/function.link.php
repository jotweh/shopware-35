<?php
function smarty_function_link($params, $smarty, $template)
{
	if(empty($params['file'])) {
		return '';
	}
	$file = $params['file'];
	
	if (strpos($file, '/')!==0&&strpos($file, '://')===false) {
		$dirs = (array) $smarty->template_dir;
		$dirs[] = Enlight()->OldPath();
		foreach ($dirs as $dir) {
			if(file_exists($dir.$file)) {
				$file = $dir.$file;
			}
		}
		if(strpos($file, Enlight()->OldPath())===0) {
			$file = substr($file, strlen(Enlight()->OldPath()));
		}
		if (strpos($file, '/')!==0) {
			//$request->getScheme().'://'. $request->getHttpHost().
			$request = Enlight()->Front()->Request();
			$file = $request->getBasePath().'/'.$file;
		}
	}
	
	if (strpos($file, '/')===0&&!empty($params['fullPath'])) {
		$file = $request->getScheme().'://'. $request->getHttpHost().$file;
	}
	
	return $file;
}