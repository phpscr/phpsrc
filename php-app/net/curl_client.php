<?php

$uri = 'http://10.210.132.20/php_curl_server.php';
// 参数数组
$data = array(
'name' => 'tanteng',
 // 'password' => 'password'
);
$ch = curl_init();
// print_r($ch);
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$return = curl_exec($ch);
curl_close($ch);
print_r($return);
