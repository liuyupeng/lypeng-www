<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-31 14:17:34
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-19 15:38:41
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\common\model\nba\GameLog;
use app\common\logic\ExcelLogic;

class DataController extends CommonController{

    use \app\admini\traits\AuthTrait;

    public function _init(){
    	// exit("access denied.");
    }

    public function log(){
        $log_path = $this->request->param("path");

        $filepath = "../runtime" . DS . 'log' . DS . $log_path;
        if (!file_exists($filepath)){
            return $this->_error("404", "日志不存在");
        }

        $context = file_get_contents($filepath);

        $delimiter = "---------------------------------------------------------------";
        $logs = explode($delimiter, $context);

        $logs = array_reverse($logs);
        $context = implode($delimiter, $logs);

        $this->assign("context", $context);
        return $this->fetch("index:log");
    }

    public function gameLog(){
    	$r = ExcelLogic::excelToArray("./attachment/temp/lebron.xlsx");
    	$list = $r["data"];

    	$success_number = 0;
    	foreach ($list as $key => $value) {
    		if($key == 1 || $key > 15) continue;

    		$gameLog = new GameLog();
        	$gameLog->player_id = 1;
    		$gameLog->season_id = $value["A"];
        	$gameLog->team_id = $value["B"];
        	$gameLog->come_num = $value["C"]; 
        	$gameLog->come_first = $value["D"]; 
        	$gameLog->minute_num = $value["E"]; 
        	$gameLog->board_num = $value["K"]; 
        	$gameLog->board_attack = $value["I"]; 
        	$gameLog->board_defense = $value["J"]; 
        	$gameLog->shoot_rate = $value["F"]; 
        	$gameLog->three_rate = $value["G"]; 
        	$gameLog->free_rate = $value["H"]; 
        	$gameLog->assists = $value["L"]; 
        	$gameLog->steals = $value["M"]; 
        	$gameLog->block = $value["N"]; 
        	$gameLog->anerror = $value["O"]; 
        	$gameLog->foul = $value["P"]; 
        	$gameLog->score = $value["Q"];

        	if ($gameLog->save() == true) {
        		$success_number++;
        	}
    	}

    	dump($success_number);
    }
}