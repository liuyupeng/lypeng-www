<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-04-16 14:42:17
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-10-27 23:55:39
 */

namespace app\www\controller;

use think\Db;
use think\Config;
use think\Controller;

use app\admini\model\Account;
use app\common\model\Account as CAccount;

use app\common\logic\RedisLogic;

class DataController extends Controller {

	public static $dateBj = "2015-03-21";
	public static $dateSh = "2017-05-31";
	public static $dayNumber = [1000, 2000];

	public function index(){

        $redisLogic = new RedisLogic();
        $redis = $redisLogic->getConnect();

        $keys = $redis->getKeys("*");
        // $redis->del($keys);
        dump($keys);exit;


        // $order = "listorder asc";
        // $field = ["id", "name", "listorder"];
        // $where = array("disabled" => 0, "parent_id" => 0);
        // $list = Db::name("category")->where($where)->field($field)->order($order)->select();


        $redis = RedisLogic::getConnect();
        // foreach ($list as $key => $li) {
        //     $redis->hmset("category_test_" . $li["id"], $li);
        // }

        $keys = $redis->getKeys("category_test_*");
        // $keys = $redis->exists("test_*");
        dump($keys);

        $category_1 = $redis->del($keys);
        // $category_1 = $redis->hgetall("category_test_9");

// dump($category_1);
exit;

        $redis->set("aaa", ["abc" => 1, "ddd" => 2]);

        // dump($redis->getKeys("*"));exit;
        dump($redis->get("aaa"));exit;
        // echo "index";
exit('access denied.');

            Db::name('tablename')->where([
                "uid" => 1,
                "is_deleted" => 0
            ])->where(function($query){
                $query->where([
                    "status" => 1
                ])->whereOr(function($qry){
                    $qry->where(["status" => 2, "type" => 1]);
                });
            })->select();

echo Db::name('test')->getLastSql();
        // dump($list);
        // exit;

        return $this->fetch('empty');
	}

    public function toShanghai(){
    	$_time = strtotime(date("Y-m-d")) - strtotime(static::$dateSh);

    	$_day = ($_time / 86400) + 1;

    	echo "今天是来上海的第 <b>" . $_day . "</b> 天";
    }

    public function toBeijing(){
    	$_time = strtotime(date("Y-m-d")) - strtotime(static::$dateBj);
    	$_day = ($_time / 86400) + 1;

    	$total = [];

        $this->assign("today", date("Y-m-d"));
    	$this->assign("city_name", "北京");
    	$this->assign("date_begin", static::$dateBj);
    	$this->assign("date_days", $_day);
    	$this->assign("total", $total);
        return $this->fetch("day");

    	echo "今天是来北京的第 <b>" . $_day . "</b> 天";

    	echo "<br /><br />";
    	$_time = strtotime(static::$dateBj) + 2000 * 86400;
    	$_day = date("Y年m月d日", $_time);
    	echo "在北京的第2000天是：<b>" . $_day . "</b>";
    }
}


