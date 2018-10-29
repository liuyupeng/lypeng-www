<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-31 10:05:29
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-31 14:31:30
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\common\model\nba\Team;
use app\common\model\nba\Player;
use app\common\model\nba\Season;
use app\common\model\nba\GameLog;

class GameLogController extends CommonController{

    use \app\admini\traits\AuthTrait;

    // 搜索
    public function search(){
    	if ($this->request->isAjax() == true) {
    		$list = GameLog::where([
                "disabled" => 0
            ])->with(["playerInfo", "teamInfo", "seasonInfo"])->select();

    		if ($list) {
    			$list = collection($list)->toArray();
    		}

    		return rt("success", 1, $list);
    	}

    	return $this->fetch();
    }

    // 编辑
    public function save(){
    	$id = $this->request->param("id");
        $gameLog = GameLog::getDefault($id);

        if ($this->request->isAjax() == true) {
        	$gameLog->season_id = $this->request->param("season_id");
        	$gameLog->player_id = $this->request->param("player_id");
        	$gameLog->team_id = $this->request->param("team_id");
        	$gameLog->come_num = $this->request->param("come_num");
        	$gameLog->come_first = $this->request->param("come_first");
        	$gameLog->minute_num = $this->request->param("minute_num");
        	$gameLog->board_num = $this->request->param("board_num");
        	$gameLog->board_attack = $this->request->param("board_attack");
        	$gameLog->board_defense = $this->request->param("board_defense");
        	$gameLog->shoot_rate = $this->request->param("shoot_rate");
        	$gameLog->three_rate = $this->request->param("three_rate");
        	$gameLog->free_rate = $this->request->param("free_rate");
        	$gameLog->assists = $this->request->param("assists");
        	$gameLog->steals = $this->request->param("steals");
        	$gameLog->block = $this->request->param("block");
        	$gameLog->anerror = $this->request->param("anerror");
        	$gameLog->foul = $this->request->param("foul");
        	$gameLog->score = $this->request->param("score");

        	$r = $this->validateData($gameLog, "nba.game_log_save");

        	if ($r["res"] == 1) {
        		$r = $gameLog->saveData();
        	}

        	return $r;
        }

    	$where = ["disabled" => 0];
    	$playerList = Player::where($where)->select();
    	$teamList = Team::where($where)->select();
    	$seasonList = Season::where($where)->order("end_date desc")->select();

    	$this->assign("info", $gameLog->toArray());
    	$this->assign("team_list", $teamList);
    	$this->assign("player_list", $playerList);
    	$this->assign("season_list", $seasonList);
    	return $this->fetch();
    }

    // 删除
    public function remove(){
    	if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $r = GameLog::getOne($id);
            if ($r["res"] == 1) {
                $gameLog = $r["data"];

                $gameLog->disabled = 1;
                $gameLog->dateline = zdate_date();
                $r = $gameLog->saveData();
            }

            return $r;
        }
    }
}