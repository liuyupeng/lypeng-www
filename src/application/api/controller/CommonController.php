<?php
namespace app\api\controller;

use Helper;
use think\Config;
use think\Controller;

class CommonController extends Controller{
	
	protected function _initialize(){
		Config::set("default_return_type", "json");

		// 控制器初始化
		$this->_init();
	}

    protected function _init(){
    	// 初始化
    }

    // 空方法
    public function _empty(){
    	return $this->_error("404", "The action does not exist.");
	}

	// 加载错误页面
	public function _error($title = "404", $content = "Not found."){
		return Helper::rt($title . " : " . $content, 0);
	}
}