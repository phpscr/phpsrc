<?php
error_reporting(0);

define('D_P',__FILE__ ? dirname(__FILE__).'/' : './');
define('R_P',D_P);

$timestamp=time();
$db_olsize=96;

list($winduid)=explode("\t",StrCode(GetCookie('winduser'),'DECODE'));
$windid=rawurldecode($_GET['windid']);//rawurlencode 会员调用 ck.php 格式: ck.php?windid=rawurlencode($windid)

if($_SERVER['HTTP_X_FORWARDED_FOR']){
     $onlineip=$_SERVER['HTTP_X_FORWARDED_FOR'];
}elseif($_SERVER['HTTP_CLIENT_IP']){
     $onlineip=$_SERVER['HTTP_CLIENT_IP'];
}else{
     $onlineip=$_SERVER['REMOTE_ADDR'];
}
$onlineip =substrs(str_replace("\n",'',$onlineip),16);
//
$x_size=60;
$y_size=20;
$nmsg=num_rand(4);

require_once(D_P.'data/bbscache/config.php');
if($db_ckmethod==2 || $_GET['admin']) {
	Cookie('cknum',StrCode($timestamp."\t\t".md5($nmsg.$timestamp)));
}else{
	require_once(R_P.'require/gdcheck.php');
	ConcleGd(GetCookie('ol_offset'),$nmsg);
}

//
if (function_exists('imagecreate')){
	$aimg = imagecreate($x_size,$y_size);
	$back = imagecolorallocate($aimg, 255, 255, 255);
	$border = imagecolorallocate($aimg, 0, 0, 0);
	imagefilledrectangle($aimg, 0, 0, $x_size - 1, $y_size - 1, $back);
	imagerectangle($aimg, 0, 0, $x_size - 1, $y_size - 1, $border);

	for ($i=0;$i<strlen($nmsg);$i++){ 
		imageString($aimg,5,$i*$x_size/4+3,2, $nmsg[$i],$border); 
	}
	header("Pragma:no-cache");
	header("Cache-control:no-cache");
	header("Content-type: image/png");
	imagepng($aimg);
	imagedestroy($aimg);
	exit;
} else{
	header("Pragma:no-cache");
	header("Cache-control:no-cache");
	header("ContentType: Image/BMP");

	$Color[0] = chr(0).chr(0).chr(0);
	$Color[1] = chr(255).chr(255).chr(255);
	$_Num[0]  = "1110000111110111101111011110111101001011110100101111010010111101001011110111101111011110111110000111";
	$_Num[1]  = "1111011111110001111111110111111111011111111101111111110111111111011111111101111111110111111100000111";
	$_Num[2]  = "1110000111110111101111011110111111111011111111011111111011111111011111111011111111011110111100000011";
	$_Num[3]  = "1110000111110111101111011110111111110111111100111111111101111111111011110111101111011110111110000111";
	$_Num[4]  = "1111101111111110111111110011111110101111110110111111011011111100000011111110111111111011111111000011";
	$_Num[5]  = "1100000011110111111111011111111101000111110011101111111110111111111011110111101111011110111110000111";
	$_Num[6]  = "1111000111111011101111011111111101111111110100011111001110111101111011110111101111011110111110000111";
	$_Num[7]  = "1100000011110111011111011101111111101111111110111111110111111111011111111101111111110111111111011111";
	$_Num[8]  = "1110000111110111101111011110111101111011111000011111101101111101111011110111101111011110111110000111";
	$_Num[9]  = "1110001111110111011111011110111101111011110111001111100010111111111011111111101111011101111110001111";

	echo chr(66).chr(77).chr(230).chr(4).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(54).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(10).chr(0).chr(0).chr(0).chr(1).chr(0);
	echo chr(24).chr(0).chr(0).chr(0).chr(0).chr(0).chr(176).chr(4).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);

	for ($i=9;$i>=0;$i--){
		for ($j=0;$j<=3;$j++){
			for ($k=1;$k<=10;$k++){
				echo $Color[substr($_Num[$nmsg[$j]], $i * 10 + $k, 1)];
			}
		}
	}
	exit;
}

function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(1,9);
	}
	return $randval;
}
function readover(){
}
function substrs($content,$length) {
    if($length && strlen($content)>$length){
        $num=0;
        for($i=0;$i<$length-3;$i++) {
            if(ord($content[$i])>127){
                $num++;
            }
        }
        $num%2==1 ? $content=substr($content,0,$length-4):$content=substr($content,0,$length-3);
        $content.='..';
    }
    return $content;
}
function get_date($timestamp,$timeformat=''){
	global $db_datefm,$db_timedf;
	$date_show=$timeformat ? $timeformat : $db_datefm;
	$offset = $db_timedf=='111' ? 0 : $db_timedf;
	return gmdate($date_show,$timestamp+$offset*3600);
}
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
    $handle=fopen($filename,$method);
    if($iflock){
        flock($handle,LOCK_EX);
    }
    fwrite($handle,$data);
    if($method=="rb+") ftruncate($handle,strlen($data));
    fclose($handle);
}
function SafeCheck($var,$ck,$PwdCode){
	global $timestamp;
	$t	= $timestamp - $ck[0];
	if($t > 1800){
		Cookie($var,'',0);
		return false;
	}elseif($ck[2] == md5($PwdCode.$ck[0])){
		if($t > 60){
			$ck[0] = $timestamp;
			$ck[2] = md5($PwdCode.$timestamp);
			$Value = implode("\t",$ck);
			$$var  = StringCode($Value);
			Cookie($var,StringCode($Value));
		}
		return true;
	}else{
		Cookie($var,'',0);
		return false;
	}
}
function StrCode($string,$action='ENCODE'){
	$key	= substr(md5($_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),8,18);
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : base64_encode($code);
	return $code;
}
function Cookie($ck_Var,$ck_Value,$ck_Time = 'F'){
	global $db_ckpath,$db_ckdomain,$timestamp;
	$ck_Time = $ck_Time == 'F' ? $timestamp + 31536000 : ($ck_Value == '' && $ck_Time == 0 ? $timestamp - 31536000 : $ck_Time);
	$S		 = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
	!$db_ckpath && $db_ckpath = '/';
	setCookie($ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}
function GetCookie($Var){
	return $_COOKIE[$Var];
}
?>