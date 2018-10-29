<?php
namespace app\index\controller;

use think\Db;
use think\Config;
use think\Loader;

use app\index\model\Article;
use app\common\logic\ArticleLogic;

class IndexController extends CommonController{

    use \app\index\traits\AuthTrait;

    private $customConfig;

    protected function init(){
        // 获取网站设置
        $this->customConfig = Config::get("custom_config");
    }

    // 首页
    public function index($rows = 10){
        // 重定位到PC版
        if (zdevice_get() == "computer") {
            $this->redirect($this->customConfig["http_www"]);
            exit();
        }

        $order = "listorder asc";
        $where = array("disabled" => 0, "parent_id" => 0);
        $list = Db::name("category")->where($where)->order($order)->select();

        foreach ($list as $key => $li) {
            $articleList = ArticleLogic::getDataAll([
                "cate_id" => $li["id"]
            ], ["cateInfo"], 3);

            if (empty($articleList)) {
                unset($list[$key]);
            } else {
                $list[$key]["articleList"] = $articleList;
            }
        }

        $this->assign("list", $list);
        return $this->fetch();
    }

    // 文章列表
    public function articleList(){
        $tag_id = $this->request->param("tag_id", 0);
        $cate_id = $this->request->param("cate_id", 0);

        if ($this->request->isAjax() == true) {
            $rows = $this->request->param("rows", 10);
            $pagination = ArticleLogic::getPagination([
                "tag_id" => $tag_id,
                "cate_id" => $cate_id
            ], ["cateInfo"], $rows);

            return self::rt("SUCCESS", 1, $pagination);
        }

        // 重定位到PC版
        if (zdevice_get() == "computer") {
            $this->redirect(
                $this->customConfig["http_www"] .
                "/index/index?tag_id=" . $tag_id . '&cate_id=' . $cate_id
            );
            exit();
        }


        $title = "文章列表";
        $description = "显示所有分类下文章";
        if ( !empty($cate_id) ) {
            $cateInfo = Db::name("category")->where(["id" => $cate_id])->find();
            if (empty($cateInfo) || $cateInfo["disabled"] == 1) {
                return $this->_error("Not Found", "栏目不存在或已删除");
            }

            $title = $cateInfo["name"];
            $description = $cateInfo["description"];
        } else if ( !empty($tag_id) ) {
            $tagInfo = Db::name("tag")->where(["id" => $tag_id])->find();
            if ( empty($tagInfo) ) {
                return $this->_error("Not Found", "标签不存在或已删除");
            }

            $title = $tagInfo["name"];
            $description = strtoupper($tagInfo["pinyin_full"]);
        }

        $this->assign("title", $title);
        $this->assign("description", $description);
        $this->assign("tag_id", $tag_id);
        $this->assign("cate_id", $cate_id);
        return $this->fetch();
    }

    // 文章详情
    public function articleInfo(){
        // 重定位到PC版
        if (zdevice_get() == "computer") {
            $uuid = $this->request->param("uuid");
            $this->redirect($this->customConfig["http_www"] . "/index/detail?uuid=" . $uuid);
            exit();
        }

        $where = array("uuid" => $this->request->param("uuid"));
    	$article = Db::name("article")->where($where)->find();

        if (empty($article) || $article["disabled"] == 1) {
            return $this->_error("Not Found", "您查看的文章不存在或已删除");
        }

        // 转化短日期格式
        $article["date_short"] = date("Y-m-d", strtotime($article["date"]));

        // 是否已点赞
        $zan_class = "zan_empty";
        $zan_users = explode(",", $article["zan_users"]);
        if (in_array($this->userinfo["id"], $zan_users)) {
            $zan_class = "zan_full";
        }

        // 获取栏目信息
        $cateInfo = Db::name("category")->where(["id" => $article["cate_id"]])->find();

        // 获取标签
        $tagList = Db::name("tag")->where(["id" => ["in", $article["tags"]]])->select();

        // 阅读数+1
        $article["read_num"]++;
        $data = ["read_num" => $article["read_num"]];
        $result = Db::name("article")->where($where)->data($data)->update();
    	
        $this->assign("info", $article);
        $this->assign("cateInfo", $cateInfo);
        $this->assign("zan_class", $zan_class);
        $this->assign("tagList", $tagList);
        return $this->fetch();
    }

    // 文章点赞
    public function articleZan($uuid = ""){
        if ($this->request->isAjax()) {
            $where = array("uuid" => $uuid);
            $article = Db::name("article")->where($where)->find();
            if (empty($article)) self::rt("文章不存在", 0);

            if (self::$result["res"] == 1) {
                $zan_users = explode(",", $article["zan_users"]);
                if (!in_array($this->userinfo["id"], $zan_users)) {
                    $zan_users[] = $this->userinfo["id"];
                    $zan_users = array_filter(array_unique($zan_users));

                    $data = array(
                        "zan_num" => $article["zan_num"] + 1,
                        "zan_users" => implode(",", $zan_users)
                    );

                    $result = Db::name("article")->where($where)->data($data)->update();
                    if (empty($result)) self::rt("操作失败", 0);
                } else {
                    self::rt("操作失败", 0);
                }
            }

            return self::$result;
        }
    }

}