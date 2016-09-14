<?php
include 'inc/conn.php';
$act = getvar('act');
$id = getvar('id');
switch ($act){
	case "del":
		del($id);
		break;
	case "check":
		check($id);
		break;
	case "addreplay":
		addreplay($id);
		break;
}
?>