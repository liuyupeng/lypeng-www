<?php
namespace app\www\controller;

class ErrorController extends CommonController {

    public function index(){
    	return $this->_error("Not Found", "The controller does not exist.");
    }
}