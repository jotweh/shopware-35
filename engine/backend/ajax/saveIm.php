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

if ($_REQUEST["msg"] && $_REQUEST["subject"]){
	$text = mysql_real_escape_string($_REQUEST["msg"]);
	$subject = mysql_real_escape_string($_REQUEST["subject"]);
	$user = $_SESSION["sName"];
	$receiver = intval($_REQUEST["user"]);
	if (empty($receiver)) $receiver = "-1";
	/*
	 	Feld  	Typ  	Kollation  	Attribute  	Null  	Standard  	Extra  	Aktion
	id 	int(11) 			Nein 	keine 	auto_increment 	Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	client 	varchar(255) 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	subject 	varchar(255) 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	text 	text 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	datum 	d
	*/
	$insert = mysql_query("
	INSERT INTO s_core_im (client,subject,text,datum,receiver)
	VALUES (
	'$user','$subject','$text',now(),'$receiver'
	)
	");
	
	
}

	
?>