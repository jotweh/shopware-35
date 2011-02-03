<?php
class Shopware_Controllers_Frontend_Captcha extends Enlight_Controller_Action
{
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	public function indexAction()
	{
		if (!Shopware()->Session() || !Shopware()->Session()->Shop) {
			return;	
		}
		
		$captcha = 'frontend/_resources/images/captcha/background.jpg';
		$font = 'frontend/_resources/images/captcha/font.ttf';
		
		$template_dirs = Shopware()->Template()->getTemplateDir();
		
		foreach ($template_dirs as $template_dir) {
			if(file_exists($template_dir.$captcha)) {
				$captcha = $template_dir.$captcha;
				break;
			}
		}
		foreach ($template_dirs as $template_dir) {
			if(file_exists($template_dir.$font)) {
				$font = $template_dir.$font;
				break;
			}
		}
		
		$random = $this->Request()->rand;
		$random .= Shopware()->Plugins()->Core()->License()->getLicense("community");
		$random .= Shopware()->Plugins()->Core()->License()->getLicense("core");
		$random = md5($random);
		$string = substr($random,0,5);

		if(file_exists($captcha)) {
			$im = imagecreatefromjpeg($captcha);
		} else {
			$im = imagecreatetruecolor(162, 87);
		}
		
		if (!empty(Shopware()->Config()->CaptchaColor)){
			$colors = explode(',',Shopware()->Config()->CaptchaColor);
		}else {
			$colors = explode(',','255,0,0');
		}
		
		$black = ImageColorAllocate($im, $colors[0], $colors[1], $colors[2]);
		
		$string = implode(' ', str_split($string));
		
		if(file_exists($font)) {
			for ($i=0;$i<=strlen($string);$i++){
				$rand1 = rand(35,40);
				$rand2 = rand(15,20);
				$rand3 = rand(60,70);
				imagettftext($im,$rand1,$rand2,($i+1)*15,$rand3,$black,$font,substr($string,$i,1));
				imagettftext($im,$rand1,$rand2,(($i+1)*15)+2,$rand3+2,$black,$font,substr($string,$i,1));
			}
			for ($i=0; $i<8; $i++) {
				imageline($im, mt_rand(30,70), mt_rand(0,50), mt_rand(100,150), mt_rand(20,100), $black);
				imageline($im, mt_rand(30,70), mt_rand(0,50), mt_rand(100,150), mt_rand(20,100), $black);
			}
		} else {
			$white = ImageColorAllocate($im, 255, 255, 255); 
			imagestring($im, 5, 40, 35, $string, $white);
			imagestring($im, 3, 40, 70, 'missing font', $white);
		}
      	
		$this->Response()->setHeader('Content-Type', 'image/jpeg');
		
	   	imagejpeg($im, NULL, 90 );
		imagedestroy($im);
	}
}