<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
if (empty($_REQUEST["files"])){
	die("No files specified");
}
$files = explode(";",$_REQUEST["files"]);
if (!count($files)){
	die("No files specified - Array empty");
}
if (empty($_REQUEST["typ"])){
	$typ = 0;
}else {
	$typ = intval($_REQUEST["typ"]);
}
foreach ($files as $file){
	$select = mysql_query("
	SELECT id,hash FROM s_order_documents WHERE orderID = $file AND type = $typ
	");
	if (@mysql_num_rows($select)){
		$hash = mysql_result($select,0,"hash");
		if (empty($hash)){
			$jobs[] = mysql_result($select,0,"id").".pdf";
		}else {
			$jobs[] = $hash.".pdf";
		}
	}
}
if (!count($jobs)) die("No files specified - jobs empty");

// *****************
?>
<?php
define('FPDF_FONTPATH','../../vendor/fpdf/font/');
require('../../vendor/fpdf/fpdf.php');
require('../../vendor/fpdf/fpdi.php');
class concat_pdf extends fpdi {    
	var $files = array();    
	function concat_pdf($orientation='P',$unit='mm',$format='A4') {        
		parent::fpdi($orientation,$unit,$format);    }    
		function setFiles($files) {        $this->files = $files;    }   
		function concat() {       
		 	 foreach($this->files AS $file) { 
		 	 	$file = "../../../files/documents/".$file;           
		 	 	$pagecount = $this->setSourceFile($file);            
		 	 	for ($i = 1; $i <= $pagecount; $i++) {                 
		 	 		$tplidx = $this->ImportPage($i);                 
		 	 		$this->AddPage();                 
		 	 		$this->useTemplate($tplidx);            
		 	 	}        
		 	 }    
		}
}
$pdf= new concat_pdf();$pdf->setFiles($jobs);$pdf->concat();$pdf->Output("Sammeldruck".date("Y_m_d_H_i_s").".pdf","D");?>