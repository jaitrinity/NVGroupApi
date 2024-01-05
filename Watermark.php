<?php 
class Watermark{

	function watermarkOnImage($sourceImg,$destinationImg,$latlong,$datetime){

		$SourceFile = '/var/www/trinityapplab.in/html/NVGroup/'.$sourceImg;
		$DestinationFile = '/var/www/trinityapplab.in/html/NVGroup/'.$destinationImg;

		$latLong = $latlong;
		// $addressResult = $this->addressByLatLong($latLong);
		$lat = explode(",", $latLong)[0];
		$long = explode(",", $latLong)[1];

		// $addressResult = json_decode($addressResult, true);
		// $address = $addressResult["results"][0]["formatted_address"];
		$datetime = $datetime;
		$WaterMarkText = $address.''.PHP_EOL.'Lat - '.$lat.''.PHP_EOL.'Long - '.$long.''.PHP_EOL.''.$datetime;
		// $watermarkFirstImg = '/var/www/trinityapplab.in/html/NVGroup/files/right.png';
		// $watermarkSecondImg = '/var/www/trinityapplab.in/html/NVGroup/files/left.png';

		$this->watermarkText($SourceFile, $WaterMarkText, $DestinationFile);
		// $this->watermarkFirstImage($DestinationFile, $watermarkFirstImg, $DestinationFile);
		// $this->watermarkSecondImage($DestinationFile, $watermarkSecondImg, $DestinationFile);
	}

	function addressByLatLong($latLong){
	   $latt = explode(",", $latLong)[0];
	   $longg = explode(",", $latLong)[1];

	   $url = "https://apis.mapmyindia.com/advancedmaps/v1/38wywkjm1wji9pobr5cczivktpwvysme/rev_geocode?lat=".$latt."&lng=".$longg;
	   $headers = array(
	         "Content-type: application/json"
	   );

	   $ch = curl_init($url);
	   curl_setopt_array($ch, array(
	     CURLOPT_POST => FALSE,
	     CURLOPT_RETURNTRANSFER => TRUE,
	     CURLOPT_HTTPHEADER => $headers
	   ));

	   $response = curl_exec($ch);
	   curl_close($ch);

	   return $response;
	}
	function watermarkText ($SourceFile, $WaterMarkText, $DestinationFile) {
	   list($width, $height) = getimagesize($SourceFile);
	   $image_p = imagecreatetruecolor($width, $height);
	   $image = imagecreatefromjpeg($SourceFile);
	   imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);
	   $red = imagecolorallocate($image_p, 255, 0, 0);
	   $font = '/var/www/trinityapplab.in/html/arial.ttf';
	   $font_size = 50;
	   $lines = explode('|', wordwrap($WaterMarkText, 70, "\n", true)); //15 sumbols - line limit
	   $wt_len = strlen($WaterMarkText);
	   $lc=1;// num lines
	   if ($wt_len > 15 && $wt_len <= 30) {
	         $lc=2;
	     } if ($wt_len > 30 && $wt_len <= 45) {
	         $lc=3;
	     } if ($wt_len > 45 && $wt_len <= 60) {
	         $lc=4;
	   }
	   foreach ($lines as $line){


	      $y=$the_box["top"] + ($imageY / 2) - ($the_box["height"] / 2);
	      $x=$the_box["left"] + ($imageX / 2) - ($the_box["width"] / (2*$lc));// * num lines to get width of 1 line

	      imagettftext($image_p, $font_size, 0, 10, ($height)-500, $red, $font, $line);
	   }
	   if ($DestinationFile) {
	      imagejpeg ($image_p, $DestinationFile, 100);
	   } else {
	      header('Content-Type: image/jpeg');
	      imagejpeg($image_p, null, 100);
	   };
	   imagedestroy($image);
	   imagedestroy($image_p);
	}

	function watermarkFirstImage($SourceFile, $WatermarkFirstImg, $DestinationFile) {
	   $marge_right = 10;
	   $marge_bottom = 10;

	   $destImage = imagecreatefromjpeg($SourceFile);
	   $watermark = imagecreatefrompng ($WatermarkFirstImg);
	   $watermark_width = imagesx($watermark);
	   $watermark_height = imagesy($watermark);

	   // this is an example to resized your watermark to 0.5% from their original size.
	   // You can change this with your specific new sizes.
	   $percent = 2;
	   $newwidth = $watermark_width * $percent;
	   $newheight = $watermark_height * $percent;

	   // create a new image with the new dimension.
	   $new_watermark = imagecreatetruecolor($newwidth, $newheight);

	   // Retain image transparency for your watermark, if any.
	   imagealphablending($new_watermark, false);
	   imagesavealpha($new_watermark, true);

	   // copy $watermark, and resized, into $new_watermark
	   // change to `imagecopyresampled` for better quality
	   imagecopyresized($new_watermark, $watermark, 0, 0, 0, 0, $newwidth, $newheight, $watermark_width, $watermark_height);

	   $_Dim[x] = imageSX($destImage);
	   $_Dim[y] = imageSY($destImage);
	   $logo_Dim[x] = imageSX($new_watermark);
	   $logo_Dim[y] = imageSY($new_watermark);
	   $x = $_Dim[x] - $logo_Dim[x];
	   $y = $_Dim[y] - $logo_Dim[y];
	   imagecopy ($destImage, $new_watermark,$x, 0, 0, 0, $newwidth, $newheight);
	   if ($DestinationFile) {
	       imagejpeg ($destImage, $DestinationFile, 100);
	   }
	   else{
	      header('Content-Type: image/jpeg');
	      imagejpeg($destImage,null,100);
	   }
	   imagedestroy($destImage);
	}
	function watermarkSecondImage($SourceFile, $WatermarkSecondImg, $DestinationFile) {

	   $destImage = imagecreatefromjpeg($SourceFile);
	   $watermark = imagecreatefrompng ($WatermarkSecondImg);
	   $watermark_width = imagesx($watermark);
	   $watermark_height = imagesy($watermark);

	   // this is an example to resized your watermark to 0.5% from their original size.
	   // You can change this with your specific new sizes.
	   // $percent = 0.6;
	   // $newwidth = $watermark_width * $percent;
	   // $newheight = $watermark_height * $percent;

	   $percent = 2;
	   $newwidth = $watermark_width * $percent;
	   $newheight = $watermark_height * $percent;

	   // create a new image with the new dimension.
	   $new_watermark = imagecreatetruecolor($newwidth, $newheight);

	   // Retain image transparency for your watermark, if any.
	   imagealphablending($new_watermark, false);
	   imagesavealpha($new_watermark, true);

	   // copy $watermark, and resized, into $new_watermark
	   // change to `imagecopyresampled` for better quality
	   imagecopyresized($new_watermark, $watermark, 0, 0, 0, 0, $newwidth, $newheight, $watermark_width, $watermark_height);

	   $_Dim[x] = imageSX($destImage);
	   $_Dim[y] = imageSY($destImage);
	   $logo_Dim[x] = imageSX($new_watermark);
	   $logo_Dim[y] = imageSY($new_watermark);
	   $x = $_Dim[x] - $logo_Dim[x];
	   $y = $_Dim[y] - $logo_Dim[y];
	   imagecopy ($destImage, $new_watermark, 0, 0, 0, 0, $newwidth, $newheight);

	   if ($DestinationFile) {
	       imagejpeg ($destImage, $DestinationFile, 100);
	   }
	   else{
	      header('Content-Type: image/jpeg');
	      imagejpeg($destImage,null,100);
	   }
	   imagedestroy($destImage);
	}
}
	

?>