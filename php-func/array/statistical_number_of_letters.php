//统计文件中的字母个数
<?php


$handle = @fopen("/data0/largefile.random.txt","r");
if($handle){
  while(!feof($handle)){
    $string = fgets($handle);
  }
  for($i=0,$j=strlen($string),$i<$j){
    echo "$string[$i]";

  }
}
