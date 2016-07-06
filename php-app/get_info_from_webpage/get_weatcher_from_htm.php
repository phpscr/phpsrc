<?php
//抓取 天气：
ini_set('pcre.backtrack_limit', 1000000); //PHP.INI,默认的只有100000

$html=file_get_contents("http://www.weather.com.cn/weather/101200101.shtml");  
echo $html;
$parLeft=preg_quote('<div class="jxyb" id="weather6h">','/');//开始
$parRight=preg_quote('</div>','/');#结束
$par='/'.$parLeft.'(.*)'.$parRight.'/isU';  //取出中间(.*)的内容

$ArrAdd=array(); 
preg_match_all($par,$html,$ArrAdd);  
print_r($ArrAdd);
//=================================
//抓取IP地址对应的地理位置：
/*

$ip = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];  
$ip = ($ip) ? $ip : $_SERVER["REMOTE_ADDR"];  
$ip='122.82.231.128';
//用百度查询IP，试过很多IP查询方式，感觉只有百度的准确一些  
$html=file_get_contents('http://www.baidu.com/s?wd='.$ip);  
//echo $html;

$parleft=preg_quote('>来&nbsp;&nbsp;&nbsp;自:&nbsp;<strong>','/');
$parright=preg_quote('</strong></p><p class="op_ip_search">','/');
$par='/'.$parleft.'(.*)'.$parright.'/i';  //取出中间(.*)的内容
$ArrAdd=array(); 
preg_match_all($par,$html,$ArrAdd);  
//print_r($ArrAdd);
echo 'IP对应地址：'.$ArrAdd[1][0].'<br>'; 
*/

?>
