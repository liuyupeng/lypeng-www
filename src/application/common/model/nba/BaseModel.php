<?php

/**
 * 公共模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-28 15:44:42
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-30 15:54:34
 */

namespace app\common\model\nba;

use think\Config;
use app\common\model\CommonModel;

class BaseModel extends CommonModel{

	protected $createTime = "date";
	protected $updateTime  = "dateline";
	protected $autoWriteTimestamp = "datetime";

	// protected $connection = "database2";
	protected $connection = [
		"prefix" => "nba_"
	];
}
