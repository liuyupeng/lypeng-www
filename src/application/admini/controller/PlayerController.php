<?php

/**
 * 球员控制器
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-28 16:14:13
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-30 16:05:21
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\common\model\nba\Player;
use app\common\model\nba\GameLog;

class PlayerController extends CommonController{

    use \app\admini\traits\AuthTrait;

    // 检索
    public function search(){
    	if ($this->request->isAjax() == true) {
    		$list = Player::where([
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
            $league_in = $this->request->param("league_in");

            $player = Player::getDefault($id);
            $player->name = $name;
            $player->name_eng = $name_eng;
            $player->league_in = $league_in;
            $player->dateline = zdate_date();

            $r = $this->validateData($player, "nba.player_save");

            if ($r["res"] == 1) {
                $r = $player->saveData();
            }

            return $r;
        }
    }

    // 删除
    public function remove(){
    	if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $r = Player::getOne($id);
            if ($r["res"] == 1) {
                $player = $r["data"];

                $logCount = GameLog::where([
                    "disabled" => 0,
                    "player_id" => $id
                ])->count("id");

                if ($logCount > 0) {
                    $r = rt("球员已有比赛记录不能删除", 0);
                }
            }

            if ($r["res"] == 1) {
                $player->disabled = 1;
                $player->dateline = zdate_date();
                $r = $player->saveData();
            }

            return $r;
        }
    }

}