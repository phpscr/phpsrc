<html>
<head>
<title>多维数组实例1</title>
</head>
<body>
<pre>
<?php
    $arr_person = array(
            'Tom'=> array(
                   'phone'=>32523543,
                   'address'=>'Hongkong,CHINA',
                   'sex' =>'M'
                         ),
            'Mary'=>array(
                   'phone'=>34563643,
                   'address'=>'Shengzheng,CHINA',
                   'sex'=>'F'
                         )
          );
print_r($arr_person);
echo $arr_person['Tom']['phone'];
?>
</pre>
</body>
</html>


<head>
<title>多维数组实例2</title>
</head>
<body>
<pre>
<?php
    $arr_person = array(
            'Tom'=> array(
                   'phone'=>32523543,
                   'address'=>'Hongkong,CHINA',
                   'sex' =>'M'
                         ),
            'Mary'=>array(
                   'phone'=>34563643,
                   'address'=>'Shengzheng,CHINA',
                   'sex'=>'F'
                         )
          );
print_r($arr_person);
foreach($arr_person as $key=>$val) {
 foreach($val as $key1=>$val1)
  {
  print "<br>".$key1."=>".$val1;
 }
}
?>
