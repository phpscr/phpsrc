<?php
$rand = "";
for($i = 0; $i<4; $i++)
{
$rand .= dechex(rand(1,15));
}

//制作图片
$im = imagecreatetruecolor(100, 30);
//设置颜色
$bg = imagecolorallocate($im, 0, 0, 0);//第一次用调色板时的颜色
$te = imagecolorallocate($im, 255, 255, 255);

//把字符串写到图像左上角,字体随机从1-6
imagestring($im, rand(1,6), rand(3,70), 0, $rand, $te);
//输出图像
header("Content-type: image/jpeg");
imagejpeg($im);
?>
