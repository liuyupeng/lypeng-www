<?php

/**
 * 标签控制器
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-24 14:38:09
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-27 23:54:32
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\admini\model\Tag;
use app\admini\model\Article;

use app\common\logic\RedisLogic;

class TagController extends CommonController{

    use \app\admini\traits\AuthTrait;

    public function select(){
        if ($this->request->isAjax() == true) {
            $tagList = Tag::all(function($query){
                $query->order("pinyin_first", "asc");
            });

            if ($tagList) {
                $tagList = collection($tagList)->toArray();
            }

            return rt("CUCCESS", 1, $tagList);
        }
        
        return $this->fetch();
    }

    // 标签列表
    public function tagList(){
    	if ($this->request->isAjax() == true) {
    		$tagList = Tag::all(function($query){
	    		$query->order("pinyin_first", "asc");
	    	});

	    	if ($tagList) {
				$tagList = collection($tagList)->toArray();
			}

			return rt("CUCCESS", 1, $tagList);
    	}

    	return $this->fetch();
    }

    // 编辑标签信息
    public function tagSave(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->param("id");
    		$name = $this->request->param("name");
    		$description = $this->request->param("description");

    		$tag = Tag::getDefault($id);
    		$tag->name = $name;
    		$tag->description = $description;
    		$tag->pinyin_full = zstr_word_full_case($name);
    		$tag->pinyin_first = strtoupper(zstr_word_first($name));
    		$tag->dateline = zdate_date();

    		$scene = isset($tag->id) ? "tag.edit" : "tag.add";
    		$r = $this->validateData($tag, $scene);

    		if ($r["res"] == 1) {
    			$r = $tag->saveData();
    		}

            if ($r["res"] == 1) {
                // 清除标签相关缓存
                $redis = RedisLogic::getConnect();
                $keys = $redis->getKeys("__TAG__*");
                $redis->del($keys);
            }

    		return $r;
    	}

    	return $this->fetch();
    }



    // 标签删除
    public function tagDelete(){
    	if ($this->request->isAjax() == true) {
    		$id = $this->request->param("id");

    		$tag = Tag::get($id);
    		$r = rt("标签不存在", 0);
    		if ( !empty($tag) ) {
    			$articleCount = Article::where("FIND_IN_SET($id, tags)")->where(["disabled" => 0])->count("id");
    			if ($articleCount > 0) {
    				$r = rt("标签已被使用不能删除", 0);
    			} else if ($tag->delete() == true) {
    				$r = rt("标签删除成功", 1);
    			} else {
    				$r = rt("标签删除失败", 0);
    			}
    		}

            if ($r["res"] == 1) {
                // 清除标签相关缓存
                $redis = RedisLogic::getConnect();
                $keys = $redis->getKeys("__TAG__*");
                $redis->del($keys);
            }

			return $r;
    	}
    }
}