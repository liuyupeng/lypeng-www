<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-14 14:10:22
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 14:52:21
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Privilege extends CommonModel{

	public static $tableName = "权限";
    

    public static $groupList = [
    	"admini_admin" => "管理员",
    	"admini_article" => "文章管理"
    ];
}