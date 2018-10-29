<?php
namespace app\admini\traits;

use think\Db;
use think\Config;
use think\Session;

use app\admini\model\Admin;
use app\admini\model\AdminRole;
use app\admini\model\Privilege;

trait AuthTrait {

	protected $routes;
	protected $userinfo;
	protected $breadcrumbs = [];
	protected $privilegeAll = [];
	protected $privileges;

	protected function _initialize(){
		if ($ajax = $this->request->param(Config::get("var_ajax"))) {
			Config::set("default_return_type", "json");
		}

		// 不需要登录的路由-控制器/方法名
		$filterActions = ["Index/login"];
		
		// 获取当前路由-控制器/方法名
		$this->routes = $this->request->controller() . "/" . $this->request->action();

		// 验证用户登录
		if (!zstr_in_array($this->routes, $filterActions)) {
			if ($this->doAuthCheck() == false) {
				if ($this->request->isAjax()) {
					return self::rt("您还没有登录请先登录", -1);
				} else {
					return $this->redirect(url("index/login"));
				}
			}

			$this->userinfo = $this->doAuthGet();

			$this->doAuthAdmini();
		}

		$this->_init();
    }

	protected function _init(){
		// 初始化
	}

    // 验证管理员权限
	protected function doAuthAdmini(){
		$role_id = $this->userinfo["role_id"];

		$adminRole = AdminRole::get($role_id);
		if (empty($adminRole)) {
			exit($this->_error("权限错误", "角色不存在或已删除"));
		}

		$this->privileges = $adminRole->getRolePrivilege();
		if ($this->privileges != "ALL") {
			$privilegeList = Privilege::where(["disabled" => 0])->field(["route_name"])->select();
		
			$this->privilegeAll = [];
			foreach ($privilegeList as $key => $priv) {
				$this->privilegeAll[] = $priv["route_name"];
			}

			$full_routes = "Admini/" . $this->routes;
			if (zstr_in_array($full_routes, $this->privilegeAll)) {
				if (!zstr_in_array($full_routes, $this->privileges)) {
					exit($this->_error("权限错误", "您没有权限进行此操作"));
				}
			}
		}
	}


	// 获取用户登录信息AuthKey
	protected function getAuthKey(){
    	return "CN_LYPENG_TP5_USER_SESSION";
    }

    // 设置用户登录信息
	protected function doAuthSet($authData){
		Session::set($this->getAuthKey(), $authData);
	}

	// 获取用户登录信息
	protected function doAuthGet(){
		return Session::get($this->getAuthKey());
	}

	// 检测用户是否登录
	protected function doAuthCheck(){
		return Session::has($this->getAuthKey());
	}

	// 注销用户登录信息
	protected function doAuthDelete(){
		Session::delete($this->getAuthKey());
		return !$this->doAuthCheck();
	}

	// 设置面包屑导航
	protected function setBreadcrumbs($label, $url = "", $overflow = false, $assign = true){
		if ($overflow == true) $this->breadcrumbs = [];
		$this->breadcrumbs[] = ["label" => $label, "url" => $url];
		
		if ($assign == true) {
			$this->assign("breadcrumbs", $this->breadcrumbs);
		}
	}
}