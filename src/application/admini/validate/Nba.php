<?php

/**
 * 验证规则
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-30 16:03:58
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-31 13:38:01
 */

namespace app\admini\validate;

use think\Validate;

class Nba extends Validate{

	protected $rule =   [
        "name"  => "require",
        "name_eng"  => "require",
        "begin_date" => "require",
        "end_date" => "require",
        "season_id" => "require",
        "team_id" => "require",
        "player_id" => "require",
    ];

    protected $message  =   [
        "name.require" => "名称必须",
        "name_eng.require" => "英文名称必须",
        "begin_date.require" => "开始日期必须",
        "end_date.require" => "结束日期必须",
        "player_id.require" => "请先选择球员",
        "team_id.require" => "请先选择球队",
        "season_id.require" => "请先选择赛季"
    ];

    protected $scene = [
    	"player_save" => ["name", "name_eng"],
    	"team_save" => ["name"],
        "season_save" => ["name", "begin_date", "end_date"],
        "game_log_save" => ["season_id", "team_id", "player_id"]
    ];
}