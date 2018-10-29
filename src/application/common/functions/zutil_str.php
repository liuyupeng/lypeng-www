<?php

/**
 * 字符串类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:34:17
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-04 11:32:34
 */

// 生成uuid
function zstr_guid(){
    if (function_exists('com_create_guid')){
        $guid = trim(com_create_guid(), '{}');
        return str_replace("-", "", $guid);
    }else{
        mt_srand((double)microtime()*10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        //chr(123)// "{"
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
                //.chr(125);// "}"
        return str_replace("-", "", $uuid);
    }
}


// 生成随机字符串
function zstr_randstr($len, $type = ""){
    $int = "123456789";
    $num = "0123456789";
    $lower = "abcdefghijklmnopqrstuvwxyz";
    $upper = strtoupper($lower);
    $str = $lower.$upper;
    $code = "abcdefghkmnprstuvwyABCDEFGHJKMNPRSTUVWY23456789";
    $hash = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".$num;
    $types = array("int","str","lower","upper","code","num","hash");
    $string = in_array($type,$types) ? $$type : ($num.$str);
    $return = "";
    for($i = 0; $i < $len; $i++){
        if($i > 0 && $type == "int") $string = $num;
        $return .= substr($string,rand(0,strlen($string)-1),1);
    }

    return (string)$return;
}

// 检测字符串是否存在于数组中
function zstr_in_array($str, $arr, $case = false, $type = false){
	if ($case == false) {
		$str = strtolower($str);
		foreach ($arr as $k => $v) {
			$arr[$k] = strtolower($v);
		}
	}

	return in_array($str, $arr, $type);
}



// 首字母    文字、分隔符、长度
function zstr_word_first($cn, $delimiter = ""){
	return \Org\ZUtf8_PY::encode($cn, $delimiter);
}

//全拼    文字、分隔符、长度
function zstr_word_full($cn, $delimiter = " ") {
	return \Org\ZUtf8_PY::encode($cn, $delimiter, "all");
}

//全拼    文字、分隔符、长度
function zstr_word_full_case($cn) {
	$word_full = \Org\ZUtf8_PY::encode($cn, "_", "all");

	$word_full_case = "";
	$wordFull = explode("_", $word_full);
	foreach ($wordFull as $key => $value) {
		$word_full_case .= ucfirst($value);
	}

	return $word_full_case;
}