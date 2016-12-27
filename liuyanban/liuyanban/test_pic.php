<?php

session_start();
$_nmber = ' ';
for ($i = 0;$i < 4;++$i) {
    //$_number .= dechex (mt_rand(0, 15));
$_nmber .= dechex(mt_rand(0, 15));
//随机15个数，然后转换成16进制，输出单一的，再进行累积
//var_dump($_nmber);
}
//echo $_nmber;

isset($_SESSION['code']) ? 0 : $_SESSION['code'] = '';
$_SESSION['code'] = $_nmber;
//跨页面调用
$_width = 100;
//设置图片的属性
$_height = 30;
//设置图片的属性
$_img = imagecreatetruecolor($_width, $_height);
/*
//创建一个真彩图片
//设置颜色
$bg = imagecolorallocate($_img, 0, 0, 0);//第一次用调色板时的颜色
$te = imagecolorallocate($_img, 255, 255, 255);

//把字符串写到图像左上角,字体随机从1-6
imagestring($_img, rand(1,6), rand(3,70), 0, $_nmber, $te);
*/
header('Content-Type:image/png');
//标头设置图片
imagepng($_img);
//输出图像
imagedestroy($_img);
?>
