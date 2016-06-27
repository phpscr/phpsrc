<?php
// 将一个文件读入数组。本例中通过 HTTP 从 URL 中取得 HTML 源文件。

#$lines = file('http://news.sina.com.cn/');
$str = file_get_contents('http://news.sina.com.cn');
echo gettype($str);
print_r($str);
// 在数组中循环，显示 HTML 的源文件并加上行号。
/*
foreach ($lines as $line_num => $line) {
        //echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
        echo "$line_num"."\n";
        //echo "$line"."\n";
}
*/
/*
// 另一个例子将 web 页面读入字符串。参见 file_get_contents()。

$html = implode('', file('http://www.example.com/'));

// 从 PHP 5 开始可以使用可选标记参数
$trimmed = file('somefile.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
*/
?> 
