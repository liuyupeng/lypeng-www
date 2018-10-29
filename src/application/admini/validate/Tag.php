<?php

/**
 * 标签验证规则
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:39:20
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-24 15:51:41
 */

namespace app\admini\validate;

use think\Validate;

class Tag extends Validate{

	protected $rule =   [
        "name"  => "require|unique:tag",
        "description"  => "require",
    ];

    protected $message  =   [
        "name.require" => "标签名称必须",
        "name.unique" => "标签名称已存在",
        "description.require" => "标签描述必须"
    ];

    protected $scene = [
        "add" => ["name", "description"],
    	"edit" => ["name" => "require|unique:tag,name^id", "description"]
    ];
}