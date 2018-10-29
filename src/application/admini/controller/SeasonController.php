<?php

/**
 * 赛季控制器
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-30 16:26:52
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-30 16:32:34
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\common\model\nba\Season;
use app\common\model\nba\GameLog;

class SeasonController extends CommonController{

    use \app\admini\traits\AuthTrait;

    // 检索
    public function search(){
    	if ($this->request->isAjax() == true) {
    		$list = Season::where([
                "disabled" => 0
            ])->order("end_date desc")->select();

    		if ($list) {
    			$list = collection($list)->toArray();
    		}

    		return rt("success", 1, $list);
    	}

    	return $this->fetch();
    }

    // 编辑
    public function save(){
        if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $name = $this->request->param("name");
            $begin_date = $this->request->param("begin_date");
            $end_date = $this->request->param("end_date");

            $season = Season::getDefault($id);
            $season->name = $name;
            $season->begin_date = $begin_date;
            $season->end_date = $end_date;
            $season->dateline = zdate_date();

            $r = $this->validateData($season, "nba.season_save");

            if ($r["res"] == 1) {
                $r = $season->saveData();
            }

            return $r;
        }
    }

    // 删除
    public function remove(){
    	if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $r = Season::getOne($id);
            if ($r["res"] == 1) {
                $season = $r["data"];

                $logCount = GameLog::where([
                    "disabled" => 0,
                    "team_id" => $id
                ])->count("id");

                if ($logCount > 0) {
                    $r = rt("赛季已有比赛记录不能删除", 0);
                }
            }

            if ($r["res"] == 1) {
                $season->disabled = 1;
                $season->dateline = zdate_date();
                $r = $season->saveData();
            }

            return $r;
        }
    }

}