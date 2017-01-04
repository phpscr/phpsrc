<?php
$fp = fsockopen("www.github.com", 80);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "Data sent by socket");
    $content = "";
    while (!feof($fp)) {  //This looped forever
        $content .= fread($fp, 1024);
    }
    fclose($fp);
    echo $content;
}
?>

