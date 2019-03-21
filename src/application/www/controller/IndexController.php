<?php
namespace app\www\controller;

use Helper;
use think\Db;
use think\Config;
use think\Loader;
use think\Request;

use app\admini\model\Article;

use app\common\logic\ArticleLogic;
use app\common\logic\RedisLogic;

class IndexController extends CommonController{

    use \app\www\traits\AuthTrait;

    private $navCateList;
    private $customConfig;

    protected function init(){
        // 获取网站设置
        $this->customConfig = Config::get("custom_config");
        $this->assign("websiteConfig", $this->customConfig["website"]);

        $this->getFirstCateMenu();
    }

    // 获取一级栏目
    private function getFirstCateMenu(){
        $redis_key = 'first_cate_menu_';
        $redisLogic = new RedisLogic("__CATEGORY__");
        if ($redisLogic->checkListData($redis_key)) {
            $list = $redisLogic->getListData($redis_key);
            $list = zarray_sort($list, "listorder", "asc");
        } else {
            $order = "listorder asc";
            $field = ["id", "name", "listorder"];
            $where = array("disabled" => 0, "parent_id" => 0);
            $list = Db::name("category")->where($where)->field($field)->order($order)->select();

            if (!empty($list)) {
                $redisLogic->saveListData($redis_key, $list);
            }
        }

        $this->navCateList = $list;
        $this->assign("nav_cate_id", -1);
        $this->assign("navCateList", $list);
    }

    // 获取当前栏目的子栏目
    private function getLeftCateMenu($nav_cate_id = 0, $left_cate_id = 0){
    	if ($nav_cate_id == 0) {
    		$list = $this->navCateList;
    	} else {
            $redis_key = 'left_cate_menu_' . $nav_cate_id . '_';
            $redisLogic = new RedisLogic("__CATEGORY__");
            if ($redisLogic->checkListData($redis_key)) {
                $list = $redisLogic->getListData($redis_key);
                $list = zarray_sort($list, "listorder", "asc");
            } else {
        		$order = "listorder asc";
    	        $field = ["id", "name", "listorder"];
    	        $where = array("disabled" => 0, "parent_id" => $nav_cate_id);
    	        $list = Db::name("category")->where($where)->field($field)->order($order)->select();

                if (!empty($list)) {
                    $redisLogic->saveListData($redis_key, $list);
                }
            }
    	}

        $this->assign("leftCateList", $list);
        $this->assign("nav_cate_id", $nav_cate_id);
        $this->assign("left_cate_id", $left_cate_id);
    }

    // 获取本站标签
    private function getRightTagList(){
        $redis_key = 'right_tag_list_';
        $redisLogic = new RedisLogic("__TAG__");
        if ($redisLogic->checkListData($redis_key)) {
            $list = $redisLogic->getListData($redis_key);
            $list = zarray_sort($list, "date", "desc");
        } else {
        	$order = "date desc";
        	$field = ["id", "name", "pinyin_full", "date"];
        	$list = Db::name("tag")->field($field)->order($order)->select();

            if (!empty($list)) {
                $redisLogic->saveListData($redis_key, $list);
            }
        }

    	$this->assign("rightTagList", $list);
    }


    // 首页
    public function index(){
        $parameter = [
            "tag_id" => $this->request->param("tag_id", 0),
            "cate_id" => $this->request->param("cate_id", 0),
            "keywords" => $this->request->param("keywords", "")
        ];

        if ($this->request->isAjax()) {
            $pagination = ArticleLogic::getPagination($parameter, ["cateInfo"]);
            return rt("success", 1, $pagination);
        }

        // 重定位到手机版
        if (zdevice_get() != "computer") {
            // 重定位到手机版列表页
            if ($parameter['tag_id'] || $parameter['cate_id']) {
                $this->redirect(
                    $this->customConfig["http_web"] .
                    "/index/articleList?tag_id=" . $parameter['tag_id'] .
                    '&cate_id=' . $parameter['cate_id']
                );
            } else { // 重定位到手机版首页
                $this->redirect($this->customConfig["http_web"]);
            }
            exit();
        }


        $title = "全部";
        $description = "显示所有分类下文章";
        $nav_cate_id = 0;
        $left_cate_id = 0;
        if ( !empty($parameter["cate_id"]) ) {
            $cateInfo = Db::name("category")->where(["id" => $parameter["cate_id"]])->find();
            if (empty($cateInfo) || $cateInfo["disabled"] == 1) {
                return $this->_error("Not Found", "栏目不存在或已删除");
            }

            $left_cate_id = $cateInfo["id"];
            if ($cateInfo["parent_id"] == 0) {
                $nav_cate_id = $cateInfo["id"];
            } else {
            	$nav_cate_id = $cateInfo["parent_id"];
            }

            $title = $cateInfo["name"];
            $description = $cateInfo["description"];
        } else if ( !empty($parameter["tag_id"]) ) {
            $tagInfo = Db::name("tag")->where(["id" => $parameter["tag_id"]])->find();
            if ( empty($tagInfo) ) {
                return $this->_error("Not Found", "标签不存在或已删除");
            }

            $title = $tagInfo["name"];
            $description = strtoupper($tagInfo["pinyin_full"]);
        } else if (!empty($parameter["keywords"]) ){
            $title = $parameter["keywords"];
            $description = "关键词模糊查询";
        }

        $rightTagList = $this->getRightTagList();
    	$leftCateList = $this->getLeftCateMenu($nav_cate_id, $left_cate_id);

        $pagination = ArticleLogic::getPagination($parameter, ["cateInfo"]);
        $articleList = $pagination["data"];

        $this->assign("title", $title);
        $this->assign("description", $description);
        $this->assign("tag_id", $parameter["tag_id"]);
        $this->assign("keywords", $parameter["keywords"]);
        $this->assign("articleList", json_encode($articleList));
    	$this->assign("has_more", $pagination["has_more"]);
    	return $this->fetch();
    }

