<?php
session_start();
$_SESSION["sUsername"] = addslashes(htmlspecialchars($_GET["sUsername"]));
$_SESSION["sPassword"] = addslashes(htmlspecialchars($_GET["sPassword"]));

define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result->sSession = addslashes(htmlspecialchars($_GET["sSession"]));
$result = $result->checkUser();

if ($result!="SUCCESS"){
	die();
}

$image = array(
	'articleID' => $_GET['article'],
	'image' => $_FILES['Filedata']['tmp_name']
);
sArticleImage($image);

$sCore->sDeletePartialCache("article",$_GET["article"]);
echo "okay";

function sResizePicture (&$image, $size, $new_width, $new_height)
{		
	$breite=$size[0]; //die Breite des Bildes
	$hoehe=$size[1]; //die Höhe des Bildes

	// Verhältnis Breite zu Höhe bestimmen
	$verhaeltnis = $breite/$hoehe;

	if ($breite < $new_width){
		$breite_neu = $breite;
	} else {
		$breite_neu = $new_width;
	}
	
	$hoehe_neu = round($breite_neu / $verhaeltnis,0);

	$newImage = imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
	
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);

	imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
	
	return $newImage;

	//imagejpeg($newImage,$newfile,90); //Thumbnail speichern

	//imagedestroy($newImage);
}

function sResizePictureDynamic (&$image, $size, $new_width, $new_height)
{
	$breite=$size[0]; //die Breite des Bildes
	$hoehe=$size[1]; //die Höhe des Bildes

	// Verhältnis Breite zu Höhe bestimmen

	if ($breite > $hoehe){
		$verhaeltnis = $breite/$hoehe;
		$breite_neu = $new_width;
		$hoehe_neu = round($breite_neu / $verhaeltnis,0);
	}else {
		$verhaeltnis = $hoehe/$breite;
		$hoehe_neu = $new_height;
		$breite_neu = round($hoehe_neu / $verhaeltnis,0);
	}

	$newImage = imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
	
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);

	imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
	
	return $newImage;

	//imagejpeg($newImage,$newfile,90); //Thumbnail speichern

	//imagedestroy($newImage);
}

function sArticleImage ($article_image = array())
{	
	global $sCore;
	$article_image['articleID'] = (int) $article_image['articleID'];
	if(empty($article_image['name']))
	{
		$i = 0;
		do
		{
			$article_image['name'] =  md5(uniqid(mt_rand(), true));
			$sql = "SELECT id FROM s_articles_img WHERE img='{$article_image['name']}'";
			$result = mysql_query($sql);
			if($result&&mysql_num_rows($result))
				$article_image['name'] = false;
			$i++;
		}
		while (empty($article_image['name'])&&$i<10);
		if(empty($article_image['name']))
		{
			return false;
		}
	}
	
	$uploaddir = realpath('../../../../'.$sCore->sCONFIG['sARTICLEIMAGES']).'/';
	$uploadfile = $uploaddir.$article_image['name'].'.tmp';
	if(!empty($article_image['image']))
	{
		if(!move_uploaded_file($article_image['image'], $uploadfile))
		{
			//$this->sAPI->sSetError("Copy image from '{$article_image['image']}' to '$uploadfile' not work", 10400);
			return false;
		}
		/*
		is_uploaded_file($article_image['image']) && 
		elseif(!copy($article_image['image'], $uploadfile))
		{
			//$this->sAPI->sSetError("Copy image from '{$article_image['image']}' to '$uploadfile' not work", 10400);
			return false;
		}
		*/
		chmod($uploadfile, 0644);
	}
	else
	{
		if(!file_exists($uploadfile))
		{
			//$this->sAPI->sSetError("Image source '$uploadfile' not found", 10401);
			return false;
		}
	}
			
	$imagesize = getimagesize($uploadfile);
	if(empty($imagesize))
	{
		unlink($uploadfile);
		//$this->sAPI->sSetError("File '$uploadfile' is not a image", 10402);
		return false;
	}
	$article_image['width'] = $imagesize[0];
	$article_image['height'] = $imagesize[1];
		
	if(!empty($article_image['image']))
	{			
		switch ($imagesize[2])
		{
			case IMAGETYPE_GIF:
				$extension = 'gif';
				$image = imagecreatefromgif($uploadfile);
				break;
			case IMAGETYPE_JPEG:
				$extension = 'jpg';
				$image = imagecreatefromjpeg($uploadfile);
				break;
			case IMAGETYPE_PNG:
				$extension = 'png';
				$image = imagecreatefrompng($uploadfile);
				break;
			default:
				unlink($uploadfile);
				//$this->sAPI->sSetError("Image type are not supported", 10403);
				return false;
		}
		
		$rename = $uploaddir.$article_image['name'].'.'.$extension;
		rename($uploadfile, $rename);
		$uploadfile = $rename;
		
		if($extension!='jpg')
		{
			imagejpeg($image, $uploaddir.$article_image['name'].'.jpg', 100);
		}
		
		$sizes = explode(";",$sCore->sCONFIG['sIMAGESIZES']);
		foreach ($sizes as $size)
		{
			list($width,$height,$suffix) = explode(':', $size);
			if (empty($height)) {
				$new_image = sResizePicture($image, $imagesize, $width, 0);
			} else {
				$new_image = sResizePictureDynamic($image, $imagesize, $width, $height);
			}
			$new_file_jpg = $uploaddir.$article_image['name']."_$suffix.jpg";
			$new_file = $uploaddir.$article_image['name']."_$suffix.".$extension;
			
			imagejpeg($new_image, $new_file_jpg, 90);
			switch($extension)
			{
				case 'gif':
					imagegif($new_image, $new_file);
					break;
				case 'png':
					imagepng($new_image, $new_file);
					break;
				default:
					break;
			}
			imagedestroy($new_image);
		}
		imagedestroy($image);
	}

	$sql = "SELECT id FROM s_articles_img WHERE articleID={$article_image['articleID']} AND main=1";
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		$article_image['main'] = 2;
	else
		$article_image['main'] = 1;
	
		
	$max = mysql_query("SELECT MAX(position) AS position FROM s_articles_img WHERE articleID = {$article_image['articleID']}"); 
	$max = @mysql_result($max,0,"position"); $max++;


	$sql = "
		INSERT INTO 
			`s_articles_img` 
			(`articleID`, `img`, `main`, `width`, `height`, `extension`,`position`) 
		VALUES (
			{$article_image['articleID']}, '{$article_image['name']}', {$article_image['main']},
			{$article_image['width']}, {$article_image['height']},
			'".mysql_real_escape_string($extension)."',$max
		)
	";
	mysql_query($sql);
	
	return mysql_insert_id();
}
?>