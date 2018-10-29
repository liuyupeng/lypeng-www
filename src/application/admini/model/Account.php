<?php

/**
 * 用户模型
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 11:29:26
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Account extends CommonModel{

    public static $tableName = "";

	protected $readonly = ["mobile", "name"];

	protected $rule =   [
        "name"  => "require|max:25",
        "age"   => "number|between:1,120",
        "email" => "email",    
    ];

    protected $message  =   [
        "name.require" => "名称必须",
        "name.max"     => "名称最多不能超过25个字符",
        "age.number"   => "年龄必须是数字",
        "age.between"  => "年龄只能在1-120之间",
        "email"        => "邮箱格式错误",    
    ];
}