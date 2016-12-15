<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/11/26 19:20
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

//考试学生名单
class TestStudent extends MyService {
    //载入期末考试学生名单
    public static function  loadFinal($year,$term){
        $bind=array('year'=>$year,'term'=>$term);
        $sql="insert into teststudent(map,studentno)
            select testcourse.id,r32.studentno
            from testcourse inner join r32 on r32.courseno+r32.[group]=testcourse.courseno
            and r32.year=testcourse.year and r32.term=testcourse.term
            where testcourse.year=:year and testcourse.term=:term and testcourse.type='A' and testcourse.lock=0";
        return Db::execute($sql,$bind);
    }
    //载入学期初补考学生名单
    public static function  loadStart($year,$term){
        $bind=array('year'=>$year,'term'=>$term);
        $sql="insert into teststudent(map,studentno)
            select testcourse.id,makeup.studentno
            from testcourse inner join makeup on makeup.courseno=substring(testcourse.courseno,1,7)
            and makeup.year=testcourse.year and makeup.term=testcourse.term
            where testcourse.year=:year and testcourse.term=:term and testcourse.type='B' and testcourse.lock=0";
        return Db::execute($sql,$bind);
    }
    public static function  loadGraduate($year,$term){
        $bind=array('year'=>$year,'term'=>$term);
        $sql="insert into teststudent(map,studentno)
            select testcourse.id,scores.studentno
            from testcourse inner join scores on scores.courseno+scores.[group]=testcourse.courseno
            and scores.year=testcourse.year and scores.term=testcourse.term
            where testcourse.year=:year and testcourse.term=:term and testcourse.type='C' and testcourse.lock=0";
        return Db::execute($sql,$bind);
    }

    //清空名单
    public static function clear(){
        $sql="delete from teststudent";
        Db::execute($sql);
    }

}