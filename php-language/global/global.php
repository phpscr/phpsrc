<?PHP
$w3sky = 1;
$w3sky2 = 2;
function Sum(){
global $w3sky, $w3sky2;
$w3sky2 = $w3sky + $w3sky2;
}
Sum();
echo $w3sky2;
?>
