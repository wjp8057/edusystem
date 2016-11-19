<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 
// +----------------------------------------------------------------------
// | Created:2016/11/19 11:52
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class ViewGradeScore extends MyService {
    function getList($page = 1, $rows = 20, $year='',$month='',$studentno='%')
    {
        $result=['total'=>0,'rows'=>[]];
        $condition = null;
        if ($year != '') $condition['year'] = $year;
        if ($month != '') $condition['month'] = $month;
        if ($studentno!= '%') $condition['viewgradescore.studentno'] = array('like', $studentno); //todo 有点奇怪，要加视图前缀才可以。
        $data = $this->query->table('viewgradescore')
            ->page($page, $rows)
            ->field('typename,studentno,name,gradename,score,year,month')
            ->where($condition)->order('year,month,typename')->select();
        $count = $this->query->table('viewgradescore')->where($condition)->count();
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }
}