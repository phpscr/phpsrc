<?php
//参数
$uri = 'http://10.210.132.20/phpsrc/php-app/net/php_curl_server.php';
$chars='123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
$length='3';
$out_file='';
// 参数数组
$data = array(
            'name' => 'tanteng',
             // 'password' => 'password'
            );
//随机文件名
/**
  * 产生随机字符串
  *
  * @param int $length 输出长度
  * @param string $chars 可选的 ，默认为 0123456789
  * @return string 字符串
  */
function randfile($length, $chars) {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}
$tt=randfile($length,$chars);
$out_file=$tt;
//使用curl
$ch = curl_init();
// print_r($ch);
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$return = curl_exec($ch);
curl_close($ch);
/*
if(file_exists($out_file))
{
    echo "当前目录中，文件".$out_file."存在".PHP_EOL;
   // exit;
    unlink($out_file);
}
else
{
  echo "当前目录中，文件".$out_file."不存在".PHP_EOL;
}
*/
print_r($return.PHP_EOL);
file_put_contents("$out_file","$return".PHP_EOL,FILE_APPEND|LOCK_EX);
//file_put_contents("$out_file","$return".PHP_EOL);

?>
