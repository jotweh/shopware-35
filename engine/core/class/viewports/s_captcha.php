<?php
class sViewportCaptcha
{
	var $sSYSTEM;
	
	function sRender()
	{
		if (!empty($this->sSYSTEM->sSESSION_ID) && empty($this->sSYSTEM->sBotSession))
		{
			$captcha = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/media/img/default/captcha.jpg";
			$font = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/media/img/default/font.ttf";
			if (!is_file($captcha)){
				$captcha = "templates"."/0/de/media/img/default/captcha.jpg";
				$font = "templates"."/0/de/media/img/default/font.ttf";
				
			}
			if (is_file($captcha)){
				
				$im = imagecreatefromjpeg($captcha);
				if (!empty($this->sSYSTEM->sCONFIG["sCAPTCHACOLOR"])){
					$colors = explode(",",$this->sSYSTEM->sCONFIG["sCAPTCHACOLOR"]);
				}else {
					$colors = explode(",","255,0,0");
				}
				
				$black = ImageColorAllocate ($im, $colors[0], $colors[1], $colors[2]);
				
				for ($i=1;$i<=5;$i++){
					$string .= chr(rand(97,122))." ";
				}
				$this->sSYSTEM->_SESSION["sCaptcha"] = $string;
				for ($i=0;$i<=strlen($string);$i++){
					$rand1 = rand(25,40);
					$rand2 = rand(0,20);
					$rand3 = rand(40,70);
					imagettftext($im,$rand1,$rand2,($i+1)*15,$rand3,$black,$font,substr($string,$i,1));
					imagettftext($im,$rand1,$rand2,(($i+1)*15)+2,$rand3+2,$black,$font,substr($string,$i,1));
				}
				for( $i=0; $i<8; $i++ ) {
		          imageline($im, mt_rand(30,70), mt_rand(0,50), mt_rand(100,150), mt_rand(20,100), $black);
		          imageline($im, mt_rand(30,70), mt_rand(0,50), mt_rand(100,150), mt_rand(20,100), $black);
		      	}
		
				header('Content-Type: image/jpeg');
			   	imagejpeg( $im, "", 90 );
				imagedestroy($im);
			}
		}
		exit;
	}
}
?>