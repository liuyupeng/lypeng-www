<?php 

/**
 * 公共逻辑
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2018-04-01 19:20:29
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-04-04 12:54:05
 */

namespace app\common\logic;

class CommonLogic {
	
	public function __construct(){

	}


	// curl
	protected function doCurlRequest($url = "", $fields = array(), $post = true){
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //超时时间(单位:秒)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且不输出到屏幕上
        if(false !== $post){
            curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
            if(is_array($fields) && !empty($fields)) {
                $_fields = http_build_query($fields, "", "&");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $_fields);
            }
        }
        
        $result = curl_exec($ch); //运行curl
        curl_close($ch);
        return $result;
    }
}

 ?>