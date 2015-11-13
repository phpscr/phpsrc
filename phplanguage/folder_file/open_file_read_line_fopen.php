/*文件指针必须是有效的，必须指向由 fopen() 或 fsockopen() 成功打开的文件(并还未由 fclose() 关闭)。
以下是一个简单例子：
*/
//Example #1 逐行读取文件
<?php
$handle = @fopen("/data0/largefile.random.txt", "r");
if ($handle) {
    while (!feof($handle)) {
        //$string = fgets($handle, 4096);
        $string = fgets($handle);
        echo $string;
	
	//	Example 1-16. Processing each byte in a string
	//	$string = "This weekend, I'm going shopping for a pet chicken.";
		$vowels = 0;
		for ($i = 0, $j = strlen($string); $i < $j; $i++) {
		//	if (strstr('aeiouAEIOU',$string[$i])) {
	        //	$vowels++;
			echo "\n";
			echo $string[$i];
			}
	}

    
    fclose($handle);
}

/*
<?php
     $f = fopen ("fgetstest.php", "r");
     $ln= 0;
     while ($line= fgets ($f)) {
         ++$ln;
         printf ("%2d: ", $ln);
         if ($line===FALSE) print ("FALSE ");
         else print ($line);
     }
     fclose ($f);
?>

*/
?> 
