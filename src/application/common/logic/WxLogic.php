<?php 
	
/**
 * 微信小程序API
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2018-04-01 19:20:29
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-04-04 13:17:41
 */

namespace app\common\logic;

use Helper;
use think\Config;
use think\Loader;

class WxLogic extends CommonLogic{
	
	private static $appid;
	private static $secret;

	private static $_instance;

	public static function getInstance(){
		if (empty(self::$_instance)) {
			static::initParam();
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	// 初始化参数
	public static function initParam(){
		$custom_config = Config::get("custom_config");
    	$wx_config = $custom_config["wx_config"];

		static::$appid = isset($wx_config["appid"]) ? $wx_config["appid"] : "";
		static::$secret = isset($wx_config["secret"]) ? $wx_config["secret"] : "";
	}

	// 通过code获取微信openid和session_key
	public function getSnsInfo($js_code){
		$params = [
			"appid" => static::$appid,
			"secret" => static::$secret,
			"js_code" => $js_code,
			"grant_type" => "authorization_code"
		];

		$url = "https://api.weixin.qq.com/sns/jscode2session?" . http_build_query($params);
		$result_json = file_get_contents($url);

		$result = json_decode($result_json, true);

		if (isset($result["errcode"])) {
			$r = Helper::rt($result["errmsg"], 0, $result);
		} else if (isset($result["openid"])) {
			$r = Helper::rt("success", 1, $result);
		} else {
			$r = Helper::rt("unknown error", 0);
		}

		return $r;
	}
}

 ?>