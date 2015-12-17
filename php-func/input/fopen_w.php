<?php


$file_name="a.txt";
if( !is_writeable($file_name)){
	$fh=fopen("a.txt","w");
}


