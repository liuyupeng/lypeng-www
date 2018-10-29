<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-04-04 11:49:24
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 14:43:25
 */

namespace app\common\model;

use think\Model;

class CommonModel extends Model{

	protected $field = true;
	public static $tableName = "";

	public static function getDefault($data){
		$obj = static::get($data);
		return $obj ? : new static();
	}

	public static function getOne($data, $asArray = false){
		$r = rt(static::$tableName . "：未找到", 0);
		$obj = static::get($data);
		if ( !empty($obj) ) {
			if ($asArray == true) $obj = $obj->toArray();

			$r = rt(static::$tableName . "：查找成功", 1, $obj);
		}

		return $r;
	}


	public function saveData($data = [], $where = [], $sequence = null){
		$r = rt(static::$tableName . "：数据保存失败", 0);
		$result = $this->save($data, $where, $sequence);
		if ($result == true) {
			$r = rt(static::$tableName . "：数据保存成功", 1);
		}

		return $r;
	}

	public static function listToArray($list){
		if (!empty($list) && is_array($list)) {
			$list = collection($list)->toArray();
		}

		return $list;
	}

}