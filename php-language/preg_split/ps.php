<?php

$pscmd="ps -aux";

$cmd_li= sprintf("%s", $pscmd); 
exec($cmd_li,$output);
var_dump($output);
foreach($output as $key => $value){
    //echo $key;
    //echo $value;
    $strs = preg_split("/\s+/",$value);
    foreach($strs as $str){
        echo "===$str===";
        echo "\n";
    }
}

?>
