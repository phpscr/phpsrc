<?php
    $ch = curl_init (); 
    curl_setopt($ch, CURLOPT_URL, 'http://www.google.com.hk'); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 200); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE); 
    curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
#curl_setopt( $ch, CURLOPT_POSTFIELDS, "username=".$username."&password=".$password ); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
    curl_exec($ch); 
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
    echo $httpCode;

?>
