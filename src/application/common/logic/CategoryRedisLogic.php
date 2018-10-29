<?php

/**
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2018-10-27 22:37:44
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-27 23:01:31
 */

namespace app\common\logic;

class CategoryRedisLogic extends RedisLogic {

	private static $prefix = "__CATEGORY__";

	public static function checkExists($key){
		$keys = static::getConnect()->getKeys(static::$prefix . $key . "*");
		return !empty($keys);
	}


	// 保存数据，二维列表
	public static function saveListData($key, $data){
		foreach ($data as $cate) {
            static::getConnect()->hmset(static::$prefix . $key . $cate["id"], $cate);
        }
	}

	// 取出数据
	public static function getListData($key){
		$keys = static::getConnect()->getKeys(static::$prefix . $key . "*");

		$list = [];
        foreach ($keys as $redis_key) {
            $list[] = static::getConnect()->hgetall($redis_key);
        }

        return $list;
	}

	// 删除数据
	public static function delListData($key){
		$keys = static::getConnect()->getKeys(static::$prefix . $key . "*");
		return static::getConnect()->del($keys);
	}

	// 删除所有
	public static function delAll(){
		$keys = static::getConnect()->getKeys(static::$prefix . "*");
		return static::getConnect()->del($keys);
	}

}