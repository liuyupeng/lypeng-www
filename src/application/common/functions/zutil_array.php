<?php

/**
 * 数组类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:41:41
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-24 16:43:04
 */


// 获取二维数组中某一项的集合
function zarray_column($data, $field){
	$egt_5_5 = version_compare(PHP_VERSION,'5.5.0','>=');
	if ($egt_5_5) {
		$fields = array_column($data, $field);
	} else {
		$fields = array_reduce($data, create_function('$v,$w', '$v[]=$w["'.$field.'"];return $v;'));
	}

	return $fields ? $fields : array();
}

// 求二维数组中某一项的加和
function zarray_sum($data, $field){
	$data = zarray_column($data, $field);
	return array_sum($data);
}

// 按二维数组中某一项对二维数组进行排序
function zarray_sort($data, $field, $sort = "asc"){
	$sort = $sort == "desc" ? SORT_DESC : SORT_ASC;

	$sortData = zarray_column($data, $field);
	array_multisort($sortData, $sort, $data);

	return $data;
}