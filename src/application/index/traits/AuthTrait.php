<?php

namespace app\index\traits;

use think\Session;

trait AuthTrait {

	protected $userinfo;

	protected function _init(){
		
		if ($this->doAuthCheck() == true) {
			$this->userinfo = $this->doAuthGet();
		} else {
			$this->userinfo = array(
				"name" => "guest",
				"device" => zdevice_get(),
				"weixin" => zdevice_is_weixin(),
				"clientip" => $this->request->ip(),
				"http_user_agent" => $_SERVER["HTTP_USER_AGENT"]
			);

			$this->userinfo["id"] = md5(http_build_query($this->userinfo));
			// $this->userinfo["id"] = md5(serialize($this->userinfo));
		}

		$this->init();
    }

    protected function init(){

    }

    protected function getAuthKey(){
    	return "CN_LYPENG_M_USER_SESSION";
    }

	protected function doAuthIndex(){
		$filterActions = ["index/login"];
	}

	protected function doAuthSet($authData){
		Session::set($this->getAuthKey(), $authData);
	}

	protected function doAuthGet(){
		return Session::get($this->getAuthKey());
	}

	protected function doAuthCheck(){
		return Session::has($this->getAuthKey());
	}

	protected function doAuthDelete(){
		Session::delete($this->getAuthKey());
		return !$this->doAuthCheck();
	}
}