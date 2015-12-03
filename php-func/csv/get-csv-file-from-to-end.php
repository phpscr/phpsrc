<?php

function get_file_count($file_name){
	$m = 0;
	$handle = fopen($file_name,"r");
		if ($handle){
			 while(!feof($handle)){
				 $m=$m+1;
				 #var_dump($m);
				 $buffer=fgets($handle);	
			}			
		}
	fclose($handle);
	return $m;	
}
/*
function get_file_line( $file_name, $line_star,  $line_end){
    $n = 0;
    $handle = fopen($file_name,"r");
    if ($handle) {
        while (!feof($handle)) {
            ++$n;
            $out = fgets($handle, 4096);
            if($line_star <= $n){
                $ling[] = $out;
            }
            if ($line_end == $n) break;
        }
        fclose($handle);
    }
    if( $line_end==$n) return $ling;
    return false;
}
$aa = get_file_line("csv.txt", 11, 20);  //从第11行到第20行
foreach ($aa as $bb){
    echo $bb."<br>";
}
*/
$t = get_file_count("csv.txt");
//var_dump($t);
echo $t;

?>
