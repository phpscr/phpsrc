<?php
$shortopts  = "";
$shortopts .= "f:";  // Required value
$shortopts .= "v::"; // Optional value
$shortopts .= "abc"; // These options do not accept values

$longopts  = array(
                "required:",     // Required value
                "optional::",    // Optional value
                "option",        // No value
                "opt",           // No value
       );
$options = getopt($shortopts, $longopts);

/*if ( $_SERVER['argc'] == 0 ){
echo " error" ;
}
*/

//var_dump($argv);
//print("*****\n");
//var_dump($argc);
print("*****\n");
echo  $argv[0];
print("*****\n");
echo  $argv[2];
print("*****\n");
echo  $argc;
/*
if(isset($argv)){
    echo "ok";
}
else
{
    echo "error";
}
*/
//var_dump($options);
$lixin;
//var_dump($lixin) ;

?> 
