<?php 
$to = "uahoo@sina.com"; //设置收件人 
$subject = "This is a test"; //设置E-mail主题 
//设置E-mail内容：
$message = " 
<html> 
<head> 
<title>This is a test</title> 
</head> 
<body> 
<p>Dear all,</p> 
<p>This is a test for HTML mail sending.</p> 
</body> 
</html> 
"; 
//设置邮件头Content-type header 
$header = "MIME-Version: 1.0/r/n"; //设置MIME版本 
$header .= "Content-type: text/html; charset=iso-8859-1/r/n"; //设置内容类型和字符集 
//设置收件人、发件人信息 
$header .= "To: Simon <simon@mailexample.com>, Elaine <elaine@mailexample.com>/r/n"; //设置收件人 
$header .= "From: PHP Tester <phptester@163.com>/r/n"; //设置发件人 
$header .= "Cc: Peter@mailexample.com/r/n"; // 设置抄送 
$header .= "Bcc: David@mailexample.com/r/n"; // 设置暗抄 
echo $header; 
mail($to, $subject, $message, $header); //发送邮件 
?>
