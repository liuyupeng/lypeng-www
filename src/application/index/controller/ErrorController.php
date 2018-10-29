<?php
namespace app\index\controller;

class ErrorController extends CommonController {

    public function index(){
    	return $this->_error("404", "The controller does not exist.");
    }
}