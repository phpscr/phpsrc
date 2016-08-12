<?php
 class expam {
    public $file_name = '';

    public function _construct($file_name){
        $this->file_name = $file_name;
        //echo $filename;
    }
    public function echp(){
         echo $this->file_name;
    }
}

