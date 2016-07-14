<?php  
//$file = dirname(__FILE__).'/'.uniqid().'.'.'txt';  
$file = dirname(__FILE__).'/'.'readme'.'.'.'txt';  
//var_dump($file);
echo L_rename($file);  
function L_rename($file){  
    $iCount = 0;  
    $File_type = strrchr($file, '.');  
    $FilePath = substr($file, 0, strrpos($file, '.'));  
    echo($FilePath)."ttttt"."\n";
    while (true) {  
        if (is_file($file)) {  
            ++$iCount;  
          //  print_r($iCount);
            $file = $FilePath . '('. $iCount .')' . $File_type;  
        }else{  
            break;  
        }  
    }  
    if (fopen($file, 'w')) {$Msg = '创建成功 '.$file;}  
    return $Msg;  
}  
?>
