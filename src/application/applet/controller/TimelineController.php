<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:33:50
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 17:36:14
 */

namespace app\applet\controller;

use think\Db;
use think\Config;
use think\Loader;
use think\Request;

use app\applet\model\Theme;
use app\applet\model\Account;
use app\applet\model\Timeline;
use app\applet\model\TimelineItem;

use app\applet\logic\TimelineLogic;

class TimelineController extends CommonController{

	use \app\applet\traits\AuthTrait;

	public function getItemList(){
        $rows = $this->request->param("rows", 20);

        $pagination = TimelineItem::where([
        	"disabled" => 0,
        	"userid" => $this->identity->userid
        ])->with(["themeInfo" => function($query){
        	$query->field(["id", "name", "icon"]);
        }])->order("date desc")->paginate($rows, false, ["primary_key" => "id"])->toArray();

        foreach ($pagination["data"] as $key => $info) {
        	$pagination["data"][$key]["title_formatter"] = $info["title"] ? : $info["theme_info"]["name"];
            $pagination["data"][$key]["date_formatter"] = date("Y年m月d", strtotime($info["date"]));
        }

        return rt("时光轴获取成功", 1, $pagination);
	}


	/**
	 * 获取详情
	 * @return [type] [description]
	 */
	public function getItemEdit(){
		$info = [];
		if ($id = $this->request->param("id")) {
			$info = TimelineItem::get($id);
		}

		$themeList = Theme::getList();
		array_unshift($themeList, ["id" => 0,"name" => "--"]);

		return rt("获取成功", 1, [
			"info" => $info,
			"themeList" => $themeList
		]);
	}

	/**
	 * 编辑时光轴信息
	 */
	public function saveItem(){
		$userid = $this->identity->userid;
		$id = $this->request->param("id");
		$timelineItem = TimelineItem::get($id);

		$r = rt("时光轴获取成功", 1);
		if (empty($timelineItem)) {
			$timeline_id = TimelineLogic::defaultId($userid);
			$timelineItem = new TimelineItem();
			$timelineItem->userid = $userid;
			$timelineItem->timeline_id = $timeline_id;
		} else {
			if ($timelineItem->userid != $userid) {
				$r = rt("您无权进行此操作", 0);
			}
		}

		if ($r["res"] == 1) {
			$timelineItem->date = $this->request->param("date");
			$timelineItem->title = $this->request->param("title");
			$timelineItem->context = $this->request->param("context");
			$timelineItem->theme_id = $this->request->param("theme_id");
			$timelineItem->dateline = zdate_date();

			$r = $this->validateData($timelineItem, "common.timeline_item");
		}

		if ($r["res"] == 1) {
			$r = $timelineItem->saveData();
		}

		return $r;
	}

	/**
	 * 删除时光轴
	 */
	public function deleteItem(){
		$userid = $this->identity->userid;
		$id = $this->request->param("id");
		$r = TimelineItem::getOne($id);

		if ($r["res"] == 1) {
			$timelineItem = $r["data"];

			if ($timelineItem->userid != $userid) {
				$r = rt("您无权进行此操作", 0);
			} else if ($timelineItem->disabled == 1) {
				$r = rt("该时光轴已删除", 0);
			}
		}

		if ($r["res"] == 1) {
			$timelineItem->disabled = 1;
			$timelineItem->dateline = zdate_date();
			$r = $timelineItem->saveData();
		}

		return $r;
	}
}