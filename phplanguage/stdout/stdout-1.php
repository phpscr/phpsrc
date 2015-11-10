<?php
while($line = fopen('php://stdin','r')){
    echo fgets($line);
}
?>
