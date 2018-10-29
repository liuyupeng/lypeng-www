<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-04-04 11:32:43
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-28 15:19:23
 */

namespace app\api\controller;

use Helper;
use think\Db;
use think\Config;
use think\Loader;
use think\Request;

use app\common\model\Account;

use app\common\logic\WxLogic;
use app\common\logic\ArticleLogic;

class IndexController extends CommonController{

	use \app\api\traits\AuthTrait;

	/**
	 * 获取首页数据
	 */
	public function getIndexData(){
		$order = "listorder asc";
        $where = array("disabled" => 0, "parent_id" => 0);
        $list = Db::name("category")->where($where)->order($order)->select();

        foreach ($list as $key => $li) {
            $articleList = Db::name("article")
                ->where(["disabled" => 0])
                ->where("FIND_IN_SET(".$li["id"].", categorys)")
                ->order("listorder asc")
                ->limit(3)->select();

            if (empty($articleList)) {
                unset($list[$key]);
            } else {
            	foreach ($articleList as $k => $article) {
            		$articleList[$k]["date_short"] = date("Y-m-d", strtotime($article["date"]));
            	}

                $list[$key]["articleList"] = $articleList;
            }
        }

        $list = array_values($list);

        return Helper::rt("获取成功", 1, [
        	"items" => $list,
        	"total" => count($list)
        ]);
	}


	/**
	 * 获取某栏目下文章列表
	 * @param  $cate_id 	[必填]栏目ID
	 * @param  $page 		[必填]当前页
	 * @param  $rows 		[选填]每页显示条数
	 */
	public function getArticleList(){
		$cate_id = $this->request->param("cate_id", 0);
        $rows = $this->request->param("rows", 10);

        $pagination = Db::name("article")
            ->where(["disabled" => 0])
            ->where("FIND_IN_SET($cate_id, categorys)")
            ->order("listorder asc")
            ->paginate($rows)->toArray();

        foreach ($pagination["data"] as $key => $info) {
            $pagination["data"][$key]["date_short"] = date("Y-m-d", strtotime($info["date"]));
        }

        $where = array("id" => $cate_id);
        $pagination["category"] = Db::name("category")->where($where)->find();

        return Helper::rt("SUCCESS", 1, $pagination);
	}

	/**
	 * 获取文章详情
	 * @param  $article_id 	[必填]文章ID
	 */
	public function getArticleInfo(){
		$where = array("id" => $this->request->param("article_id"));
    	$article = Db::name("article")->where($where)->find();

    	$result = Helper::rt("您查看的内容不存在或已删除", 0);
        if ($article && $article["disabled"] == 0) {
        	// 转化短日期格式
        	$article["date_short"] = date("Y-m-d", strtotime($article["date"]));

        	// 是否已点赞
	        $zan_users = explode(",", $article["zan_users"]);
	        if (in_array($this->identity["userid"], $zan_users)) {
	            $article["is_zan"] = 1;
	        } else {
	        	$article["is_zan"] = 0;
	        }

	        // 获取栏目信息
        	$cateInfo = Db::name("category")->where(["id" => $article["cate_id"]])->find();
        	$article["cateInfo"] = $cateInfo;

        	// 阅读数+1
        	$data = [
        		"read_num" => $article["read_num"] + 1,
        		"dateline" => date("Y-m-d H:i:s")
        	];
        	Db::name("article")->where($where)->data($data)->update();

        	$result = Helper::rt("文章内容获取成功", 1, $article);
        }

        return $result;
	}

	/**
	 * 文章点赞
	 * @param  $article_id [必填]文章ID
	 */
    public function doArticleZan(){
        $where = array("id" => $this->request->param("article_id"));
        $article = Db::name("article")->where($where)->find();

        $r = Helper::rt("内容不存在或已删除", 0);
        if ($article && $article["disabled"] == 0) {
        	$zan_users = explode(",", $article["zan_users"]);
        	if (in_array($this->identity["userid"], $zan_users)) {
        		$r = Helper::rt("你已经点过赞了", 0);
        	} else {
        		$zan_users[] = $this->identity["userid"];
                $zan_users = array_filter(array_unique($zan_users));

                $data = array(
                    "zan_num" => $article["zan_num"] + 1,
                    "zan_users" => implode(",", $zan_users),
                    "dateline" => date("Y-m-d H:i:s")
                );

                $result = Db::name("article")->where($where)->data($data)->update();
                if ($result) {
                	$r = Helper::rt("点赞成功", 1, [
                		"zan_num" => $data["zan_num"]
                	]);
                } else {
                	$r = Helper::rt("点赞失败", 0);
                }
        	}
        }

        return $r;
    }


	/**
	 * 登录
	 * @param  $wx_code 	[必填]小程序登录接口获取的code
	 */
	public function login(){
		$wx_code = $this->request->param("wx_code");

		$r = Helper::rt("登录失败", 0);

		$result = WxLogic::getInstance()->getSnsInfo($wx_code);

		if ($result["res"] == 1) {
            $wx_openid = $result["data"]["openid"];
            $session_key = $result["data"]["session_key"];

            $account = Account::saveInfo($wx_openid, $session_key);
            if ( !empty($account) ) {
            	$r = Helper::rt("登录成功", 1, $account);
            }
        }

        return $r;
	}

	/**
	 * 用户信息上传
	 * @param  $wx_rawdata [必填]小程序获取用户信息接口获取的数据包
	 */
	public function uploadUserInfo(){
		$wx_rawdata = $_POST["wx_rawdata"];
		$rawdata = json_decode($wx_rawdata, true);

		$r = Helper::rt("参数错误", 0);
		if ( $rawdata && is_array($rawdata) ) {
			$identity = $this->identity;

	        $identity->wx_nickname = isset($rawdata["nickName"]) ? $rawdata["nickName"] : "";
	        $identity->wx_avatarurl = isset($rawdata["avatarUrl"]) ? $rawdata["avatarUrl"] : "";
	        $identity->wx_gender = isset($rawdata["gender"]) ? $rawdata["gender"] : 0;
	        $identity->wx_country = isset($rawdata["country"]) ? $rawdata["country"] : "";
	        $identity->wx_province = isset($rawdata["province"]) ? $rawdata["province"] : "";
	        $identity->wx_city = isset($rawdata["city"]) ? $rawdata["city"] : "";
	        $identity->dateline = date("Y-m-d H:i:s");
	        $identity->uuid = Helper::zstrGuid();

	        if ($identity->save() == true) {
	            $r = Helper::rt("用户信息保存成功", 1, $identity->toArray());
	        } else {
	        	$r = Helper::rt("用户信息保存失败", 0);
	        }
		}

		return $r;
	}

}