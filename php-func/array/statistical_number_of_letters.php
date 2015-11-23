//统计文件中的字母个数
<?php

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
			var_dump($n);			

  		}
	}
}
echo "=======" ;
echo $n ;
echo "=======" ;
