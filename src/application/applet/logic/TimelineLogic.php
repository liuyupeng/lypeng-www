<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 16:05:10
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 16:07:32
 */


namespace app\applet\logic;

use think\Config;
use think\Loader;

use app\applet\model\Theme;
use app\applet\model\Account;
use app\applet\model\Timeline;
use app\applet\model\TimelineItem;

class TimelineLogic {

	public static function defaultId($userid){
		$timeline = Timeline::get(["userid" => $userid]);

		if (empty($timeline)) {
			$timeline = new Timeline();
			$timeline->userid = $userid;
			$timeline->name = "默认时光轴";
			$timeline->description = "默认时光轴";

			$result = $timeline->save();
			if ($result == false) {
				$timeline->id = 0;
			}
		}

		return $timeline->id;
	}
}