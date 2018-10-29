<?php
namespace app\admini\traits;

trait MenuTrait {

	// 配置菜单
	protected static function get_menu_list(){
		return 	array(
			array("name" => "管理员", "icon" => "user", "children" => array(

				array("name" => "管理员", "icon" => "circle-o", "link" => url("admin/search"), "act" => "admin/search"),
				array("name" => "角色管理", "icon" => "circle-o", "link" => url("admin/role"), "act" => "admin/role"),
				// array("name" => "权限配置", "icon" => "circle-o", "link" => url("admin/privilege"), "act" => "admin/privilege")
			)),
			array("name" => "文章管理", "icon" => "file-word-o", "children" => array(
				array("name" => "文章列表", "icon" => "circle-o", "link" => url("article/articleList"), "act" => "article/articleList"),
				array("name" => "栏目列表", "icon" => "circle-o", "link" => url("article/cateList"), "act" => "article/cateList"),
				array("name" => "标签列表", "icon" => "circle-o", "link" => url("tag/tagList"), "act" => "tag/tagList")
			)),

			array("name" => "NBA管理", "icon" => "adjust", "children" => array(
				array("name" => "球员列表", "icon" => "circle-o", "link" => url("player/search"), "act" => "player/search"),
				array("name" => "球队列表", "icon" => "circle-o", "link" => url("team/search"), "act" => "team/search"),
				array("name" => "赛季列表", "icon" => "circle-o", "link" => url("season/search"), "act" => "season/search"),
				array("name" => "比赛记录", "icon" => "circle-o", "link" => url("gameLog/search"), "act" => "gameLog/search")
			))
		);
	}

	// 过滤菜单
	protected function get_menu_filter_list(){
		$menu_list = self::get_menu_list();
		
		if ($this->privileges != "ALL") {
			foreach ($menu_list as $key => $menu) {
				foreach ($menu["children"] as $k => $children) {
					$action = "Admini/" . $children["act"];

					if (zstr_in_array($action, $this->privilegeAll)) {
						if (!zstr_in_array($action, $this->privileges)) {
							unset($menu_list[$key]["children"][$k]);
						}
					}
				}

				if (count($menu_list[$key]["children"]) == 0) {
					unset($menu_list[$key]);
				}
			}
		}

		return array_values($menu_list);
	}
}