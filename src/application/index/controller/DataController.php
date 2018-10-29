<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-28 15:21:34
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-28 15:55:12
 */

namespace app\index\controller;

use think\Config;
use think\Controller;

use app\common\logic\ArticleLogic;

class DataController extends Controller{

	protected function _initialize(){
		Config::set("default_return_type", "json");
	}

	public function weight(){
        $r = rt("access denied", 0);
        if ($this->request->isAjax() == true) {
            if ($this->request->param("token") == "aebb47633215db4d") {
                $success_num = ArticleLogic::setWeight();
                $r = rt("权重更新成功", 1, ["number" => $success_num]);
            }
        }

        return $r;
    }
}