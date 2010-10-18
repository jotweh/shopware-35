<?php
class sViewportLogout{
	var $sSYSTEM;
	
	function sRender(){
		$this->sSYSTEM->sMODULES['sAdmin']->sLogout();
		return $this->sSYSTEM->sMODULES['sCore']->sStart();
	}
}
?>