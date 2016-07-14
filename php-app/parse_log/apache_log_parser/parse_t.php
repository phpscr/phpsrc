<?php
$log = '/var/log/httpd/access_log';

$data = file_get_contents("$log");
if(is_string($data)){

    echo "string!".PHP_EOL;
}

$rows = explode("\n",$data);
$n = sizeof($rows);


for($i=0;$i<$n;$i++){
    $part = explode(" ",$rows[$i]);   
  //  print_r($part);
    //echo sizeof($part).PHP_EOL;    
    array_pop($part);
   foreach($part as $key => $value){
        echo $key."=>".$value.PHP_EOL;
    }
}
?>
