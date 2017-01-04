<?php
!function_exists('readover') && exit('Forbidden');

function ImgWaterMark($source,$w_pos=0,$w_img="",$w_text="",$w_font=5,$w_color="#FF0000",$w_pct,$w_quality){
	global $imgdir;
    if(!empty($source) && file_exists($source)){
        $source_info = getimagesize($source);
        $source_w    = $source_info[0];
        $source_h    = $source_info[1];
        switch($source_info[2]){
            case 1 :
				$source_img = imagecreatefromgif($source);
				break;
            case 2 :
				$source_img = imagecreatefromjpeg($source);
				break;
            case 3 :
				$source_img = imagecreatefrompng($source);
				break;
            default :
				return;
        }
    }else{
        return;
    }
    if(!empty($w_img) && file_exists("$imgdir/water/$w_img")){
        $ifWaterImage = 1;
        $water_info   = getimagesize("$imgdir/water/$w_img");
        $width        = $water_info[0];
        $height       = $water_info[1];
        switch($water_info[2]){
            case 1 :
				$water_img = imagecreatefromgif("$imgdir/water/$w_img");
				break;
            case 2 :
				$water_img = imagecreatefromjpeg("$imgdir/water/$w_img");
				break;
            case 3 :
				$water_img = imagecreatefrompng("$imgdir/water/$w_img");
				break;
            default :
				return;
        }
    }else{
		$ifWaterImage = 0;
        $temp = imagettfbbox(ceil($w_font*2.5),0,"./cour.ttf",$w_text);//取得使用 TrueType 字体的文本的范围
        $width = $temp[2] - $temp[6];
        $height = $temp[3] - $temp[7];
        unset($temp);
    }
    switch($w_pos){
        case 0:
            $wX = rand(0,($source_w - $width));
            $wY = rand(0,($source_h - $height));
            break;
        case 1:
            $wX = 5;
            $wY = 5;
            break;
        case 2:
            $wX = ($source_w - $width) / 2;
            $wY = 0;
            break;
        case 3:
            $wX = $source_w - $width;
            $wY = 0;
            break;
        case 4:
            $wX = 0;
            $wY = $source_h - $height;
            break;
		case 5:
            $wX = ($source_w - $width) / 2;
            $wY = $source_h - $height;
            break;
		case 6:
			$wX = $source_w - $width;
			$wY = $source_h - $height;
			break;
        default:
			$wX = ($source_w - $width) / 2;
			$wY = ($source_h - $height) / 2;
            break;
    }
    imagealphablending($source_img, true);

    if($ifWaterImage){
		imagecopymerge($source_img, $water_img, $wX, $wY, 0, 0, $width,$height,$w_pct);
    }else{
        if(!empty($w_color) && (strlen($w_color)==7)){
            $R = hexdec(substr($w_color,1,2));
            $G = hexdec(substr($w_color,3,2));
            $B = hexdec(substr($w_color,5));
        }else{
            return;
        }
        imagestring($source_img,$w_font,$wX,$wY,$w_text,imagecolorallocate($source_img,$R,$G,$B));
    }

    P_unlink($source);
    switch($source_info[2]){
        case 1 :
			imagegif($source_img,$source);
			break;
        case 2 :
			imagejpeg($source_img,$source,$w_quality);
			break;
        case 3 :
			imagepng($source_img,$source);
			break;
        default :
			return;
    }

    if(isset($water_info)){
		unset($water_info);
	}
    if(isset($water_img)){
		imagedestroy($water_img);
	}
    unset($source_info);
    imagedestroy($source_img);
}
?>