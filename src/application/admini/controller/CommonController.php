<?php

/**
 * 公共控制器
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:38:09
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-04 09:05:49
 */

namespace app\admini\controller;

use think\Config;
use think\Controller;

class CommonController extends Controller{

	protected static $result = ["res" => 1, "msg" => "SUCCESS", "data" => null];

	protected function _initialize(){
		
	}

    // 构建返回数据结构
    protected static function rt($msg = "SUCCESS", $res = 1, $data = null){
    	self::$result["res"] = intval($res);
    	self::$result["msg"] = $msg;
    	self::$result["data"] = $data;

    	return self::$result;
	}

    // 空方法
    public function _empty(){
    	return $this->_error("Not Found", "The action does not exist.");
	}

	// 加载错误页面
	public function _error($title = "Not Found", $content = "抱歉，找不到您要查看的页面。"){
        if ($this->request->isAjax() == true) {
            return rt($title . ":" . $content, 0);
        } else {
            $this->assign("title", $title);
            $this->assign("content", $content);
            return $this->fetch("public:error");
        }
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