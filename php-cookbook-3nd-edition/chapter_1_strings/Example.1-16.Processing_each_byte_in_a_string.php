
<?php
//Example 1-16. Processing each byte in a string
$string = "This weekend, I'm going shopping for a pet chicken.";
$vowels = 0;
for ($i = 0, $j = strlen($string); $i < $j; $i++) {
        
	echo $string[$i];
	echo "*****";
	echo "\n";
	if (strstr('aeiouAEIOU',$string[$i])) {
	$vowels++;
	echo "\n";
	}
}

print "\n";
print $string;
print "\n";
print $vowels;

?>

