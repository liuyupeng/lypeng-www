<?php

namespace app\applet\traits;

use app\applet\model\Account;

trait AuthTrait {

	protected $identity;

	protected static $signatures = [
		"index/wxlogin" => "4d962ff2b613d9e6"
	];

	protected function _init(){
		$theAction = $this->request->controller() . "/" . $this->request->action();
		$theAction = strtolower($theAction);

		$signature = $this->request->param("signature");
		if (array_key_exists($theAction, static::$signatures)) {
			if ($signature !== static::$signatures[$theAction]) {
				$result = $this->_error("签名错误", "请携带正确的签名参数。", ["error" => "signature"]);
				exit(json_encode($result));
			}
		} else {
			$userinfo = Account::getByAccessToken($signature);
			if ( empty($userinfo) ) {
				$result = $this->_error("未登录", "请重新登录。", ["error" => "auth"]);
				exit(json_encode($result));
			}

			$this->identity = $userinfo;
		}
    }
}