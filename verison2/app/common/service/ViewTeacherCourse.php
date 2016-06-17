<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 14:26
 */

namespace app\common\service;

use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;

class ViewTeacherCourse extends  MyService {

    function getCourseList($page=1,$rows=20,$year='',$term='',$teacherno='')
    {
        if ($year == '' || $term == '' || $teacherno == '')
            throw new Exception('year term teacherno is empty', MyException::PARAM_NOT_CORRECT);

        $result = array();
        $condition = null;
        $condition['year'] = $year;
        $condition['term'] = $term;
        $condition['teacherno'] = $teacherno;
        $count = $this->query->table('viewteachercourse')->where($condition)->count();// 查询满足要求的总记录数
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->query->table('viewteachercourse')->where($condition)->page($page, $rows)->order('courseno')->select();
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }
}