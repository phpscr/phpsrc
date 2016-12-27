<?php
/**
 * _generate_code()生成验证码图片，并将验证码放到session中
 * @access public
 * @param int $_width
 * @param int $_height
 * @param int $_length
 * @param bool $_flag
 */
function _generate_code($_width=75, $_height=25, $_length=4, $_flag = true) {
  $_nmsg="";
	//创建随机码作为验证码
	for($i = 0; $i < $_length; $i++) {
		$_nmsg .= dechex(mt_rand(0, 15));
	}
  //echo $_nmsg;
	//将验证码保存在服务器的会话中--保持持久有效
	$_SESSION['code'] = $_nmsg;
	//创建一张图片
	$_img = imagecreatetruecolor($_width, $_height);
	//创建一个白颜色指派器
	$_white = imagecolorallocate($_img, 255, 255, 255);
//填充图片颜色
	imagefill($_img, 0, 0, $_white);
	//是否显示边框，默认显示
	if($_flag) {
		//创建一个黑色指派器
		$_black = imagecolorallocate($_img, 0, 0, 0);
		//绘制黑色边框
		imagerectangle($_img, 0, 0, $_width-1, $_height-1, $_black);
	}
	//随机绘制6条线，线条的位置和颜色也是随机分配的
	for($i = 0; $i < 6; $i++) {
		//创建一个随机颜色指派器
		$_rnd_color = imagecolorallocate($_img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		//绘制线条
		imageline($_img, mt_rand(0, $_width), mt_rand(0, $_height), mt_rand(0, $_width), mt_rand(0, $_height), $_rnd_color);
	}
	//随机雪花
	for($i = 0; $i < 100; $i++) {
		//创建一个随机颜色指派器，颜色相对较淡些
		$_rnd_color = imagecolorallocate($_img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
		//在图片上加上水印*
		imagestring($_img, 1, mt_rand(1, $_width), mt_rand(1, $_height), '*', $_rnd_color);
	}
	//输出验证码，且每个验证码的字体大小和颜色都随机分配
	for($i = 0; $i < strlen($_SESSION['code']); $i++) {
		$_rnd_color = imagecolorallocate($_img, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200));
		imagestring($_img, mt_rand(4, 5), $i * $_width / $_length + mt_rand(1, 10),
			mt_rand(1, $_height / 2), $_SESSION['code'][$i], $_rnd_color);
	}
	//设置页面内容的输出类型，这一步相当重要
header('Content-Type:image/png');
//输出对应格式的图片
imagepng($_img);
//释放图像资源
imagedestroy($_img);
}
//_generate_code();
?>
