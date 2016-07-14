<?php
// Array to convert into CSV
$arr = array(
            array(
                "name" => "A4",
                "manufacturer" => "Audi",
                "year" => "1994"
                ),
            array(
                "name" => "CLK",
                "manufacturer" => "Mercedes",
                "year" => "2005"
                ),  
            array(
                "name" => "Golf",
                "manufacturer" => "Volkswagen",
                "year" => "2008"
                ),      
            );




foreach($arr as $value){
   // var_dump($value);
    foreach($value as $values){
   //     echo $values;
    }
}
//var_dump($arr[2]);
//echo $arr[0]['name'].PHP_EOL;

$data=array();
if(isset($data['A4'])){
    $data['A4']++;
    //print_r($data['A4']);
    echo "nn";
}else{
    $data['A4'] = 1;
    print_r($data['A4']);
    echo "mm";
}
//var_dump($data['A4']);
?>
