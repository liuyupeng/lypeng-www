<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-19 16:10:51
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-24 11:14:17
 */

namespace app\common\logic;

use Helper;
use think\Config;
use think\Loader;

use app\admini\model\Article;

class ArticleLogic extends CommonLogic{

	// 文章查询条件组合
	private static function getQueryModel($parameter = [], $with = []){
		if (is_string($with)) {
			$with = explode(",", $with);
			$with = array_filter($with);
		}

		// 构建查询
		$dataModel = Article::where(["disabled" => 0]);

		// 所属栏目筛选
		if (isset($parameter["cate_id"]) && $parameter["cate_id"]) {
			$dataModel->where("FIND_IN_SET(".$parameter['cate_id'].", categorys)");
		}

		// 文章标签筛选
		if (isset($parameter["tag_id"]) && $parameter["tag_id"]) {
			$dataModel->where("FIND_IN_SET(".$parameter['tag_id'].", tags)");
		}

		// 文章标签筛选
		if (isset($parameter["keywords"]) && $parameter["keywords"]) {
			$keywords = $parameter["keywords"];
			$dataModel->where(function($query) use ($keywords){
                $query->where([
                    "title" => ["like", "%" .$keywords . "%"]
                ])->whereOr([
                    "description" => ["like", "%" .$keywords . "%"]
                ]);
            });
		}

		// 文章显示状态
		if (!isset($parameter["is_show"])) {
			$dataModel->where(["is_show" => 1]);
		} else if ($parameter["is_show"] != "all") {
			$dataModel->where(["is_show" => $parameter["is_show"]]);
		}

		if (in_array("cateInfo", $with)) {
			$dataModel->with(["cateInfo" => function($query){
				$query->field(["id", "name"]);
			}]);
		}

		$order = ["weight" => "desc", "weight_hot" => "desc"];
		if (isset($parameter["order"]) && $parameter["order"]) {
			$order = $parameter["order"];
		}

		$dataModel->order($order)->field([
    		"id", "uuid", "cate_id", "title", "thumb",
    		"description", "author", "date", "dateline",
    		"read_num", "zan_num", "listorder", "is_show"
    	]);

        return $dataModel;
	}

	// 获取文章列表
	public static function getDataAll($parameter = [], $with = [], $limit = 0){
		$dataModel = static::getQueryModel($parameter, $with);
		$list = $dataModel->limit($limit)->select();
        return static::setFormatter($list);
	}

	// 获取文章分页列表
	public static function getPagination($parameter = [], $with = [], $pagesize = 10){
		$dataModel = static::getQueryModel($parameter, $with);
		$pagination = $dataModel->paginate($pagesize, false, ["primary_key" => "id"])->toArray();
        $pagination["data"] = static::setFormatter($pagination["data"]);
        return $pagination;
	}

	// 格式化
	public static function setFormatter($list){
		foreach ($list as $key => $info) {
			$list[$key]["date_short"] = date("Y-m-d", strtotime($info["date"]));
		}

		return $list;
	}


	// 更新文章权重
	public static function setWeight(){
		$where = ["disabled" => 0];
		
        $max_zan_num = Article::where($where)->max("zan_num");
        $max_read_num = Article::where($where)->max("read_num");
        $max_listorder = Article::where($where)->max("listorder");
        // $max_datetime = strtotime(Article::where($where)->max("date"));
        $min_datetime = strtotime(Article::where($where)->min("date"));
        $left_datetime = time() - $min_datetime;

        $articleList = Article::all(function($query) use ($where){
            $query->where($where)->field([
                "id", "read_num", "zan_num", "date",
                "listorder", "weight", "weight_hot"
            ]);
        });

        $success_num = 0;
        foreach ($articleList as $key => $article) {
            $rete_zan = $article["zan_num"] / $max_zan_num;
            $rete_read = $article["read_num"] / $max_read_num;

            $weight_zan = $rete_zan * Article::WEIGHT_ZAN;
            $weight_read = $rete_read * Article::WEIGHT_READ;
            $weight_hot = $weight_zan + $weight_read;

            if ($article["listorder"] > 10) {
                $rete_listorder = ($max_listorder - $article["listorder"]) / $max_listorder;
                $rete_datetime = (strtotime($article["date"]) - $min_datetime) / $left_datetime;

                $weight_listorder = $rete_listorder * Article::WEIGHT_SORT;
                $weight_datetime = $rete_datetime * Article::WEIGHT_DATE;
                $weight = $weight_hot + $weight_listorder + $weight_datetime;
            } else {
                $weight = 1;
            }

            $article->weight = $weight;
            $article->weight_hot = $weight_hot;
            if ($article->save() == true) {
                $success_num++;
            }
        }

        return $success_num;
	}
}