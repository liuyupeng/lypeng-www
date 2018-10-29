<?php

/**
 * 管理员模型
 * @Author: lovenLiu
 * @Email:  lypeng9205@163.com
 * @Date:   2018-08-24 14:40:28
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 12:42:05
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class Admin extends CommonModel{

    public static $tableName = "管理员";
    
    protected $createTime = "date";
    protected $updateTime  = "dateline";
    protected $autoWriteTimestamp = "datetime";

	protected $readonly = ["id", "account"];

    private static $initPasswd = "123456";

    public function roleInfo(){
        return $this->hasOne("AdminRole", "id", "role_id");
    }

    public static function getInitPasswd(){
        return static::$initPasswd;
    }

    // 密码加密规则
    public static function password($password = "", $encrypt = ""){
        if(empty($password)) $password = static::$initPasswd;
        if(empty($encrypt) || strlen($encrypt) !== 8) $encrypt = zstr_randstr(8);

        $_encrypt = md5($encrypt);
        $_password = md5($password);
        $crypt_pwd = crypt($_password, $_encrypt);
        $md5_password = md5($crypt_pwd);

        return ["encrypt" => $encrypt, "password" => $md5_password];
    }

    // 验证密码
    public function validatePassword($password){
        $passwords = $this->password($password, $this->encrypt);
        return $this->password === $passwords["password"];
    }
}