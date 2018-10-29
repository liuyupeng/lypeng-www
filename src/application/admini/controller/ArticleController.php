<?php

/**
 * 文章控制器
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:38:09
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-27 23:52:49
 */


namespace app\admini\controller;

use think\Db;
use think\Config;

use app\admini\model\Tag;
use app\admini\model\Admin;
use app\admini\model\Article;
use app\admini\model\Category;

use app\common\logic\ArticleLogic;
use app\common\logic\RedisLogic;

class ArticleController extends CommonController{

    use \app\admini\traits\AuthTrait;

    public function _init(){
    }

    // 文章列表
    public function articleList($sortName = "weight", $sortOrder = "desc"){
    	if ($this->request->isAjax() == true) {
            $pageSize = $this->request->param("pageSize", 10);

            $paginate = ArticleLogic::getPagination([
                "cate_id" => $this->request->param("cate_id"),
                "is_show" => $this->request->param("is_show", 1),
                "keywords" => $this->request->param("searchText"),
                "order" => [$sortName => $sortOrder]
            ], ["cateInfo"], $pageSize);

            return [
                "rows" => $paginate["data"],
                "total" => $paginate["total"]
            ];
    	}

        return $this->fetch();
    }

    // 新增文章
    public function articleAdd(){
    	if ($this->request->isAjax() == true) {
			$article = new Article();
			$article->title = $this->request->post("title");
            $article->cate_id = $this->request->post("cate_id");
			$article->thumb = $this->request->post("thumb");
            $article->description = $this->request->post("description");
            $article->author = $this->request->post("author");
            $article->listorder = $this->request->post("listorder", 9999, "intval");
            $article->content = $_POST["content"];

            $tags = $this->request->post("tags/a", []);
            $tags = array_filter(array_unique($tags));
            $article->tags = implode(",", $tags);

			$article->u_id = $this->userinfo["id"];
			$article->u_account = $this->userinfo["account"];
            $article->dateline = zdate_date();
            $article->date = zdate_date();
            $article->uuid = zstr_guid();
            $article->disabled = 0;
            $article->is_show = 1;

			$check_result = $this->validate($article, "article.save");
	    	if ($check_result !== true) {
	    		self::rt($check_result, 0);
	    	}

	    	if (self::$result["res"] == 1 && $article->cate_id) {
	    		$cateinfo = Category::get($article->cate_id);
	    		if (!empty($cateinfo)) {
	    			$article->categorys = $cateinfo->categorys;
	    		} else {
	    			self::rt("所属栏目不存在", 0);
	    		}
	    	}

	    	if (self::$result["res"] == 1) {
	    		if (false == $article->save()) {
	    			self::rt("保存失败", 0);
	    		}
	    	}

	    	return self::$result;
    	}

        return $this->fetch();
    }

    // 编辑文章信息
    public function articleEdit(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->post("id");
			$article = Article::get($id);
			$article->title = $this->request->post("title");
            $article->thumb = $this->request->post("thumb");
            $article->author = $this->request->post("author");
            $article->description = $this->request->post("description");
            $article->listorder = $this->request->post("listorder", 9999, "intval");
            $article->disabled = $this->request->post("disabled", 0, "intval");
			$article->content = $_POST["content"];
			$article->dateline = zdate_date();

            $tags = $this->request->post("tags/a", []);
            $tags = array_filter(array_unique($tags));
            $article->tags = implode(",", $tags);

			$check_result = $this->validate($article, "article.save");
	    	if ($check_result !== true) {
	    		self::rt($check_result, 0);
	    	}

	    	if (self::$result["res"] == 1) {
	    		if (false == $article->save()) {
	    			self::rt("保存失败", 0);
	    		}
	    	}

	    	return self::$result;
    	}

    	$id = $this->request->get("id");
    	$article = Article::get($id);

    	if (empty($article)) {
    		return $this->_error("Not Found", "您要查看的内容不存在");
    	}

    	$cate_info = $article->cateInfo;
        @$parent_info = $cate_info->parentInfo;
        
        $cate_info = $cate_info ? $cate_info->toArray() : [];
    	$parent_info = $parent_info ? $parent_info->toArray() : [];

        $tags = explode(",", $article->tags);
        $tagList = Tag::where(["id" => ["in", $tags]])->select();

    	$this->assign("info", $article->toArray());
        $this->assign("cate_info", $cate_info);
        $this->assign("parent_info", $parent_info);
        $this->assign("tags", json_encode($tags));
    	$this->assign("tagList", json_encode($tagList));

