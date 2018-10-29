<?php

/**
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2018-10-27 20:53:57
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-27 23:27:53
 */

namespace app\common\logic;

class RedisLogic {

	protected static $_connect;

	protected static $prefix = "__PREFIX__";

	public function __construct($prefix = null){
		if ( $prefix && is_string($prefix) ) {
			static::$prefix = $prefix;
		}
	}

	// 获取连接
	public static function getConnect(){
		if ( empty(static::$_connect) ) {
			static::$_connect = new \Redis();
			static::$_connect->connect('127.0.0.1', 6379);
		}

		return static::$_connect;
	}

	// 设置前缀
	public function setPrefix($prefix){
		static::$prefix = $prefix;
	}

	public function checkListData($key){
		$keys = static::getConnect()->getKeys(static::$prefix . $key . "*");
		return !empty($keys);
	}


	// 保存数据，二维列表
	public function saveListData($key, $data){
		foreach ($data as $cate) {
            static::getConnect()->hmset(static::$prefix . $key . $cate["id"], $cate);
        }
	}

	// 取出数据
	public function getListData($key){
		$keys = static::getConnect()->getKeys(static::$prefix . $key . "*");

		$list = [];
        foreach ($keys as $redis_key) {
            $list[] = static::getConnect()->hgetall($redis_key);
        }

        return $list;
	}


}