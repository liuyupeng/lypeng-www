<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 17:31:27
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-27 09:46:35
 */

namespace app\applet\controller;

use think\Db;
use think\Config;
use think\Loader;
use think\Request;

use app\applet\model\Theme;
use app\applet\model\Tickler;
use app\applet\model\Account;

class TicklerController extends CommonController{

	use \app\applet\traits\AuthTrait;

	public function getList(){
        $rows = $this->request->param("rows", 20);

        $pagination = Tickler::where([
        	"disabled" => 0,
        	"userid" => $this->identity->userid
        ])->with(["themeInfo" => function($query){
        	$query->field(["id", "name", "icon"]);
        }])->order("dateline desc")->paginate($rows, false, ["primary_key" => "id"])->toArray();

        foreach ($pagination["data"] as $key => $info) {
            $pagination["data"][$key]["date_formatter"] = date("Y-m-d H:i", strtotime($info["dateline"]));

            if ($info["title"] == "" && $info["theme_info"]) {
        		$pagination["data"][$key]["title_formatter"] = $info["theme_info"]["name"];
            } else {
            	$pagination["data"][$key]["title_formatter"] = $title = $info["title"];
            }
        }

        return rt("备忘录获取成功", 1, $pagination);
	}

	/**
	 * 获取详情
	 * @return [type] [description]
	 */
	public function getEdit(){
		$info = [];
		if ($id = $this->request->param("id")) {
			$info = Tickler::get($id);
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
	public function save(){
		$userid = $this->identity->userid;
		$id = $this->request->param("id");
		$tickler = Tickler::get($id);

		$r = rt("备忘录获取成功", 1);
		if (empty($tickler)) {
			$tickler = new Tickler();
			$tickler->userid = $userid;
		} else {
			if ($tickler->userid != $userid) {
				$r = rt("您无权进行此操作", 0);
			}
		}

		if ($r["res"] == 1) {
			$tickler->title = $this->request->param("title");
			$tickler->context = $this->request->param("context");
			$tickler->theme_id = $this->request->param("theme_id");
			$tickler->dateline = zdate_date();

			$r = $this->validateData($tickler, "common.tickler_save");
		}

		if ($r["res"] == 1) {
			$r = $tickler->saveData();
		}

		return $r;
	}


	/**
	 * 删除
	 */
	public function deleteInfo(){
		$userid = $this->identity->userid;
		$id = $this->request->param("id");
		$r = Tickler::getOne($id);

		if ($r["res"] == 1) {
			$tickler = $r["data"];

			if ($tickler->userid != $userid) {
				$r = rt("您无权进行此操作", 0);
			} else if ($tickler->disabled == 1) {
				$r = rt("该备忘录已删除", 0);
			}
		}

		if ($r["res"] == 1) {
			$tickler->disabled = 1;
			$tickler->dateline = zdate_date();
			$r = $tickler->saveData();
		}

		return $r;
	}
}