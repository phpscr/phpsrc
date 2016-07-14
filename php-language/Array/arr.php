<?php
$arr=array();
$data=array();
for($i=0;$i<9;$i++){
    //echo $i;
    $arr=array('a'=>$i,'b'=>$i);
    $data[]=$arr;
}
//var_dump($arr);
//print_r($arr);
//var_dump($data);
foreach($data as $key => $value){
    foreach($value as $values){
        echo $values.PHP_EOL;
    }
}



?>
