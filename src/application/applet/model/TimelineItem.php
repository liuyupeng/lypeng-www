<?php

/**
 * 时光轴模型
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-09-26 14:46:03
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-09-26 16:42:11
 */

namespace app\applet\model;

class TimelineItem extends BaseModel{

	public function themeInfo(){
        return $this->hasOne("Theme", "id", "theme_id");
    }
}