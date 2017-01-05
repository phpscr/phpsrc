
<?php
/*注意：curl函数在php中默认是不被支持的，如果需要使用curl函数我们需在改一改你的php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
例1
 代码如下
复制代码
$uri = "http://tanteng.duapp.com/test.php";
// 参数数组
$data = array (
        'name' => 'tanteng'
// 'password' => 'password'
);
 
$ch = curl_init ();
// print_r($ch);
curl_setopt ( $ch, CURLOPT_URL, $uri );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
$return = curl_exec ( $ch );
curl_close ( $ch );
 
print_r($return);
接受php页面远程服务器：
<?php
if(isset($_POST['name'])){
    if(!empty($_POST['name'])){
        echo '您好，',$_POST['name'].'！';
    }
}
?>
*/
/*例2
用CURL模拟POST请求抓取邮编与地址
完整代码：
 代码如下
复制代码

#!/usr/local/php/bin/php
<?php
*/
$runtime = new runtime ();
$runtime->start ();

$cookie_jar = tempnam('/tmp','cookie');

$filename = $argv[1];
$start_num= $argv[2];
$end_num  = $argv[3];

for($i=$start_num; $i<$end_num; $i++){
$zip = sprintf('6s',$i);

$fields_post = array(
'postcode' => $zip,
'queryKind' => 2,
'reqCode' => 'gotoSearch',
'search_button.x'=>37,
'search_button.y'=>12
);

$fields_string = http_build_query ( $fields_post, '&' );
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, "URL?reqCode=gotoSearch&queryKind=2&postcode=".$zip);
 curl_setopt($ch, CURLOPT_HEADER, true);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_POST, true);
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120 );
 curl_setopt($ch, CURLOPT_REFERER, $refer );
 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login );
 curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar );
 curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar );
 curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
 curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
 curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );

 $data = curl_exec($ch);
 preg_match_all('/id="table1">[s]*?<tr>[s]*?<td class="maintext">[sS]*?</td>[s]*?</tr>/', $data, $matches);
 if (!$handle = fopen($filename, 'a+')) {
 echo "不能打开文件 $filename";
 echo "n";
 exit;
 }
if (fwrite($handle, $matches[0][1]) === FALSE) {
 echo "不能写入到文件 $filename";
 echo "n";
 exit;
}

echo "成功地将 $somecontent 写入到文件$filename";
echo "n";

fclose($handle);
curl_close($ch);
}
class runtime
{
var $StartTime = 0;
var $StopTime = 0;
function get_microtime()
 {
 list($usec,$sec)=explode(' ',microtime());return((float)$usec+(float)$sec);
}
 function start()
 {
 $this->StartTime=$this->get_microtime();
}
function stop(){
$this->StopTime=$this->get_microtime();
}
 function spent()
{
 return ($this->StopTime-$this->StartTime);
 }
}
$runtime->stop ();

$con = 'Processed in'.$runtime->spent().'seconds';
echo 'Processed in'. $runtime->spent().'seconds';
// 模拟POST请求 提交数据或上传文件 .
// 代码如下
//http://www.a.com/a.php
//发送POST请求
function execUpload(){

$file = '/doucment/Readme.txt';
$ch = curl_init();
$post_data = array(
    'loginfield' => 'username',
    'username' => 'ybb',
    'password' => '123456',
'file' => '@d:usrwwwtranslatedocumentReadme.txt'
);
curl_setopt($ch, CURLOPT_HEADER, false);
//启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
curl_setopt($ch, CURLOPT_POST, true); 
curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
curl_setopt($ch, CURLOPT_URL, 'http://www.b.com/handleUpload.php');
$info= curl_exec($ch);
curl_close($ch);
  
print_r($info);
}
2.http://www.b.com/handleUpload.php
function handleUpload(){
print_r($_POST);
echo '===file upload info:';
print_r($_FILES);
}
