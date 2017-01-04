<?php
//判断手机版是否存在导航栏里
$sql = "SELECT id FROM " . $dou->table('nav') . " WHERE module = 'mobile'";
$num = $dou->num_rows($dou->query($sql));
if (!$num)
    $up_sql .= "INSERT INTO " . $dou->table('nav') . " VALUES(NULL,'mobile','手机版','-1','0','top','50');\n";
?>