    // 文章详情
    public function detail(){
        // 重定位到手机版
        if (zdevice_get() != "computer") {
            $uuid = $this->request->param("uuid");
            $this->redirect($this->customConfig["http_web"] . "/index/articleInfo?uuid=" . $uuid);
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
        $is_user_zan = 0;
        $zan_users = explode(",", $article["zan_users"]);
        if (in_array($this->userinfo["id"], $zan_users)) {
            $is_user_zan = 1;
        }

        // 获取栏目信息
        $cateInfo = Db::name("category")->where(["id" => $article["cate_id"]])->find();

        // 获取标签
        $tagList = Db::name("tag")->where(["id" => ["in", $article["tags"]]])->select();

        // 阅读数+1
        $article["read_num"]++;
        $data = ["read_num" => $article["read_num"]];
        $result = Db::name("article")->where($where)->data($data)->update();

        $rightTagList = $this->getRightTagList();


        $cate_id = $cateInfo["parent_id"] ? : $cateInfo["id"];
        $where = "FIND_IN_SET($cate_id, categorys)";

        // 上一篇
        $prevInfo = Db::name("article")->where($where)->where([
            "disabled" => 0, "is_show" => 1,
            "id" => ["lt", $article["id"]]
        ])->field(["id", "uuid", "title", "date"])->order("id desc")->find();

        // 下一篇
        $nextInfo = Db::name("article")->where($where)->where([
            "disabled" => 0, "is_show" => 1,
            "id" => ["gt", $article["id"]]
        ])->field(["id", "uuid", "title", "date"])->order("id asc")->find();

        $this->assign("info", $article);
        $this->assign("cateInfo", $cateInfo);
        $this->assign("tagList", $tagList);
        $this->assign("is_user_zan", $is_user_zan);
        $this->assign("prevInfo", $prevInfo);
        $this->assign("nextInfo", $nextInfo);
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

    // 文件上传
    public function upload(){
    	if ($this->request->isPost()) {

    		Loader::import("cosSDK.cos-autoloader", EXTEND_PATH);

			$cosClient = new \Qcloud\Cos\Client(array(
				'region' => "sh",
	    		'credentials'=> array(
			        'appId' => "1256381791",
			        'secretId'    => "",
			        'secretKey' => ""
			    )
	    	));

			try {

				$result = $cosClient->getObject(array(
				    //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
				    'Bucket' => 'loven-1256381791',
				    'Key' => 'lypeng/20180330/aaa.txt'
				));

				Helper::zdataDownload($result['Body'], 'lypeng/20180330/aaa.txt');
exit();

				$fileinfo = pathinfo("lypeng/20180330/aaa.txt");
			    header('Content-type: application/x-'.$fileinfo['extension']);
			    header('Content-Disposition: attachment; filename=aaa.txt');
			    header('Content-Length: '. strlen($result['Body']));

				echo($result['Body']);

				exit();

				// try {
				//     $result = $cosClient->upload(
				//         $bucket='testbucket-1252448703',
				//         $key = '111.txt',
				//         $body = str_repeat('a', 5* 1024 * 1024),
				//         $options = array(
				//             "ACL"=>'private',
				//             'CacheControl' => 'private',
				//             'ServerSideEncryption' => 'AES256'));
				//     print_r($result);
				// } catch (\Exception $e) {
				//     echo "$e\n";
				// }

			    $result = $cosClient->upload(
			        //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
			        $bucket='loven-1256381791',
			        $key = 'lypeng/20180330/aaa.txt',
			        $body = "dsdksjdljsdlk",
			        $options = array(
			            "ACL"=>'private',
			            'CacheControl' => 'private',
			            'ServerSideEncryption' => 'AES256'
			        )
			    );

			    print_r($result);exit;
			} catch (\Exception $e) {
			    echo "$e\n";
			}

    		exit;
    	}

    	return $this->fetch();
    }
}