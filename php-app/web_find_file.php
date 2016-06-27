<html>
 <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
     <title>php版文件查找（file search）</title>
      </head>
       <body>
         <form action="" method="post">
           <p> 文件查找（注：区分大小写）</p>
             <p>路径：<input type="text" name="path" /></p>
               <p>查找：<input type="text" name="key" /></p>
                 <p><input type="submit" name="sub" value=" 开 始 " /></p>
                   </form>
                    </body>
                    </html>
                    <?php
                    /*
                        * 注：区分大小写
                         * by: http://www.jb51.net
                          */
                    if(!empty($_POST['path'])&&!empty($_POST['key'])){
                         echo "在路径 ".$_POST['path']."/ 中查找 ".$_POST['key']." 的结果为：<hr/>";
                          $file_num = $dir_num = 0;
                           $r_file_num = $r_dir_num= 0;
                            $findFile = $_POST['key'];
                             function delDirAndFile( $dirName ){ 
                                   if ( $handle = @opendir( "$dirName" ) ) {
                                          while ( false !== ( $item = readdir( $handle ) ) ) {  
                                                  if ( $item != "." && $item != ".." ) {  
                                                           if ( is_dir( "$dirName/$item" ) ) {  
                                                                     delDirAndFile( "$dirName/$item" );
                                                                          } else {  
                                                                                    $GLOBALS['file_num']++;
                                                                                          if(strstr($item,$GLOBALS['findFile'])){
                                                                                                     echo " <span><b> $dirName/$item </b></span><br />\n";
                                                                                                            $GLOBALS['r_file_num']++;
                                                                                                                  }
                                                                                               }  
                                                               }
                                                     }
                                             closedir( $handle );  
                                                $GLOBALS['dir_num']++;
                                                   if(strstr($dirName,$GLOBALS['findFile'])){
                                                           $loop = explode($GLOBALS['findFile'],$dirName);
                                                               $countArr = count($loop)-1;
                                                                   if(empty($loop[$countArr])){
                                                                            echo " <span style='color:#297C79;'><b> $dirName </b></span><br />\n";
                                                                                 $GLOBALS['r_dir_num']++;
                                                                                     }
                                                                      }
                                                     }else{
                                                            die("没有此路径！");
                                                              }
                                    }

                              delDirAndFile($_POST['path']);
                               echo "<hr/>本次共搜索到".$file_num."个文件，文件夹".$dir_num."个<br/>";
                                echo "<hr/>符合结果的共".$r_file_num."个文件，文件夹".$r_dir_num."个<br/>";
                    }

?>

