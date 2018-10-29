<?php

/**
 * 比赛记录模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-28 15:33:06
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-31 10:57:30
 */

namespace app\common\model\nba;

class GameLog extends BaseModel{
	
	public function playerInfo(){
        return $this->hasOne("Player", "id", "player_id");
    }

    public function teamInfo(){
        return $this->hasOne("Team", "id", "team_id");
    }

    public function seasonInfo(){
        return $this->hasOne("Season", "id", "season_id");
    }
}
