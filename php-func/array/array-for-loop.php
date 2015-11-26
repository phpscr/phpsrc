<?php

//使用for循环遍历
 $arr2=array(
    array("张三","20","男"),
    array("李四","25","男"),
    array("王五","19","女"),
    array("赵六","25","女")
);
// echo "<table border=2 bordercolor=red><tr><td>姓名</td><td>年龄</td& gt;<td>性别</td></tr>";
for($i=0;$i<4;$i++){
//    echo "<tr>";
    for($j=0;$j<3;$j++){
//      echo "<td>";
      echo $arr2[$i][$j];
//      echo "</td>";
    }
// echo "</tr>";
// echo "<br>";
}
// echo "</table>";
//
//使用foreach遍历

/*
<?php
 $arr = array('one'=>array('name'=>'张三','age'=>'23','sex'=>'男'),
     'two'=>array('name'=>'李四','age'=>'43','sex'=>'女'),
     'three'=>array('name'=>'王五','age'=>'32','sex'=>'男'),
     'four'=>array('name'=>'赵六','age'=>'12','sex'=>'女'));

foreach($arr as $k=>$val){
    echo $val['name'].$val['age'].$val['sex']."<br>";
 }
 echo "<p>";
 ?>
*/

/*
<?php
 $arr = array('one'=>array('name'=>'张三','age'=>'23','sex'=>'男'),
     'two'=>array('name'=>'李四','age'=>'43','sex'=>'女'),
     'three'=>array('name'=>'王五','age'=>'32','sex'=>'男'),
     'four'=>array('name'=>'赵六','age'=>'12','sex'=>'女'));
 foreach($arr as $key=>$value){
 foreach($value as $key2=>$value2){
    echo $value2;
 }
 echo "<br>";
 }
*/
?>
