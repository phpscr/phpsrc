<?php
$fh = fopen("http://www.baidu.com/", "r");
if($fh){
    while(!feof($fh)) {
        echo fgets($fh);
    }
}
?>
