<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-14 11:20:15
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 13:30:18
 */

namespace app\admini\validate;

use think\Validate;

class Common extends Validate{

	protected $rule = [
        "name" => "require",
        "title"  => "require",
        "cate_id"  => "require",
        "content"  => "require",
        "description"  => "require",

        "account" => "require|unique:admin",
        "username" => "require",
        "role_id" => "require",
    ];

    protected $message  =   [
        "title.require" => "标题必须",
        "cate_id.require" => "栏目必须",
        "content.require" => "内容必须",
        "description.require" => "描述必须",

        "account.require" => "账号不能为空",
        "account.unique" => "账号已存在",
        "username.require" => "用户名必须不能为空",
        "role_id.require" => "用户角色不能为空",
    ];

    protected $scene = [
        "base_name" => ["name"],
    	"admin_add" => ["account", "username", "role_id"],
        "admin_edit" => ["account" => "require|unique:admin,account^id", "username", "role_id"],
    ];
}