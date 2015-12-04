<?php 
$file = fopen('csv.txt','r'); 
while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
//print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
$goods_list[] = $data;
 }
//print_r($goods_list);
 foreach ($goods_list as $k => $arr){
 //   if ($arr[0]!=""){
	print_r ($k);
	print "<br>";
        print_r ($arr);
	
        print	"<br>";
 //    }
}
# echo $goods_list[2][0];
 fclose($file);
?>
