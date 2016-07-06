
<!-- //PHP抓取天气预报绝对精简代码 -->
< form method="post"> 
< input name="a" type="text" id="a" /> 
< input type="submit" name="Submit" value="查" /> 
< /form> 
< ?php  
$city = $_REQUEST["a"];  
if ($city=="")  
{  
    $city="菏泽";  
}  
$url = 'http://www.baidu.com/s?wd='.$city.'天气';   
$lines_array = file($url);   
$lines_string = implode('', $lines_array);   
eregi("今天（(.*)今日气象指数", $lines_string, $body);   
$body[0]=strip_tags($body[0]);  
$body[0] = str_replace(" 今日气象指数","",$body[0]);  
$body[0] = str_replace(" ","",$body[0]);  
echo $city.$body[0];  
?> 

