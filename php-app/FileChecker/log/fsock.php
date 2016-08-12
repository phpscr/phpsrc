<?php
$fp = fsockopen("www.sina.com.cn", 80, $errno, $errstr, 30);   
print_r($errno);
print_r($errstr);
/*
if (!$fp) {   
    echo "$errstr ($errno)<br />\n";   
} else {   
    $out = "GET / HTTP/1.1\r\n";   
    $out .= "Host: www.github.com\r\n";   
    $out .= "Connection: Close\r\n\r\n";   

    fwrite($fp, $out);   
    while (!feof($fp)) {   
        echo fgets($fp, 128);   
    }   
}  
 */

var_dump($fp);
    fclose($fp);   
?>
