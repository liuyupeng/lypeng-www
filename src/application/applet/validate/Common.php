<?php

/**
 * 验证规则
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:49:55
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 17:44:28
 */

namespace app\applet\validate;

use think\Validate;

class Common extends Validate{

	protected $rule = [
        "name" => "require",
        "title"  => "require",
        "context"  => "require",
        "theme_id"  => "require",
        "date"  => "require",
    ];

    protected $message  =   [
        "title.require" => "标题必须",
        "name.require" => "名称必须",
        "context.require" => "请先输入内容",
        "theme_id.require" => "请先选择主题",
        "date.require" => "请先选择日期"
    ];

    protected $scene = [
        "timeline_item" => ["context", "theme_id", "date"],
        "tickler_save" => ["context"],
    ];
}