<?php
//Example 1-19. Reversing a string by word
$s = "Once upon a time there was a turtle.";
// break the string up into words
$words = explode(' ',$s);
// reverse the array of words
$words = array_reverse($words);
// rebuild the string
$s = implode(' ',$words);
print $s;
//Example 1-19 prints:
//turtle. a was there time a upon Once

?>
