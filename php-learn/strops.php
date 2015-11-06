<?php //echo strpos("You love php, I love php too!","php"); print strpos("You love php, I love php too!","php"); if(strpos("lixin@staff.sina.com.cn","@") !== false){
/*echo "包";
}else{
echo "错";
}
*/

/**
* PHP中用strpos函数过滤关键字
* 脚本之家
*/
// 关键字过滤函数
function keyWordCheck($content){
// 去除空白
   $content = trim($content);
// 读取关键字文本
   $content = @file_get_contents('keyWords.txt');
// 转换成数组
   $arr = explode("\n", $content);
// 遍历检测
for($i=0,$k=count($arr);$i<$k;$i++){
// 如果此数组元素为空则跳过此次循环
   if($arr[$i]==''){
       continue;
         }

// 如果检测到关键字，则返回匹配的关键字,并终止运行
// 这一次加了 trim()函数
 if(@strpos($str,trim($arr[$i]))!==false){
//$i=$k;
         return $arr[$i];
         }
     }
// 如果没有检测到关键字则返回false
     return false;
 }


 $content = '这里是要发布的文本内容。。。';

 // 过滤关键字
 $keyWord =  keyWordCheck($content);

 // 判断是否存在关键字
 if($keyWord){
         echo '你发布的内容存在关键字'.$keyWord;
 }else{
         echo '恭喜！通过关键字检测';
         // 往下可以进行写库操作完成发布动作。

 }
*>
