<?php  
//抓取网页内容  
set_time_limit(0);  

$line = file_get_contents("http://news.sina.com.cn/");   
//var_dump($line);
$line = strstr( $line, "<div>");  
echo ($line)."\n";
$line=explode("</div>",$line);  

$count=count($line);  
for($i=0;$i<$count;$i++){  
    eregi ("<pubDate>(.*)</pubDate>",$line[$i],$time);  
    //取得链结  
    if (eregi ("<link>(.*)</link>",$line[$i],$out)) {   
        $news = file_get_contents("$out[1]");  
        if(eregi ("<h1>(.*)</h1>",$news,$title)){       //匹配标题  
            preg_replace("/ /","",$title[1]);       //去除标题中的空格  
            $title[1] = trim($title[1]);  
            $title[1]=strip_tags($title[1]);                    //去除标题中的的HTML标记  
            //$title[1] = addslashes($title[1]);  

            //检查是否实视频，是视频则不抓取  
            if(strpos($title[1],"视频",0)!==flase){  
                echo "不抓取视频！";  
                //exit;  
                return false;  
            }  
            echo $title[1];  
            echo "<br>";  
        }else{  
            $title[1]="";  
        }  

        $dt=getdate();  
        $hours=$dt[hours];  
        $publish_time=$dt[year]."-".$dt[mon]."-".$dt[mday]."-".$hours."-".$dt[minutes]."-".$dt[seconds];  
        if($title[1]!=""){  
            $sql_title="select title from news where title='$title[1]'";  
            $result=$DB->fetch_one_array($sql_title);  
            if(empty($result)){  

                //取出正文部分  
                $news = strstr($news,"<!--正文内容开始-->");  
                $news = explode("<!--正文内容结束-->", $news);  



                //匹配正文内容  
                if(eregi ("<p>(.*)</p>",$news[0],$content)){  
                    echo $content[1];  
                    echo "<br>";  
                }else{  
                    $content[1]="";  
                }  


                //匹配图片路径  
                $picture_name="";  
                $img="";  
                $news=explode("border",$news[0]);  
                for($j=0;$j<count($news);$j++){  
                    if(preg_match("/http:\/\/i(.*)\.(jpeg|png|jpg)/i",$news[$j],$picture)){  
                        $img[$j]=GrabImage($picture[0],"");   

                        if($img[$j]){  
                            $imgpath[$j]=dirname(__FILE__)."/pictures/".$img[$j];   
                            echo '<pre><img src='.$imgpath[$j].'></pre>';   
                        }else{  
                            $img[$j]="";  
                        }  
                    }else{  
                        $picture[0]="";  
                        $img[$j]="";  
                    }  
                    $picture_name.=$img[$j];  
                }  

            }     
        }  
    }  
}  

?>  


<?php  
// Function: 获取远程图片并把它保存到本地  
// $url 是远程图片的完整URL地址，不能为空。  
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期自动生成.  
function GrabImage($url,$filename="") {   
    if($url==""){  
        return false;  
    }  
    if($filename=="") {   
        $ext=strrchr($url,".");   
        $temext = strrchr(strtolower($url),".");   
        if($temext!=".jpg"&$temext!=".jpeg"){  
            return false;  
        }  
        $filename=date("dMYHis").$ext;  
        $filepath=dirname(__FILE__)."/pictures/".$filename;   
    }   

    ob_start();   
    readfile($url);   
    $img = ob_get_contents();   
    ob_end_clean();   
    $size = strlen($img);   

    $fp2=fopen($filepath, "a");   
    fwrite($fp2,$img);   
    fclose($fp2);   

    return $filename;   
}   
?>
