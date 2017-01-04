<?php
require_once('global.php');

header("Content-Type: text/html; charset=$db_charset");
if($type=='color'){
	include('template/wind/color.htm');
}elseif($type=='table'){
	include('template/wind/table.htm');
}elseif($type=='sale'){
	include('template/wind/sale.htm');
}elseif($type=='download'){
	include('template/wind/download.htm');
}
?>