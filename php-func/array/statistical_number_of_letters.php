//统计文件中的字母个数
<?php




$count_letter = array(
	'A'=>array(
		'A'=>'A',
                'N'=>0,
		),
	'B'=>array(
		'B'=>'B',
		'N'=>0,
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
			if (string[$i] === r(

  		}
	}
}

print( $count_letter['A']['A']) ;
echo "=======" ;
#echo $n ;
echo "=======" ;

//
