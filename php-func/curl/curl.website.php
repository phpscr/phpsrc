<?php
$ch = curl_init(); 
$c_url = 'http://www.sina.com.cn'; 
$c_url_data = "product_&type=".$type.""; 
curl_setopt($ch, CURLOPT_URL,$c_url); 
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $c_url_data); 
echo $result = curl_exec($ch); 
curl_close ($ch); 
unset($ch); 
?>
