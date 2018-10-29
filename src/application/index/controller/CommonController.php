<?php
namespace app\index\controller;

use think\Config;
use think\Controller;

class CommonController extends Controller{
	
	protected static $result = ["res" => 1, "msg" => "SUCCESS", "data" => null];

	protected function _initialize(){
		// if (zdevice_get() == "computer") {
		// 	$this->redirect("http://www.lypeng.cn");
		// 	exit();
		// }

		if ($ajax = $this->request->param(Config::get("var_ajax"))) {
			Config::set("default_return_type", "json");
		}

		// 控制器初始化
		$this->_init();
	}

	// 初始化
    protected function _init(){
		// $article = Article::all(function($query){
		// 	$query->where("disabled", 0)->where("FIND_IN_SET(1, categorys)")->limit(3)->order("weight", "desc");
		// });

		// if ($article) {
		// 	$article = collection($article)->toArray();
		// }
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
    	return $this->_error("404", "The action does not exist.");
	}

	// 加载错误页面
	public function _error($title = "404", $content = "Not found."){
		$this->assign("title", $title);
    	$this->assign("content", $content);
    	return $this->fetch("public:error");
	}
}