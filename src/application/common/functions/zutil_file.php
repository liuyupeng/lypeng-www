<?php

/**
 * 文件类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:35:53
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-24 16:42:52
 */


// 下载文件（保留文件原有文件名）
function zfile_download($file_path, $savename = ""){
	$fileinfo = pathinfo($file_path);
    header('Content-type: application/x-'.$fileinfo['extension']);
    header('Content-Disposition: attachment; filename='. ($savename ? $savename : $fileinfo['basename']));
    header('Content-Length: '.filesize($file_path));
    readfile($file_path);
}

// 内容下载
function zfile_data_download($body, $savename){
    ob_clean();
	$fileinfo = pathinfo($savename);
    header('Content-type: application/x-'.$fileinfo['extension']);
    header('Content-Disposition: attachment; filename='. $fileinfo['basename']);
    header('Content-Length: '.strlen($body));
    // echo $body;
    exit($body);
}