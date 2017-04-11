<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 11:03
 */

namespace app\common\service;
use app\common\access\MyService;
use app\common\access\MyException;
use think\Exception;

/**教学评估存档
 * Class QualityFile
 * @package app\common\service
 */
class QualityFile extends MyService{
    /**
     * @param int $page
     * @param int $rows
     * @param string $teacherno
     * @return array|null
     * @throws \think\Exception
     */
    function getList($page=1,$rows=20,$teacherno='%',$year='',$term='',$courseno='%',$coursename='%',$teachername='%'){
           $result=null;
        $condition=null;
        if($teacherno!='%') $condition['teacherno']=array('like',$teacherno);
        if($courseno!='%') $condition['courseno']=array('like',$courseno);
        if($coursename!='%') $condition['coursename']=array('like',$coursename);
        if($teachername!='%') $condition['teacherno']=array('like',$teachername);
        if($year!='') $condition['year']=$year;
        if($term!='') $condition['term']=$term;
        $count= $this->query->table('qualityfile')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('qualityfile')->where($condition)->page($page,$rows)->order('year desc,term desc')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

}