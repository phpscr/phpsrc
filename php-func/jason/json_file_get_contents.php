<?php
$content= file_get_contents('data_jason.txt');
$content = json_decode($content);//将json字符串转化成php数组
#print_r($content);

foreach ($content as $key => $value ) {//循环数组
	var_dump($key);	
	var_dump($value);	
	foreach ($value as $key2 => $value2) {
		#var_dump($key2);
		#var_dump($value2);
	}
}
# echo '<li>' . $key['city'] . '</li>';
# echo '<li>' . $key['city_en'] . '</li>';
# echo '<li>' . $key['date_y'] . '</li>';
# echo '<li>' . $key['week'] . '</li>';
 

?>

