<?php
 //   print_r(scandir("/var/log/httpd"));
    foreach(scandir("/var/log/httpd") as $value){
                echo $value;
                echo "\n";
                }
?>
