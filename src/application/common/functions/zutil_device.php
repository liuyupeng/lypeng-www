<?php

/**
 * 设备类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:36:08
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-07 09:22:05
 */


// 是否是微信浏览器
function zdevice_is_weixin(){
	if ( strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false ) {
		return true;
	}
	return false;
}

// 获取设备类型
function zdevice_get() {
	$detect = new \Org\MobileDetect();

	if ($detect->isMobile()) {
		$detect_name = "mobile";
	} else if ($detect->isTablet()) {
		$detect_name = "tablet";
	} else {
		$detect_name = "computer";
	}

	return $detect_name;
}