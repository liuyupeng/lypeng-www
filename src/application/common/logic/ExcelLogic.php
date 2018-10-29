<?php

/**
 * @Author: lovenLiu
 * @Email:	lypeng9205@163.com
 * @Date:   2018-08-31 14:03:31
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2018-08-31 14:21:13
 */

namespace app\common\logic;

use think\Loader;

class ExcelLogic {

	public static function read(){


	}

	/**
	 * 把Excel内容转化成数组
	 * @author liuyupeng [lypeng9205@163.com]
	 * @version 2017-06-23T10:11:38+0800
	 */
	public static function excelToArray($file_path, $sheet = 0){
		Loader::import("PHPExcel.PHPExcel", EXTEND_PATH);
		Loader::import("PHPExcel.PHPExcel.IOFactory", EXTEND_PATH);
		Loader::import("PHPExcel.PHPExcel.Style.Alignment", EXTEND_PATH);
		Loader::import("PHPExcel.PHPExcel.Reader.Excel2007", EXTEND_PATH);

	    $r = rt("文件检测成功", 1);
	    if(empty($file_path) || !file_exists($file_path)){
	        $r = rt("Excel 读取错误", 0);
	    }

	    if($r["res"] == 1){
	        $PHPReader = new \PHPExcel_Reader_Excel2007();        // 建立reader对象
	        
	        if(!$PHPReader->canRead($file_path)){
	            $PHPReader = new \PHPExcel_Reader_Excel5();
	            if(!$PHPReader->canRead($file_path)){
	                $r = rt("Excel 读取错误", 0);
	            }
	        }
	    }

	    if($r["res"] == 1){
	        $PHPExcel = $PHPReader->load($file_path);           // 建立excel对象
	        $currentSheet = $PHPExcel->getSheet($sheet);        // 读取excel文件中的指定工作表

	        $allColumn = $currentSheet->getHighestColumn();       // 取得最大的列号
	        $allRow = $currentSheet->getHighestRow();             // 取得一共有多少行

	        $dataExcel = array();
	        // 循环读取每个单元格的内容。注意行从1开始，列从A开始
	        for($rowIndex = 1; $rowIndex <= $allRow; $rowIndex ++){        
	            for($colIndex = "A"; $colIndex <= $allColumn; $colIndex ++){
	                $location = $colIndex.$rowIndex;

                    // $cell = $currentSheet->getCell($location)->getValue();
	                $cell = $currentSheet->getCell($location)->getCalculatedValue();
	                
	                if($cell instanceof PHPExcel_RichText){ // 富文本转换字符串
	                    $cell = $cell->__toString();
	                }
	                
	                $dataExcel[$rowIndex][$colIndex] = $cell;
	            }
	        }

	        $r = rt("文件内容获取成功", 1);
	        $r["allRow"] = $allRow;
	        $r["allColumn"] = $allColumn;
	        $r["data"] = $dataExcel;
	    }

	    return $r;
	}
}
