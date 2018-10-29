<?php

/**
 * 备忘录
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:48:51
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 17:39:31
 */

namespace app\applet\model;

class Tickler extends BaseModel{

	public function themeInfo(){
        return $this->hasOne("Theme", "id", "theme_id");
    }
    
}