<?php
session_start();
$folder = $_SESSION['folder'];
$filename = $folder . "/question.txt";

$file_handle = fopen($filename,"a+");
$comments = fread($file_handle,filesize($filename));
fclose($file_handle);

if(!empty($_POST['posted'])) {
$question1 = $_POST['question1'];
$file_handle = fopen($filename,"w+");
if ( flock($file_handle,LOCK_EX)) {
if(fwrite($file_handle,$question1) == FALSE{
        echo "Cannot writeto file ($filename)";
}
flock($file_handle,LOCK_UN);
}
fclose($file_handle);
header( "Location:page2.php");
} else {
?>
<html>
<head>
<title>Files & folders - On -line Survey </title>
</head>
<body>
<table border=0><tr><td>
Please enter your reponse to the following survey question:
</td></tr>

<tr bgcolor=lightblue><td>
