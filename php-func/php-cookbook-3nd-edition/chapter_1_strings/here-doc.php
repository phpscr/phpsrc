<?php
/*
print <<< END
This is a file !
 I can not tell this file!
*/
$html = <<< END
//<div class="$divClass">
<div class="divClass">
//<ul class="$ulClass">
<ul class="ulClass">
<li>
END

. $listItem . '</li></div>';

print $html;
END
 ?>
