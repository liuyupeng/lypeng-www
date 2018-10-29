<?php

/**
 * 空控制器
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:38:09
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-24 14:44:49
 */

namespace app\admini\controller;

class ErrorController extends CommonController {

    public function index(){
    	return $this->_error("Not Found", "The controller does not exist.");
    }
}