<?php
require_once("global.php");
require_once("require/header.php");
ob_end_clean();
ob_start();
$a=readover('template/wind/readtpl.htm');

$a=str_replace(array('<!--css-->','<!--','-->','<?php'),'',$a);
//$a;
eval($a);
?>