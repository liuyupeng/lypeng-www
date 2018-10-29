<?php

/**
 * 用户模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:05:33
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 14:21:15
 */

namespace app\applet\model;

class Account extends BaseModel{

	protected $readonly = ["userid", "wx_openid"];

	public static function getByAccessToken($access_token){
		return static::get(["access_token" => $access_token]);
	}

	public static function saveInfo($wx_openid, $session_key){
		$account = static::get(["wx_openid" => $wx_openid]);

		if ( empty($account) ) {
			$account = new Account();
			$account->wx_openid = $wx_openid;
			$account->access_token = strtolower(zstr_guid());
			$account->datetime = date("Y-m-d H:i:s");
		}

		$account->session_key = $session_key;
		$account->dateline = date("Y-m-d H:i:s");
		$result = $account->save();

		return $result ? $account->toArray() : false;
	}
}