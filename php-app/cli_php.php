
<?php 
// ask for input 
fwrite(STDOUT, "Enter your name: "); 

// get input 
$name = trim(fgets(STDIN)); 

// write input back 
fwrite(STDOUT, "Hello, $name!"); 

print_r($argv);

?> 
