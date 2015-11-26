//统计文件中的字母个数
<?php



//定义
$count_letter = array(
	'A'=>array(
		'0'=>'A',
                '1'=>0,
		),
	'B'=>array(
		'0'=>'B',
		'1'=>9,
		)
	);
print_r($count_letter);

$n=0;
$handle = @fopen("/data0/largefile.random.txt","r");
var_dump($handle);
if($handle){
	while(!feof($handle)){
  	$string = fgets($handle);
	#print($string);
   	#var_dump($string);
  
  		for($i=0,$j=strlen($string);$i<$j;$i++){
			#var_dump($i,$j);
			#echo "$string[$i]";
			#echo "\n";
			$n=$n+1;
		#	var_dump($n);			
			foreach($count_letter as $k => $val) {
			
				if (string[$i] === $val['0']){
					$val['1']=$val['1']+1;
					$count_letter=array(array('1'=>$val[$i]));	


  		}
	}
}

print( $count_letter['A']['A']) ;
print( $count_letter['B']['N']) ;
echo "=======" ;
#echo $n ;
echo "=======" ;

//
