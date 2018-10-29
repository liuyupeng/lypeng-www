<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// ini_set('display_errors', 1);
// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function rt($msg = "SUCCESS", $res = 1, $data = null){
	return ["res" => $res, "msg" => $msg, "data" => $data];
}

include_once("common/functions/zutil_str.php");
include_once("common/functions/zutil_img.php");
include_once("common/functions/zutil_date.php");
include_once("common/functions/zutil_file.php");
include_once("common/functions/zutil_array.php");
include_once("common/functions/zutil_device.php");