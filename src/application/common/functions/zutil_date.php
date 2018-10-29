<?php

/**
 * 日期类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:35:22
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-24 16:43:09
 */



// 获取日期
function zdate_date($format = "Y-m-d H:i:s", $time = ""){
	if ( empty($time) ) $time = time();
	return date($format, $time);
}

// 获取时间
function zdate_time($date = ""){
	if ( !empty($date) ) {
		return strtotime($date);
	}

	return time();
}