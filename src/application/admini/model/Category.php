<?php

/**
 * 栏目模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 11:29:11
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Category extends CommonModel{

	public static $tableName = "栏目";

	protected $readonly = ["parent_id"];

	public function parentInfo(){
        return $this->hasOne("Category", "id", "parent_id");
    }
}