        return $this->fetch();
    }

    // 隐藏，显示
    public function articleActived(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->param("id");
    		$actived = $this->request->param("actived", 0, "intval");

    		$r = Article::getOne($id);
    		if ($r["res"] == 1) {
    			$article = $r["data"];

    			$article->is_show = $actived;
                $article->dateline = zdate_date();
    			$r = $article->saveData();
    		}

			return $r;
    	}
    }

    // 文章删除
    public function articleRemove(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->param("id");

    		$r = Article::getOne($id);
    		if ($r["res"] == 1) {
    			$article = $r["data"];
    			if ($article->is_show == 1) {
    				$r = rt("文章显示中不能删除", 0);
    			}
    		}

    		if ($r["res"] == 1) {
    			$article->disabled = 1;
                $article->dateline = zdate_date();
    			$r = $article->saveData();
    		}

			return $r;
    	}
    }

    // 更新权重
    public function articleWeight(){
        if ($this->request->isAjax() == true) {
            $success_num = ArticleLogic::setWeight();
            return rt("权重更新成功", 1, ["number" => $success_num]);
        }
    }




    // 获取栏目数据
    public function cateAll(){
    	if ($this->request->isAjax() == true) {
	    	$categoryList = Category::all(function($query){
	    		$parent_id = $this->request->param("parent_id");
	    		$query->where("disabled", 0)->where("parent_id", $parent_id)->order("listorder", "asc");
	    	});

	    	if ($categoryList) {
				$categoryList = collection($categoryList)->toArray();
			}

			return self::rt("CUCCESS", 1, $categoryList);
    	}
    }

    // 文章栏目列表
    public function cateList(){
    	if ($this->request->isAjax() == true) {
    		$categoryList = Category::all(function($query){
	    		$query->where(["disabled" => 0])->with("parentInfo")->order("listorder", "asc");
	    	});

	    	if ($categoryList) {
				$categoryList = collection($categoryList)->toArray();
			}

            // $dpTree = new \DpTree(array("pid" => "parent_id"));
            // $categoryList = $dpTree->getTree($categoryList);

            foreach ($categoryList as $key => $category) {
                // if ($category["parent_id"]) {
                //     $categoryList[$key]["parentId"] = $category["parent_id"];
                // } else {
                // }
                $categoryList[$key]["parentId"] = $category["parent_id"] ? : null;
            }


            return $categoryList;
			return self::rt("CUCCESS", 1, $categoryList);
    	}

        return $this->fetch();
    }

    // 新增栏目
    public function cateAdd(){
    	if ($this->request->isAjax()){
    		$category = new Category();
	    	$category->name = $this->request->post("name");
	    	$category->parent_id = $this->request->post("parent_id", 0, "intval");
	    	$category->description = $this->request->post("description");
	    	$category->listorder = $this->request->post("listorder", 255, "intval");
	    	$category->disabled = $this->request->post("disabled", 0, "intval");
	    	$category->dateline = zdate_date();
	    	$category->date = zdate_date();
	    	$category->categorys = "";

	    	$check_result = $this->validate($category, "category.add");
	    	if ($check_result !== true) {
	    		self::rt($check_result, 0);
	    	}

	    	if (self::$result["res"] == 1 && $category->parent_id) {
	    		$parent = Category::get($category->parent_id);
	    		if (!empty($parent)) {
	    			$category->categorys = $parent->categorys;
	    		} else {
	    			self::rt("所属栏目不存在", 0);
	    		}
	    	}

	    	if (self::$result["res"] == 1) {
	    		if (true == $category->save()) {
	    			$category->categorys = trim($category->categorys . "," . $category->id, ",");
	    			$category->save();

                    // 清除栏目相关缓存
                    $redis = RedisLogic::getConnect();
                    $keys = $redis->getKeys("__CATEGORY__*");
                    $redis->del($keys);
	    		} else {
	    			self::rt("保存失败", 0);
	    		}
	    	}
	    	
	    	return self::$result;
    	}

        return $this->fetch();
    }

    // 编辑栏目信息
    public function cateEdit(){
    	if ($this->request->isAjax()){
    		$id = $this->request->param("id");
    		$category = Category::get($id);
	    	$category->name = $this->request->post("name");
	    	$category->description = $this->request->post("description");
	    	$category->listorder = $this->request->post("listorder", 255, "intval");
	    	$category->disabled = $this->request->post("disabled", 0, "intval");
	    	$category->dateline = zdate_date();

	    	$check_result = $this->validate($category, "category.edit");
	    	
	    	if ($check_result !== true) {
	    		self::rt($check_result, 0);
	    	}

	    	if (self::$result["res"] == 1) {
	    		if (false == $category->save()) {
	    			self::rt("保存失败", 0);
	    		} else {
                    // 清除栏目相关缓存
                    $redis = RedisLogic::getConnect();
                    $keys = $redis->getKeys("__CATEGORY__*");
                    $redis->del($keys);
                }
	    	}

	    	return self::$result;
    	}

    	$id = $this->request->param("id");
    	$category = Category::get($id);

    	if (empty($category)) {
    		return $this->_error("Not Found", "您要查看的内容不存在");
    	}

    	$parent_info = $category->parentInfo;
    	$parent_info = $parent_info ? $parent_info->toArray() : [];

    	$this->assign("info", $category->toArray());
    	$this->assign("parent_info", $parent_info);
        return $this->fetch();
    }

    // 栏目删除
    public function cateRemove(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->param("id");

    		$r = Category::getOne($id);
    		if ($r["res"] == 1) {
    			$category = $r["data"];

    			$articleCount = Article::where("FIND_IN_SET($id, categorys)")->where(["disabled" => 0])->count("id");
    			if ($articleCount > 0) {
    				$r = rt("栏目已被使用不能删除", 0);
    			}
    		}

    		if ($r["res"] == 1) {
    			$category->disabled = 1;
                $category->dateline = zdate_date();
    			$r = $category->saveData();
    		}

            if ($r["res"] == 1) {
                // 清除栏目相关缓存
                $redis = RedisLogic::getConnect();
                $keys = $redis->getKeys("__CATEGORY__*");
                $redis->del($keys);
            }

			return $r;
    	}
    }
}