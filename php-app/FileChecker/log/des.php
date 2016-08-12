<?php
$jsonf = __DIR__.'/'.'list.txt';

//$opf=file_get_contents($jsonf) ;
//$opf=file($jsonf) ;
$arr = json_decode(file_get_contents($jsonf),true);
//var_dump($arr);

foreach($arr as $key=>$value){

    echo $key.PHP_EOL;
    echo "-------------------".PHP_EOL;
   // var_dump($value);
    foreach($value as $keys => $values){
        echo $keys.'===>'.$values.PHP_EOL;
    }
        echo PHP_EOL;
}
?>
