<?php

/**
 * 栏目验证规则
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-03 17:08:00
 */

namespace app\admini\validate;

use think\Validate;

class Category extends Validate{

	protected $rule =   [
        "name"  => "require|unique:category",
        "description"  => "require",
    ];

    protected $message  =   [
        "name.require" => "栏目名称必须",
        "name.unique" => "栏目名称已存在",
        "description.require" => "栏目描述必须"
    ];

    protected $scene = [
        "add" => ["name", "description"],
        // "edit" => ["name" => "require|unique:category,name^id", "description"]
    	"edit" => ["name" => "require", "description"]
    ];
}