<?php

/**
 * 标签模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 11:29:19
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Tag extends CommonModel{

	public static $tableName = "标签";

	protected $createTime = "date";
	protected $updateTime  = "dateline";
	protected $autoWriteTimestamp = "datetime";
}