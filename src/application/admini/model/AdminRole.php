<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-14 11:16:27
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-17 12:01:41
 */

namespace app\admini\model;

use app\common\model\CommonModel;

class AdminRole extends CommonModel{

	public static $tableName = "管理员角色";

	protected $createTime = "date";
    protected $updateTime  = "dateline";
    protected $autoWriteTimestamp = "datetime";
 
    // 获取角色权限
    public function getRolePrivilege(){
    	if ($this->is_admin == 1) {
    		$privilege = "ALL";
    	} else {
    		$privilegeList = Privilege::all(function($query){
    			$query->where(["disabled" => 0]);
    			$query->where(
    				"FIND_IN_SET(id, :privId)",
					["privId" => $this->privilege]
				);

    			$query->field("route_name");
    		});

    		$privilege = [];
    		foreach ($privilegeList as $key => $priv) {
    			$privilege[] = $priv["route_name"];
    		}
    	}

    	return $privilege;
    }

}