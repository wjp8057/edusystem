<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/28
 * Time: 13:56
 */

/**
 * Class MappingModel
 * 处理映射表的模型
 */
class MappingModel extends CommonModel{

    /**
     * 获取时间段映射
     * @param string $code
     * @return array|int|string
     */
    public function getTimeSectionsByCode($code){
        return $this->getTable('TIMESECTIONS',array(
            'NAME'  => array($code,true,'like'),
        ));
    }

    /**
     * 获取单双周映射
     * @param string $code
     * @return array|int|string
     */
    public function getOewOptionByCode($code){
        return $this->getTable('OEWOPTIONS',array(
            'CODE'  => array($code,true,'like'),
        ));
    }

    /**
     * 节次映射
     * @return array|string
     */
    public function getTimeSectorsMap(){
        $rst = $this->doneQuery($this->sqlQuery('select * from TIMESECTORS'));
        if(is_string($rst)){
            return $rst;
        }
        $map = array();
        foreach($rst as $val){
            $map[trim($val['NAME'])] = $val;
        }
        return $map;
    }

}