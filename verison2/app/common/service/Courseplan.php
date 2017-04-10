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
// | Created:2017/3/30 15:09
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

class CoursePlan extends MyService {

    function init($year,$term,$classno,$start){
        $row=0;
        $public=0;
        try {
            MyAccess::checkAccess('E');
            $bind=["year"=>$year,"term"=>$term,'classno'=>$classno];
            //删除原有记录
            $sql='delete from courseplan
                where year=:year and term=:term and classno like :classno';
           Db::execute($sql,$bind);
            $bind=["year"=>$year,"term"=>$term,'year2'=>$year,'term2'=>$term,'classno'=>$classno,'start'=>$start];
            //生成开课记录
            $sql="insert into courseplan(year,term,courseno,[group],attendents,weeks,school,coursetype,examtype,classno,date)
            select :year,:term,r12.courseno,dbo.fn_10to36((ROW_NUMBER() over(partition by r12.courseno order by classplan.classno)-1+:start)),classes.students,r12.weeks,
            courses.school,r12.coursetype,r12.examtype,classes.classno,getdate()
            from classplan
            inner join majorplan on majorplan.rowid=classplan.MAJORPLANID
            inner join r30 on r30.MAJORPLAN_ROWID=majorplan.rowid
            inner join r12 on r12.programno=r30.progno
            inner join classes on classes.classno=classplan.classno
            inner join courses on courses.courseno=r12.courseno
            where majorplan.year+r12.year-1=:year2 and r12.term=:term2 and courses.courseno not like 'A1%' and classes.classno like :classno";
            $row+=Db::execute($sql,$bind);
            if($classno!='%') {
                $bind = ["year" => $year, "term" => $term, 'year2' => $year, 'term2' => $term, 'quarter' => '%' . $term . '%'];
                $sql = "INSERT INTO COURSEPLAN
                  (SCHOOL, YEAR, TERM, COURSENO, [GROUP], WEEKS, ATTENDENTS,COURSETYPE, EXAMTYPE, CLASSNO)
                  SELECT SCHOOL, :year AS Expr1, :term AS Expr2, COURSENO, '00' AS Expr3,262143 AS Expr4, Limit, 'E' AS Expr5, 'E' AS Expr6, '000000' AS Expr7
                   FROM COURSES
                  WHERE (COURSENO LIKE '08%') AND (Quarter LIKE :quarter) AND (REM IS NULL or rem not like '%04%') AND
                  (NOT EXISTS
                      (SELECT *
                     FROM courseplan
                     WHERE year = :year2 AND term = :term2 AND courseplan.courseno = courses.courseno))";
                $public += Db::execute($sql, $bind);
            }

        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"成功生成！".$row."条一般开课记录".$public."条公选课记录","status"=>"1"];
    }

}