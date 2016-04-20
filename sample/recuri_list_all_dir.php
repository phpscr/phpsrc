
<?php
function showdir($path){
 $dh = opendir($path);//打开目录
 while(($d = readdir($dh)) != false){
 //逐个文件读取，添加!=false条件，是为避免有文件或目录的名称为0
 	if($d=='.' || $d == '..'){//判断是否为.或..，默认都会有
 	continue;
 	}
 echo $d."<br />";
 if(is_dir($path.'/'.$d)){//如果为目录
 showdir($path.'/'.$d);//继续读取该目录下的目录或文件
 }
 }
}
 
#$path = './';//当前目录
$path = '/data0';//当前目录
showdir($path);


/* 
/*我的程序在国外的SREVER上，自己编的程序存放到哪，我很难记清。 
所以编了一个简单的目录递归函数，查看我的程序，很方便的。 
 
function tree($directory) 
{ 
$mydir=dir($directory); 
echo "<ul>"; 
while($file=$mydir->read()){ 
if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!="..")){ 
echo "<li><font color='#ff00cc'><b>$file</b></font></li>"; 
tree("$directory/$file"); 
}else{ 
echo "<li>$file</li>"; 
} 
} 
echo "</ul>"; 
$mydir->close(); 
} 
//start the program 
echo "<h2>目录为粉红色</h2>"; 
tree("."); 
?> 
*/

?>
 
