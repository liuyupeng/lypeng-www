<?php

/**
 * 主题模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:34:42
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 15:24:03
 */

namespace app\applet\model;

class Theme extends BaseModel{

	public static $listFields = ["id", "name", "icon"];
	public static $selectFields = ["id", "name"];

	public static function getList(){
		$list = static::all(function($query){
			$query->where(["disabled" => 0]);
			$query->order(["sort" => "asc"]);
			$query->field(static::$selectFields);
		});

		return static::listToArray($list);
	}
}