<?php

/**
 * 主控制器
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:38:09
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-20 17:09:44
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\admini\model\Admin;
use app\common\model\nba\Player;

use app\common\logic\CosLogic;

class IndexController extends CommonController{

    use \app\admini\traits\AuthTrait;
	use \app\admini\traits\MenuTrait;

    public function main(){
        return $this->fetch();
    }

    public function index(){
        $this->assign("menu_list", $this->get_menu_filter_list());
        $this->assign("userinfo", $this->userinfo);
        return $this->fetch();
    }
    
    // 欢迎页面
    public function welcome(){
        $where = ["disabled" => 0];
        $admin_count = Db::name("admin")->where($where)->count("id");
        $admin_role_count = Db::name("admin_role")->where($where)->count("id");

        $category_count = Db::name("category")->where($where)->count("id");
        $article_count = Db::name("article")->where($where)->count("id");
        $tag_count = Db::name("tag")->count("id");

        $this->assign("admin_count", $admin_count);
        $this->assign("admin_role_count", $admin_role_count);
        $this->assign("category_count", $category_count);
        $this->assign("article_count", $article_count);
        $this->assign("tag_count", $tag_count);
        return $this->fetch();
    }

    // 用户登录
    public function login(){

        if ($this->request->isAjax()){
            $account = $this->request->post("account");
            $password = $this->request->post("password");

            $admin = Admin::get(["account" => $account]);
            if (empty($admin)) {
                self::rt("账号不存在", 0);
            } else {
                $result = $admin->validatePassword($password);
                if ($result == true) {
                    $this->doAuthSet($admin->toArray());
                } else {
                    self::rt("密码输入错误", 0);
                }
            }

            return self::$result;
        }

        if ($this->doAuthCheck() == true) {
            return $this->redirect(url("index"));
        }

        return $this->fetch();
    }

    // 退出登录
    public function logout(){
        if ($this->doAuthDelete() == true) {
            return $this->redirect(url("login"));
        } else {
            return $this->redirect(url("index"));
        }
    }

    // 个人资料
    public function adminInfo(){
        return $this->fetch();
    }

    // 修改密码
    public function editPassword(){

        if ($this->request->isAjax()) {
            $password = $this->request->param("password");
            $rep_password = $this->request->param("rep_password");
            $old_password = $this->request->param("old_password");

            if (empty($old_password)) {
                self::rt("请先输入原密码", 0);
            } else if (empty($password)) {
                self::rt("请先输入新密码", 0);
            } else if (empty($rep_password)) {
                self::rt("请再次输入新密码", 0);
            } else if ($password != $rep_password) {
                self::rt("两次密码输入不一致", 0);
            }

            if (self::$result["res"] == 1) {
                $admin = Admin::get($this->userinfo["id"]);
                $result = $admin->validatePassword($old_password);
                if ($result == false) {
                    self::rt("原密码输入错误", 0);
                }
            }

            if (self::$result["res"] == 1) {
                $passwords = Admin::password($password);
                $admin->encrypt = $passwords["encrypt"];
                $admin->password = $passwords["password"];

                if ($admin->save() == false) {
                    self::rt("密码修改失败", 0);
                }
            }

            return self::$result;
        }

        return $this->fetch();
    }

    // 图片上传
    public function upload(){

        $r = zimg_upload();
        if ($r["res"] == 1) {
            $imgInfo = $r["data"];

            // 图片缩放
            zimg_zoom_max($imgInfo["path"], 720, 720);

            // 上传到腾讯云
            $res = CosLogic::uploadOne($imgInfo["path"], $imgInfo["name"], true);

            $r = rt($res["msg"], $res["ret"]);
            if ($r["res"] == 1) {
                $r["data"] = $res["data"];

                $preview = $res["data"]["url"];
                if ($module = $this->request->param("module")) {
                    $thumb_module = "thumb_" . $module;
                    $custom_config = Config::get("custom_config");
                    $imageView = $custom_config["cos_loven"]["imageView"];

                    if (array_key_exists($thumb_module, $imageView)) {
                        $preview = $res["data"]["url"] . $imageView[$thumb_module];
                    }
                }

                $r["data"]["preview"] = $preview;
            }

            // 删除本地文件
            unlink($imgInfo["path"]);
        }
        
        return $r;
    }
}