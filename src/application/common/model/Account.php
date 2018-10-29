<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-04-04 11:49:55
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-04-04 15:03:19
 */

namespace app\common\model;

use Helper;

class Account extends CommonModel{

	protected $readonly = ["userid", "wx_openid"];

	public static function getByAccessToken($access_token){
		return static::get(["access_token" => $access_token]);
	}

	public static function saveInfo($wx_openid, $session_key){
		$account = static::get(["wx_openid" => $wx_openid]);

		if ( empty($account) ) {
			$account = new self();
			$account->wx_openid = $wx_openid;
			$account->access_token = strtolower(Helper::zstrGuid());
			$account->datetime = date("Y-m-d H:i:s");
			$account->uuid = Helper::zstrGuid();
		}

		$account->session_key = $session_key;
		$account->dateline = date("Y-m-d H:i:s");
		$result = $account->save();

		return $result ? $account->toArray() : false;
	}
}