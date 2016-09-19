<?php
/**
 * 点击式验证码 by xiaopiao
 */
class xpClickCode {
	public $font;
	public $format = 'png';
	public $type = 0;

	protected $image;
	protected $bg;
	protected $color;

	private $vc_session = NULL;
	private $tempArray = array();
	private $dotXY = array();
	private $ClickArray = 0;

	public function  __construct() {
		$this->font = str_replace('\\', '/', dirname(__FILE__)).'/click/font.ttf';
		$this->type = rand(0, 3);
		$this->image = imagecreate(200, 100);
		$this->bg = imagecreatefrompng(str_replace('\\', '/', dirname(__FILE__)).'/click/background.png');
		$this->color = imagecolorallocate($this->image, 0, 0, 0);
		imagecopy($this->image, $this->bg, 0, 0, 0, 0, 200, 100);

		$this->vc_session = &$_SESSION['verifysession'];
		$this->dotXY[] = array(3, 3, 38, 58);
		$this->dotXY[] = array(3, 59, 38, 96);
		$this->dotXY[] = array(39, 59, 78, 96);
		$this->dotXY[] = array(40, 20, 78, 58);
		$this->dotXY[] = array(80, 20, 121, 58);
		$this->dotXY[] = array(79, 59, 121, 96);
		$this->dotXY[] = array(122, 59, 161, 96);
		$this->dotXY[] = array(122, 20, 161, 58);
		$this->dotXY[] = array(162, 3, 196, 58);
		$this->dotXY[] = array(162, 59, 196, 96);

		$this->ClickArray = range(0, 9);
	}

	public function display() {
		$del = rand(2, 5); //随机删除字符数量
		switch($this->type){
			case 3: //唯一的数字
			case 2: //唯一的字母
				if($this->type==2){
					$randNum = rand(0, 24);
					$randNum = $randNum>=14?$randNum+1:$randNum; //去除字母“O”
					$this->tempArray['num'] = chr(65+$randNum);
					$this->ClickArray[0] = $this->tempArray['num']; //去除数字“0”
				}else{
					$curNum = rand(1, 9); //去除数字“0”
					$this->tempArray['num'] = $this->ClickArray[$curNum];
					foreach($this->ClickArray as $val=>$key){
						if($key!=$curNum){
							$randNum = rand(0, 24);
							$randNum = $randNum>=14?$randNum+1:$randNum; //去除字母“O”
							$this->ClickArray[$val] = chr(65+$randNum);
						}
					}
				}
				while(count($this->ClickArray)!=0){
					shuffle($this->ClickArray);
					shuffle($this->dotXY);
					$curXY = array_shift($this->dotXY);
					$dotX = rand($curXY[0], $curXY[2]-26);
					$dotY = rand($curXY[1], $curXY[3]-26);
					$curNum = array_shift($this->ClickArray);
					if($del>0&&$this->tempArray['num']!=$curNum&&rand(0, 1)==1){
						--$del;
					}else{
						$this->make_img($dotX, $dotY, 27, rand(0, 1), $curNum);
						if($this->tempArray['num']==$curNum){
							$this->tempArray['xy'] = $dotX.','.$dotY.',26';
						}
					}
				}
				break;
			case 1: //最小的数字
			default: //最大的数字
				$this->tempArray['num'] = $this->type==1?9:0;
				while(count($this->ClickArray)!=0){
					shuffle($this->ClickArray);
					shuffle($this->dotXY);
					$curXY = array_shift($this->dotXY);
					$dotX = rand($curXY[0], $curXY[2]-26);
					$dotY = rand($curXY[1], $curXY[3]-26);
					$curNum = array_shift($this->ClickArray);
					if($del>0&&rand(0, 1)==1){
						--$del;
					}else{
						$this->make_img($dotX, $dotY, 27, rand(0, 1), $curNum);
						if($this->type==1){
							if($this->tempArray['num']>$curNum){
								$this->tempArray['num'] = $curNum;
								$this->tempArray['xy'] = $dotX.','.$dotY.',26';
							}
						}else{
							if($this->tempArray['num']<$curNum){
								$this->tempArray['num'] = $curNum;
								$this->tempArray['xy'] = $dotX.','.$dotY.',26';
							}
						}
					}
				}
				break;
		}
		imagecopy($this->image, $this->bg, 46, 5, 200, 14*$this->type, 108, 14);
		$this->vc_session = isset($this->tempArray['xy'])?$this->tempArray['xy']:'';
		$this->show_img();
		exit();
	}

	public function verify($varX, $varY, $is_clear = TRUE) {
		$varArray = explode(',', $this->vc_session);
		$result = FALSE;
		if(
			$varX>=$varArray[0] && $varX<=$varArray[0]+$varArray[2]-1 &&
			$varY>=$varArray[1] && $varY<=$varArray[1]+$varArray[2]-1
		) {
			$result = TRUE;
		}
		if($is_clear) $this->vc_session = '';
		return $result;
	}

	public function show_img() {
		@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@header("Cache-Control: no-store, no-cache, must-revalidate");
		@header("Pragma: no-cache");
		if($this->format == 'png') {
			@header("Content-type: image/png");
			imagepng($this->image);
		}elseif($this->format == 'jpg') {
			@header("Content-type: image/jpeg");
			imagejpeg($this->image);
		}else{
			@header("Content-type: image/gif");
			imagegif($this->image);
		}
		imagedestroy($this->image);
		imagedestroy($this->bg);
	}

	public function make_img($dotX, $dotY, $width, $makeType='none', $xpText='') {
		$dotC = ceil($width/2);
		$dotCX = $dotX+$dotC-1;
		$dotCY = $dotY+$dotC-1;
		switch($makeType){
			case 1:
				imagearc($this->image, $dotCX, $dotCY, $width, $width, 0, 360, $this->color);
				break;
			default:
				$dotX2 = $dotX+$width-1;
				$dotY2 = $dotY+$width-1;
				imageline($this->image, $dotX, $dotY, $dotX2, $dotY, $this->color);
				imageline($this->image, $dotX, $dotY, $dotX, $dotY2, $this->color);
				imageline($this->image, $dotX, $dotY2, $dotX2, $dotY2, $this->color);
				imageline($this->image, $dotX2, $dotY, $dotX2, $dotY2, $this->color);
				break;
		}
		if(isset($xpText))imagestring($this->image, 5, $dotCX-4, $dotCY-7, $xpText, $this->color);
	}
}
/* End of this file */