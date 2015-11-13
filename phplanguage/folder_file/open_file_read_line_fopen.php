/*文件指针必须是有效的，必须指向由 fopen() 或 fsockopen() 成功打开的文件(并还未由 fclose() 关闭)。
以下是一个简单例子：
*/
//Example #1 逐行读取文件
<?php
$handle = @fopen("/data0/largefile.random.txt", "r");
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
?> 

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
