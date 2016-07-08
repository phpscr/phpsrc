#
<?php
//
$_PWD="/data0/github";
$_FILE="md5_file.txt";
//exist file
if(file_exists("$_FILE")){
    if(unlink("$_FILE")){
        echo 'Delete file "$_FILE"!';
    }
}
//list 
$results = shell_exec ( "find $_PWD ") ;
//echo $results ;
$lines = preg_split('/\n/', $results, -1, PREG_SPLIT_NO_EMPTY);
//print_r($chars);
$fp=fopen("$_FILE",'a');
foreach($lines as $line){
    echo "$line \n";
    $file_of_md5=md5_file($line);
    $md5_line="$line"."|"."$file_of_md5"."\n";
    fwrite($fp,$md5_line);
}

fclose($fp);
?>
