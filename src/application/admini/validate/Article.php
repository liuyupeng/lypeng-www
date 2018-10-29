<?php

/**
 * 文章验证规则
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-04 13:51:23
 */

namespace app\admini\validate;

use think\Validate;

class Article extends Validate{

	protected $rule =   [
        "title"  => "require",
        "cate_id"  => "require",
        "content"  => "require",
        "description"  => "require"
    ];

    protected $message  =   [
        "title.require" => "标题必须",
        "cate_id.require" => "栏目必须",
        "content.require" => "内容必须",
        "description.require" => "描述必须"
    ];

    protected $scene = [
        "save" => ["title", "cate_id", "content", "description"]
    ];
}