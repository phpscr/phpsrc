<?php
!function_exists('readover') && exit('Forbidden');
//===========bank===========//
 if($H_name=='bank'){
	 require_once(R_P.'hack/bank.php');
}
//===========bank===========//

//===========colony===========//
 if($H_name=='colony'){
	 require_once(R_P.'hack/colony.php');
}
//===========colony===========//

//===========advert===========//
 if($H_name=='advert'){
	 require_once(R_P.'hack/');
}
//===========advert===========//

//===========new===========//
 if($H_name=='new'){
	 require_once(R_P.'hack/');
}
//===========new===========//

//===========medal===========//
 if($H_name=='medal'){
	 require_once(R_P.'hack/medal.php');
}
//===========medal===========//

//===========toolcenter===========//
 if($H_name=='toolcenter'){
	 require_once(R_P.'hack/toolcenter.php');
}
//===========toolcenter===========//

//===========blog===========//
 if($H_name=='blog'){
	 require_once(R_P.'hack/blog.php');
}
//===========blog===========//

?>