<?php
$arr = array(
	'one'=>array(
			'name'=>'张三',
			'age'=>'23',
			'sex'=>'男'),
   	'two'=>array(
			'name'=>'李四',
			'age'=>'43',
			'sex'=>'女'),
	'three'=>array(
			'name'=>'王五',
			'age'=>'32',
			'sex'=>'男'),
   	'four'=>array(
			'name'=>'赵六',
			'age'=>'12',
			'sex'=>'女')
	);

foreach($arr as $k=>$val){
		echo "\n";
   		echo $val['name'].$val['age'].$val['sex']."\n";
		echo "\n";
	}
?>

