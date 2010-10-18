<html>
<title></title>
<head>
</head>
<style>
* {padding:0;margin:0}
ul {list-style:none}
body {
	color: #000;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	padding: 5 5 5 5;
}
a:link {color:black; text-decoration:underline; font-weight:normal;}
a:visited {color:black; text-decoration:underline; font-weight:normal;}  
a:hover {color:black; text-decoration:underline; font-weight:normal;}
a:active {color:black; text-decoration:underline; font-weight:normal;} 
a:focus {color:black; text-decoration:underline; font-weight:normal;} 

form.standard_form {border:1px solid #dadada; background:#ececec}
label {float:left; min-width:150px; font-size:.9em; padding-top:4px;}
input.norm {border: 1px solid #000; padding:3px; font-size:.9em; float:left; width: 170px;}
.instyle_error {background:red}
textarea {width:367px; border:1px solid #000; padding:3px; font-size:.9em; font-family:Arial, Helvetica, sans-serif}
.submitbutton {margin:5px; border:1px solid #c7c7c7; background:#dadada;}
.error {background:#f7d8d8; border: 1px solid #ff0000; font-weight:bold; color:#ff0000}
.success {font-weight:bold}
br {
	clear: both;
}
#ueberpruefen, #versandkosten, #zahlungsweise {
	border: 1px solid #dadada;
	background: #ececec;
	border-top: none;
	margin-bottom: 10px;
	padding: 5px 0 0 0;
}
p {
	padding: 5px;
	position: relative;
	margin-bottom: 5px;
}
strong {
	font-size: 12px;
	padding: 5px 0 0 5px;
	margin-top: 5px;
}
label {
	width: 80px;
}
h1 {
	font-size:12px;
	color:#F00;
}
p.line {padding-bottom:10px; border-bottom:1px solid white}



</style>
<body>
<?php
#$_GET["ret_errormsg"] = strip_tags($_GET["ret_errormsg"]);
#$_GET["ret_additionalmsg"] = strip_tags($_GET["ret_additionalmsg"]);

if ($_GET["ret_status"]=="ERROR"){
	echo "<h1>".htmlentities($_GET["ret_errormsg"])."</h1>";
	echo "<h1>".htmlentities($_GET["ret_additionalmsg"])."</h1>";
	echo "<br /><a href=\"javascript:history.back();\">zurück zur Eingabe</a>";
}
?>
</body>
</html>