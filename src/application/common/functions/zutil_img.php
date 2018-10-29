<?php

/**
 * 图片类扩展方法
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 16:35:37
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-04 12:13:22
 */


// 文件名称
function zimg_name(){
	return md5(microtime(true));
}

function zimg_info($source_path) {
	$source_info = getimagesize($source_path);

	return array(
		"width" => isset($source_info[0]) ? $source_info[0] : "",
		"height" => isset($source_info[1]) ? $source_info[1] : "",
		"mime" => isset($source_info["mime"]) ? $source_info["mime"] : "",
		"size" => filesize($source_path)
	);
}

// 不超过指定宽高的缩放
function zimg_zoom_max($source_path, $maxWidth, $maxHeight, $save_path = "") {
	$save_path = $save_path ? $save_path : $source_path;

	$imgInfo = zimg_info($source_path);
	if ($imgInfo["width"] <= $maxWidth && $imgInfo["height"] <= $maxHeight) {
		return true;
	}
	
	$target_width = $imgInfo["width"];
	$target_height = $imgInfo["height"];
	$source_ratio = $imgInfo["width"] / $imgInfo["height"]; // 原图比例

	if ($target_width > $maxWidth) {
		$target_width  = $maxWidth;
		$target_height = $maxWidth / $source_ratio;
	}

	if ($maxHeight > 0 && $target_height > $maxHeight) {
		$target_height = $maxHeight;
		$target_width  = $maxHeight * $source_ratio;
	}

	switch ($imgInfo["mime"]) {
		case "image/gif":
			$source_image = imagecreatefromgif($source_path);
			break;
		case "image/jpeg":
			$source_image = imagecreatefromjpeg($source_path);
			break;
		case "image/png":
			$source_image = imagecreatefrompng($source_path);
			break;
		default:
			return false;
		break;
	}

	$target_image  = imagecreatetruecolor($target_width, $target_height);
	$cropped_image = imagecreatetruecolor($imgInfo["width"], $imgInfo["height"]);

	// 裁剪
	imagecopy($cropped_image, $source_image, 0, 0, 0, 0, $imgInfo["width"], $imgInfo["height"]);
	// 缩放
	imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $imgInfo["width"], $imgInfo["height"]);

	//header('Content-Type: image/jpeg');
	imagejpeg($target_image, $save_path, 100);
	imagedestroy($source_image);
	imagedestroy($target_image);
	imagedestroy($cropped_image);

	return true;
}


// 生成二维码
function zimg_qrcode($code, $savepath = false, $level = "L", $size = 8, $margin = 2){
	Loader::import("phpqrcode.phpqrcode", EXTEND_PATH);
	
	ob_clean();

	$QRcode = new \QRcode();
	$QRcode->png($code, $savepath, $level, $size, $margin);
	exit();
}



function zimg_upload($field = 'file'){
	if ($file = request()->file($field)) {
		$allow_exts = 'jpg,png,jpeg,gif';
		$save_path = 'attachment' . DS . 'temp';
		$abs_path = ROOT_PATH . DS . 'public' . DS . $save_path;
        $info = $file->rule("zimg_name")->validate(['ext' => $allow_exts])->move($abs_path);
        if ($info) {
        	$imgInfo = $info->getInfo();

        	$save_name = $info->getSaveName();
        	$file_path = $save_path . DS . $save_name;

        	$r = rt("图片上传成功", 1, [
        		'path' => $file_path,
        		'name' => $imgInfo['name']
        	]);
        } else {
            $r = rt($file->getError(), 0);
        }
    } else {
        $r = rt("请先选择图片", 0);
    }

    return $r;
}