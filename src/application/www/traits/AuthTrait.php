<?php

namespace app\www\traits;

use think\Session;

trait AuthTrait {
	protected $userinfo;

	protected function _init(){
		
		$this->userinfo = array(
			"name" => "guest",
			"device" => zdevice_get(),
			"weixin" => zdevice_is_weixin(),
			"clientip" => $this->request->ip()
		);

		$this->userinfo["id"] = md5(http_build_query($this->userinfo));

		$this->init();
    }

    protected function init(){

    }
}