<?php

namespace app\applet\controller;

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
	public function _error($title = "404", $content = "Not found.", $data = []){
		return rt($title . " : " . $content, 0, $data);
	}

	// 数据验证
    public function validateData($data, $validate, $message = [], $batch = false, $callback = null){
        $r = rt("数据验证成功", 1);
        $checked = $this->validate($data, $validate, $message, $batch, $callback);
        if ($checked !== true) {
            $r = rt($checked, 0);
        }

        return $r;
    }
}