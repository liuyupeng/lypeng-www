<?php

/**
 * 腾讯对象存储
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2018-04-01 19:20:29
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-18 13:58:02
 */

namespace app\common\logic;

use think\Config;
use think\Loader;

class CosLogic {

	private static $region;

	private static $appId;

	private static $secretId;

	private static $secretKey;

	private static $bucket;

	private static $bucketFull;

	private static $rootPath;

	private static $imageView;

	public function __construct($config = []){
		static::initConfig($config);
	}

	public static function initConfig($config = []){
		if ( empty($config) ) {
			$custom_config = Config::get("custom_config");
    		$config = $custom_config["cos_loven"];
		}

		static::$region = isset($config["region"]) ? $config["region"] : "";
		static::$appId = isset($config["appId"]) ? $config["appId"] : "";
		static::$secretId = isset($config["secretId"]) ? $config["secretId"] : "";
		static::$secretKey = isset($config["secretKey"]) ? $config["secretKey"] : "";
		static::$bucket = isset($config["bucket"]) ? $config["bucket"] : "";
		static::$rootPath = isset($config["rootPath"]) ? $config["rootPath"] : "";
		static::$imageView = isset($config["imageView"]) ? $config["imageView"] : [];
		static::$bucketFull = static::$bucket . "-" . static::$appId;
	}

	// 设置上传空间
	public function setBucket($bucket){
		static::$bucket = $bucket;
		static::$bucketFull = static::$bucket . "-" . static::$appId;
	}

	// 设置上传根目录
	public function setRootPath($rootPath){
		static::$rootPath = $rootPath;
	}

	// 获取万象优图地址
	public function getViewUrl($url, $type = "default"){
		if (array_key_exists($type, static::$imageView)) {
			return $url . static::$imageView[$type];
		}

		return $url;
	}

	// 单文件上传
	public static function uploadOne($filepath, $filename, $isImage = true){
		static::initConfig();
		return static::doUpload($filepath, $filename, $isImage);
	}

	// 单文件上传
	public function uploadFile($field = "file", $isImage = true){
		if ( isset($_FILES[$field]) ) {
			$result = static::doUpload($_FILES[$field]["tmp_name"], $_FILES[$field]["name"], $isImage);
		} else {
			$result = ["ret" => "0", "msg" => "请先选择文件"];
		}

		return $result;
	}

	// 多文件上传
	public function uploadFiles($field = "files", $isImage = true){
		$result = ["ret" => "0", "msg" => "请先选择文件"];

		if ( isset($_FILES[$field]) ) {
			$files = $_FILES[$field];

			if ( isset($files["tmp_name"]) && is_array($files["tmp_name"]) ) {

				$items = [];
				for ($i = 0; $i < count($files["tmp_name"]); $i++) {
					$r = static::doUpload($files["tmp_name"][$i], $files["name"][$i], $isImage);
					if ($r["ret"] == 1) $items[] = $r["data"];
				}

				$result = ["ret" => 1, "msg" => "文件上传成功", "data" => [
					"items" => $items,
					"total" => count($items)
				]];
			}
		}

		return $result;
	}

	// 获取文件内容
	public function getObject($Key){
		try {
	    	$result = static::getClient()->getObject(array(
			    "Bucket" => static::$bucketFull,
			    "Key" => $Key
			));

			$result = ["ret" => 1, "msg" => "文件内容获取成功", "data" => $result["Body"]];
    	} catch (\Exception $e) {
		    $result = ["ret" => 0, "msg" => $e];
		}

    	return $result;
    }

    // 删除文件
	public function deleteObject($Key){
		try {
	    	$result = static::getClient()->deleteObject(array(
			    "Bucket" => static::$bucketFull,
			    "Key" => $Key
			));

			$result = ["ret" => 1, "msg" => "文件删除成功"];
    	} catch (\Exception $e) {
		    $result = ["ret" => 0, "msg" => $e];
		}

    	return $result;
    }


	// 上传到腾讯云
	private static function doUpload($filepath, $filename, $isImage = true){
		try {
		    static::getClient()->upload(
	        	$bucket = static::$bucketFull,
	        	$key = static::getKey($filename),
	        	$body = file_get_contents($filepath),
	       		$options = array(
		            // "ACL"=>"private",
		            // "CacheControl" => "private"
		        )
		    );

		    $result = ["ret" => 1, "msg" => "文件上传成功", "data" => [
		    	"url" => static::getUrl($key, $isImage),
		    	"filepath" => $key,
		    	"file_name" => trim(strrchr($key, "/"), "/"),
		    	"file_oldname" => $filename
		    ]];
		} catch (\Exception $e) {
		    $result = ["ret" => 0, "msg" => $e];
		}

    	return $result;
    }

    
    
    // 获取上传路径
	private static function getKey($name){
		$uuid = static::getGuid();
		$_name = $uuid . "." . static::getExtension($name);

		return implode("/", [static::$rootPath, date("Ym"), $_name]);
	}

	// 获取文件地址
	private static function getUrl($key, $isImage = true){
		if ($isImage == true) {
    		return "http://" . static::$bucketFull . ".picsh.myqcloud.com/" . $key;
		} else {
			return $key;
		}
    }

    // 获取文件后缀名
	private static function getExtension($name){
    	return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    // 获取上传对象
    private static function getClient(){
    	Loader::import("cosSDK.cos-autoloader", EXTEND_PATH);

		$cosClient = new \Qcloud\Cos\Client(array(
			"region" => static::$region,
    		"credentials"=> array(
		        "appId" => static::$appId,
		        "secretId" => static::$secretId,
		        "secretKey" => static::$secretKey
		    )
    	));

		return $cosClient;
	}

	// 生成uuid
	private static function getGuid(){
	    if (function_exists('com_create_guid')){
	        $guid = trim(com_create_guid(), '{}');
	        $uuid = str_replace("-", "", $guid);
	    }else{
	        mt_srand((double)microtime()*10000);
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);

	        $uuid = substr($charid, 0, 8) . $hyphen
	                . substr($charid, 8, 4) . $hyphen
	                . substr($charid, 12, 4) . $hyphen
	                . substr($charid, 16, 4) . $hyphen
	                . substr($charid, 20, 12);
	    }

	    return strtolower(str_replace("-", "", $uuid));
	}
}