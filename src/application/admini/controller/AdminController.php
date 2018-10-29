<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-14 11:05:31
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-14 17:15:04
 */

namespace app\admini\controller;

use think\Db;
use think\Config;

use app\admini\model\Admin;
use app\admini\model\AdminRole;
use app\admini\model\Privilege;

class AdminController extends CommonController{

    use \app\admini\traits\AuthTrait;

    public function _init(){
    }

    // 管理员列表
    public function search(){
    	if ($this->request->isAjax() == true) {
    		$list = Admin::all(function($query) {
	    		$query->where(["disabled" => 0])->with("roleInfo");
                $query->order(["date" => "desc"]);
	    	});

	    	if ($list) {
				$list = collection($list)->toArray();
			}

			return self::rt("CUCCESS", 1, $list);
		}

		$this->assign("initPasswd", Admin::getInitPasswd());
    	return $this->fetch();
    }

    // 新增管理员
    public function add(){
    	$roleList = AdminRole::all(function($query){
    		$query->where(["disabled" => 0]);
    		$query->order(["date" => "desc"]);
    		$query->field(["id", "name", "date"]);
    	});

    	$this->assign("roleList", $roleList);
    	return $this->fetch("save");
    }

    // 编辑管理员信息
    public function edit(){
    	$id = $this->request->param("id");
    	$admin = Admin::get($id);
    	if ( empty($admin) || $admin->disabled == 1 ) {
    		return $this->_error("Not Found", "管理员不存在或已删除");
    	}

    	$roleList = AdminRole::all(function($query){
    		$query->where(["disabled" => 0]);
    		$query->order(["date" => "desc"]);
    		$query->field(["id", "name", "date"]);
    	});

    	$this->assign("info", $admin);
    	$this->assign("roleList", $roleList);
    	return $this->fetch("save");
    }

    // 保存管理员信息
    public function save(){
    	if ($this->request->isAjax() == true) {
	    	$account = $this->request->param("account");
	    	$admin = Admin::getDefault(["account" => $account]);

	    	$validate_scene = "common.admin_edit";
	    	if ( !isset($admin->id) || empty($admin->id) ) {
	    		$validate_scene = "common.admin_add";
	    		$admin->account = $account;

	    		$passwords = Admin::password();
	    		$admin->encrypt = $passwords["encrypt"];
	    		$admin->password = $passwords["password"];
	    	}

	    	// 默认超级管理员不允许修改所属角色
	    	if ( !isset($admin->is_admin) || $admin->is_admin == 0) {
	    		$admin->role_id = $this->request->param("role_id");
	    	}

	    	$admin->username = $this->request->param("username");
	    	$admin->description = $this->request->param("description");
	    	$admin->dateline = date("Y-m-d H:i:s");
	    	$admin->disabled = 0;

	    	$r = $this->validateData($admin, $validate_scene);
	    	if ($r["res"] == 1) {
	    		$r = $admin->saveData();
	    	}

	    	return $r;
	    }
    }

    // 管理员删除
    public function remove(){
    	if ($this->request->isAjax() == true) {
	    	$id = $this->request->param("id");

	    	$r = Admin::getOne($id);
	    	if ($r["res"] == 1) {
	    		$admin = $r["data"];

	    		if ($admin["disabled"] == 1) {
	    			$r = rt("管理员已被删除", 0);
	    		} else if ($admin["is_admin"] == 1) {
	    			$r = rt("该管理员不能被删除", 0);
	    		} else {
	    			$admin->disabled = 1;
	    			$r = $admin->saveData();
	    		}
	    	}

	    	return $r;
    	}
    }

    // 重置密码
    public function initPassword(){
    	if ($this->request->isAjax() == true) {
	    	$id = $this->request->param("id");

	    	$r = Admin::getOne($id);
	    	if ($r["res"] == 1) {
	    		$admin = $r["data"];

	    		$passwords = Admin::password();
	    		$admin->encrypt = $passwords["encrypt"];
	    		$admin->password = $passwords["password"];
	    		$admin->dateline = date("Y-m-d H:i:s");

    			$r = $admin->saveData();
	    	}

	    	return $r;
	    }
    }

    // 管理员角色列表
    public function role(){

    	if ($this->request->isAjax() == true) {
    		$list = AdminRole::all(function($query) {
	    		$query->where(["disabled" => 0]);
                $query->order(["date" => "desc"]);
	    	});

	    	if ($list) {
				$list = collection($list)->toArray();
			}

            foreach ($list as $key => $value) {
                if ($value["is_admin"] == 1) {
                    $list[$key]["priv_desc"] = "全部";
                } else {
                    $privilege = explode(",", $value["privilege"]);
                    $privilege = array_filter(array_unique($privilege));
                    $list[$key]["priv_desc"] = count($privilege) . "项";
                }
            }

			return self::rt("CUCCESS", 1, $list);
		}

    	return $this->fetch();
    }

