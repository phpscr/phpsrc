<?php
//This script checks specific ports so you need to have the correct port open on the server for this to work. 
//E.g if i have a windows domain controller and it is servering LDAP then the following would be used to check it is online: 

chkServer("www.github.com", "80"); 
chkServer("www.sina.com.cn", "80"); 

//-------------------------------------------------------- 
// check if a server is up by connecting to a port 
function chkServer($host, $port) 
{   
    $hostip = @gethostbyname($host); // resloves IP from Hostname returns hostname on failure 
    echo "arg1: ".$hostip.PHP_EOL;

    if ($hostip == $host) // if the IP is not resloved 
    { 
        echo "Server is down or does not exist".PHP_EOL; 
    } 
    else 
    { 
        if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5)) // attempt to connect 
        { 
            echo "Server is down".PHP_EOL; 
        } 
        else 
        { 
            echo "Server is up".PHP_EOL; 
            if ($x) 
            { 
                @fclose($x); //close connection 
            } 
        }  
    } 
} 
?> 
