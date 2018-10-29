<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-04-04 11:32:43
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 14:19:27
 */

namespace app\applet\controller;

use Helper;
use think\Db;
use think\Config;
use think\Loader;
use think\Request;

use app\applet\model\Account;

use app\common\logic\WxLogic;

class IndexController extends CommonController{

	use \app\applet\traits\AuthTrait;


	/**
	 * 登录
	 * @param  $wx_code 	[必填]小程序登录接口获取的code
	 */
	public function wxlogin(){
        $r = rt("登录失败", 0);
		$wx_code = $this->request->param("wx_code");
		$result = WxLogic::getInstance()->getSnsInfo($wx_code);

		if ($result["res"] == 1) {
            $wx_openid = $result["data"]["openid"];
            $session_key = $result["data"]["session_key"];

            $account = Account::saveInfo($wx_openid, $session_key);
            if ( !empty($account) ) {
            	$r = rt("登录成功", 1, $account);
            }
        }

        return $r;
	}

	/**
	 * 用户信息上传
	 * @param  $wx_rawdata [必填]小程序获取用户信息接口获取的数据包
	 */
	public function uploadUserInfo(){
		$wx_rawdata = $_POST["wx_rawdata"];
		$rawdata = json_decode($wx_rawdata, true);

		$r = Helper::rt("参数错误", 0);
		if ( $rawdata && is_array($rawdata) ) {
			$identity = $this->identity;

	        $identity->wx_nickname = isset($rawdata["nickName"]) ? $rawdata["nickName"] : "";
	        $identity->wx_avatarurl = isset($rawdata["avatarUrl"]) ? $rawdata["avatarUrl"] : "";
	        $identity->wx_gender = isset($rawdata["gender"]) ? $rawdata["gender"] : 0;
	        $identity->wx_country = isset($rawdata["country"]) ? $rawdata["country"] : "";
	        $identity->wx_province = isset($rawdata["province"]) ? $rawdata["province"] : "";
	        $identity->wx_city = isset($rawdata["city"]) ? $rawdata["city"] : "";
	        $identity->dateline = date("Y-m-d H:i:s");
	        $identity->uuid = Helper::zstrGuid();

	        if ($identity->save() == true) {
	            $r = Helper::rt("用户信息保存成功", 1, $identity->toArray());
	        } else {
	        	$r = Helper::rt("用户信息保存失败", 0);
	        }
		}

		return $r;
	}


}