    // 管理员角色编辑
    public function roleSave(){
        if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $name = $this->request->param("name");
            $description = $this->request->param("description");

            $adminRole = AdminRole::getDefault($id);
            $adminRole->name = $name;
            $adminRole->description = $description;
            $adminRole->dateline = zdate_date();

            $r = $this->validateData($adminRole, "common.base_name");

            if ($r["res"] == 1) {
                $r = $adminRole->saveData();
            }

            return $r;
        }
    }

    // 管理员角色删除
    public function roleRemove(){
    	if ($this->request->isAjax() == true) {
            $id = $this->request->param("id");
            $r = AdminRole::getOne($id);
            if ($r["res"] == 1) {
                $adminRole = $r["data"];

            	if ($adminRole["disabled"] == 1) {
    				$r = rt("角色已被删除", 0);
	    		} else if ($adminRole["is_admin"] == 1) {
	    			$r = rt("该管理员角色不能被删除", 0);
	    		} else {
	    			$adminCount = Admin::where([
	                    "disabled" => 0, "role_id" => $id
	                ])->count("id");

	                if ($adminCount > 0) {
	                    $r = rt("该角色已关联管理员不能删除", 0);
	                }
	    		}
            }

            if ($r["res"] == 1) {
                $adminRole->disabled = 1;
                $adminRole->dateline = zdate_date();
                $r = $adminRole->saveData();
            }

            return $r;
        }
    }

    // 管理员角色权限配置
    public function rolePrivilege(){
    	$role_id = $this->request->param("role_id");
        $adminRole = AdminRole::get($role_id);

        if ( empty($adminRole) || $adminRole->disabled == 1 ) {
    		return $this->_error("Not Found", "管理员角色不存在或已删除");
    	} else if ($adminRole->is_admin == 1) {
    		return $this->_error("Error", "该管理员角色不允许修改权限配置");
    	}

    	$rolePrivilege = explode(",", $adminRole->privilege);
    	$rolePrivilege = array_filter(array_unique($rolePrivilege));

    	$privilegeList = Privilege::all(function($query){
    		$query->where(["disabled" => 0])->order("sort asc");
    		$query->field(["id", "name", "module_name", "controller_name", "route_name"]);
    	});

    	$privilegeGroupList = [];
    	foreach ($privilegeList as $key => $privilege) {
    		$privilege["is_checked"] = 0;
    		if (in_array($privilege["id"], $rolePrivilege)) {
    			$privilege["is_checked"] = 1;
    		}

    		$module_name = strtolower($privilege["module_name"]);
    		$controller_name = strtolower($privilege["controller_name"]);
    		$group = $module_name . "_" . $controller_name;

    		if (!isset($privilegeGroupList[$group])) {
    			$privilegeGroupList[$group] = array(
    				"name" => Privilege::$groupList[$group],
    				"code" => strtoupper($controller_name),
    				"privilegeList" => []
    			);
    		}

    		$privilegeGroupList[$group]["privilegeList"][] = $privilege->toArray();
    	}

    	$this->assign("adminRole", $adminRole);
    	$this->assign("privilegeGroupList", array_values($privilegeGroupList));
    	return $this->fetch();
    }

    // 管理员角色保存权限配置
    public function roleEditPrivilege(){
    	if ($this->request->isAjax() == true) {
	    	$role_id = $this->request->param("role_id");
	    	$privileges = $this->request->param("privileges/a", []);

	        $r = AdminRole::getOne($role_id);

	        if ($r["res"] == 1) {
	        	$adminRole = $r["data"];
	        	if ($adminRole->is_admin == 1) {
	        		$r = rt("该管理员角色不允许修改权限配置", 0);
	        	} else if ($adminRole->disabled == 1) {
	        		$r = rt("该管理员角色不存在或已删除", 0);
	        	}
	        }

	        if ($r["res"] == 1) {
	        	$privileges = array_filter(array_unique($privileges));
	        	$role_privilege = implode(",", $privileges);

	        	$adminRole->privilege = $role_privilege;
	        	$adminRole->dateline = zdate_date();

	        	$r = $adminRole->saveData();
	        }

	        return $r;
    	}
    }

    // 基础权限
    public function privilege(){
        if ($this->request->isAjax() == true) {
            $privilegeList = Privilege::all(function($query){
                $query->where(["disabled" => 0])->order("sort asc");
                $query->field(["id", "name", "module_name", "controller_name", "action_name", "route_name", "sort"]);
            });

            $index = 1;
            $privilegeTree = [];
            foreach ($privilegeList as $key => $privilege) {
                $module_name = strtolower($privilege["module_name"]);
                $controller_name = strtolower($privilege["controller_name"]);
                $group = $module_name . "_" . $controller_name;

                if (!isset($privilegeTree[$group])) {
                    $privilegeTree[$group] = array(
                        "id" => 0,
                        "treeId" => $index++,
                        "parentId" => null,
                        "name" => Privilege::$groupList[$group],
                        "module_name" => $module_name,
                        "controller_name" => $controller_name
                    );
                }

                $privilege = $privilege->toArray();
                $privilege["treeId"] = $index++;
                $privilege["parentId"] = $privilegeTree[$group]["treeId"];

                $privilegeTree[] = $privilege;
            }

            $privilegeTree = array_values($privilegeTree);

            return rt("success", 1, $privilegeTree);
        }

       return $this->fetch();
    }
}