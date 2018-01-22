<?php
$imgurl=base64_decode($_GET['url']);
$pathinfo=@getimagesize($imgurl);
switch ($pathinfo[2]) {  
	case 1: $im=imagecreatefromgif($imgurl); $pathext='gif';break;  
	case 2: $im=imagecreatefromjpeg($imgurl);$pathext='jpg'; break;  
	case 3: $im=imagecreatefrompng($imgurl); $pathext='png';break;  
}
$width=$_GET['w'];
if(!isset($_GET['h'])){
$kgpi=$pathinfo[0]/$width;
$height=$width;//$pathinfo[1]/$kgpi;
}else{
$height=$_GET['h'];
}
$ims=imagecreatetruecolor($width,$height);
if($_GET['fun']!='no'){
	$dst_path = './images/ddgshui.png';
	$dst = imagecreatefromstring(file_get_contents($dst_path));
	list($src_w, $src_h) = getimagesize($dst_path);
}
if(imagecopyresampled($ims,$im,0,0,0,0,$width,$height,$pathinfo[0],$pathinfo[1])){
	if($_GET['fun']!='no'){
		imagecopy( $ims, $dst,($width-5-$src_w),($height-5-$src_h), 0, 0, $src_w, $src_h);
	}
	header("content-type: image/jpeg");
	imagejpeg($ims);
}
?>