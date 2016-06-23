<?php
function rec_listFiles( $from = '.')
{
    if(! is_dir($from))
      return false;

    $files = array();
    if( $dh = opendir($from))
    {
        while( false !== ($file = readdir($dh)))
        {
            // Skip '.' and '..'
            if( $file == '.' || $file == '..')
              continue;
            $path = $from . '/' . $file;
            if( is_dir($path) )
              $files += rec_listFiles($path);
            else
              $files[] = $path;
        }
        closedir($dh);
    }
    return $files;
}
$ret=rec_listFiles();
print_r ($ret);
?>

