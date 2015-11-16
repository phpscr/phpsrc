<?php
/*我的程序在国外的SREVER上，自己编的程序存放到哪，我很难记清。
所以编了一个简单的目录递归函数，查看我的程序，很方便的。
*/
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