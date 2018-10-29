<?php

namespace app\api\traits;

use app\common\model\Account;

trait AuthTrait {

	protected $identity;

	protected static $signatures = [
		"index/login" => "0a833c9db8960976d822e9eb5a504b59",
		"index/weight" => "aebb47633215db4d",
	];

	protected function _init(){
		$theAction = $this->request->controller() . "/" . $this->request->action();
		$theAction = strtolower($theAction);

		$signature = $this->request->param("signature");
		if (array_key_exists($theAction, static::$signatures)) {
			if ($signature !== static::$signatures[$theAction]) {
				$result = $this->_error("签名错误", "请携带正确的签名参数。");
				exit(json_encode($result));
			}
		} else {
			$userinfo = Account::getByAccessToken($signature);
			if ( empty($userinfo) ) {
				$result = $this->_error("未登录", "请重新登录。");
				exit(json_encode($result));
			}

			$this->identity = $userinfo;
		}
    }
}