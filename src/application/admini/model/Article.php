<?php

/**
 * 文章模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 11:29:05
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Article extends CommonModel{

	public static $tableName = "文章";

	const WEIGHT_SORT 	= 0.4;
	const WEIGHT_DATE 	= 0.3;
	const WEIGHT_ZAN 	= 0.2;
	const WEIGHT_READ 	= 0.1;

	protected $readonly = [];

	public function cateInfo(){
        return $this->hasOne("Category", "id", "cate_id");
    }

}