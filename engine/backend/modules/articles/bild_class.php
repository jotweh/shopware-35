<?php
/*
Stellt diverse Funktionen zur Verfügung, die wir für Bildmanipulation benötigen...
*/
class bildedit
{
	/*
	Passt die Größe eines Bildes an
	@@test
	*/
	function resize ($picture, $new_width, $new_height, $mode, $newfile)
	{
		$image=$this->imagecreatefrom($picture); //erstellt ein Abbild im Speicher
		$size=getimagesize($picture); //ermittelt die Größe des Bildes
		
		$breite=$size[0]; //die Breite des Bildes
		$hoehe=$size[1]; //die Höhe des Bildes
			
		// Verhältnis Breite zu Höhe bestimmen
		$verhaeltnis = $breite/$hoehe;
				
		if ($breite < $new_width){
			$breite_neu = $breite;
		}else {
			$breite_neu = $new_width;
		}
		
						
		$hoehe_neu = round($breite_neu / $verhaeltnis,0);	
		
		$newImage=imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
		imagealphablending($newImage, false);
		imagesavealpha($newImage, true);
		
		imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
						
		$this->imageend($newImage,$newfile); //Thumbnail speichern
		
		imagedestroy($image);
		imagedestroy($newImage);
	}
	
	function resize_dynamic ($picture, $new_width, $new_height, $mode, $newfile)
	{
		$image=$this->imagecreatefrom($picture); //erstellt ein Abbild im Speicher
		$size=getimagesize($picture); //ermittelt die Größe des Bildes
		
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
		
		$newImage=imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
		imagealphablending($newImage, false);
		imagesavealpha($newImage, true);
		
		imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
						
		$this->imageend($newImage,$newfile); //Thumbnail speichern
		
		imagedestroy($image);
		imagedestroy($newImage);
	}
	
	/*
		Dreht $image um 90° und speichert das Ergebnis als $newimage
	*/
	function rotate ($image, $newimage)
	{
		
				// Rotate-Funktion
			   $handle = imagick_readimage( $image ) ;
		        if ( imagick_iserror( $handle ) )
		        {
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
	        }
	
			if ( !imagick_rotate( $handle, -90 ) )
			{
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "imagick_rotate() failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
			}
	
	        if ( !imagick_writeimage( $handle, $newimage ) )
	        {
	                $reason      = imagick_failedreason( $handle ) ;
	                $description = imagick_faileddescription( $handle ) ;
	
	                print "imagick_writeimage() failed<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
			exit ;
	        }else {
	        	#echo "Angeblich gespeichert!?";	
	        }
	
	       # print "Done!<BR>\n" ;	
	}
	/*
	Schärfen-Funktion
	*/
	function sharpen ($image, $newimage)
	{
		   $handle = imagick_readimage( $image ) ;
		        if ( imagick_iserror( $handle ) )
		        {
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
	        }
	
			if (!imagick_sharpen( $handle, 10, 5 ) )
			{
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "imagick_rotate() failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
			}
	
	        if ( !imagick_writeimage( $handle, $newimage ) )
	        {
	                $reason      = imagick_failedreason( $handle ) ;
	                $description = imagick_faileddescription( $handle ) ;
	
	                print "imagick_writeimage() failed<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
			exit ;
	        }else {
	        	
	        }
	
	       # print "Done!<BR>\n" ;	
	}
	/*
	Unschärfen-Funktion
	*/
	function soften ($image, $newimage)
	{
		   $handle = imagick_readimage( $image ) ;
		        if ( imagick_iserror( $handle ) )
		        {
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
	        }
	
			if (!imagick_blur( $handle, 3, 3 ) )
			{
		                $reason      = imagick_failedreason( $handle ) ;
		                $description = imagick_faileddescription( $handle ) ;
		
		                print "imagick_rotate() failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
				exit ;
			}
	
	        if ( !imagick_writeimage( $handle, $newimage ) )
	        {
	                $reason      = imagick_failedreason( $handle ) ;
	                $description = imagick_faileddescription( $handle ) ;
	
	                print "imagick_writeimage() failed<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
			exit ;
	        }else {
	        	#echo "Angeblich gespeichert!?";	
	        }
	
	       # print "Done!<BR>\n" ;	
	}

	//by SC
	function imagecreatefrom( $picture )
	{
		$suffix = $this->imagesuffix( $picture );
		$this->suffix = $suffix;
		switch( $suffix ) {
			case 'jpg':  // jpeg
				return imagecreatefromjpeg( $picture );
				break;
			case 'png': //png
				return imagecreatefrompng( $picture );
				break;
			default: // else
				return imagecreatefromjpeg( $picture );
		}
	}
	
	function imageend ( $newImage , $newfile )
	{
		$suffix = $this->suffix;
		switch( $suffix ) {
			case 'jpg':  // jpeg
				return imagejpeg($newImage,$newfile,90);
				break;
			case 'png': //png
				return imagepng($newImage,$newfile);
				break;
			default: // else
				return imagejpeg($newImage,$newfile,90);
		}
	}
	
	function imagesuffix( $picture )
	{
		$suffix = $this->getsuffix( $picture );
		switch( $suffix ) {
			case 'jpeg': // jpeg
				return 'jpg';
				break;
			case 'jpg':  // jpeg
			case 'png': //png
				return $suffix;
				break;
			default: // else
				return 'jpg';
		}
	}
	
	function getsuffix ( $file )
	{
		return pathinfo( $file , PATHINFO_EXTENSION );
	}
}


?>