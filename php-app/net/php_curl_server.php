<?php
/*
*/
//接受php页面远程服务器：
if (isset($_POST['name'])) {
    if (!empty($_POST['name'])) {
        echo '您好，',$_POST['name'].'！';
    }
}
