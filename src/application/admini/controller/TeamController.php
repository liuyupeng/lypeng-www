<?php

/**
 * 球队
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-30 16:01:07
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-30 16:19:00
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\common\model\nba\Team;
use app\common\model\nba\GameLog;

class TeamController extends CommonController{

    use \app\admini\traits\AuthTrait;

    // 检索
    public function search(){
    	if ($this->request->isAjax() == true) {
    		$list = Team::where([
                "disabled" => 0
            ])->select();

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
            $name_eng = $this->request->param("name_eng");
            $city = $this->request->param("city");
            $location = $this->request->param("location");

            $team = Team::getDefault($id);
            $team->name = $name;
            $team->name_eng = $name_eng;
            $team->city = $city;
            $team->location = $location;
            $team->dateline = zdate_date();

            $r = $this->validateData($team, "nba.team_save");

            if ($r["res"] == 1) {
                $r = $team->saveData();
            }

            return $r;
        }
    }

    // 删除
    public function remove(){
    	if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $r = Team::getOne($id);
            if ($r["res"] == 1) {
                $team = $r["data"];

                $logCount = GameLog::where([
                    "disabled" => 0,
                    "team_id" => $id
                ])->count("id");

                if ($logCount > 0) {
                    $r = rt("球队已有比赛记录不能删除", 0);
                }
            }

            if ($r["res"] == 1) {
                $team->disabled = 1;
                $team->dateline = zdate_date();
                $r = $team->saveData();
            }

            return $r;
        }
    }

}