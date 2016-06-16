<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/15
 * Time: 9:52
 */
class TeacherModel extends CommonModel{
    /**
     * 获取课程排课计划 的教师情况
     * 注意到 '008A00A2B' 2014年第2学期有多条教师记录，而原来的程序员只列出了第一条教室信息
     * 具体情况可能是排课计划中由于程序或认为导致的错误
     * 现在列出教室名称和教师号 例如：刘玲(122107),方黛春(122103),朱建国(121112),刘文霞(012415)
     *
     * 注：SchedulePlan 主键是 year term courseno group
     *
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @return string
     */
    public function getTeachersBySchedulePlan($year,$term,$coursegroupno){
        $sql = "
SELECT distinct
dbo.group_concat( RTRIM(teachers.NAME)+'('+RTRIM(teachers.teacherno)+')',',' ) as teachers
FROM SCHEDULEPLAN
INNER JOIN TEACHERPLAN ON SCHEDULEPLAN.RECNO=TEACHERPLAN.MAP
INNER JOIN TEACHERS  ON TEACHERPLAN.TEACHERNO=TEACHERS.TEACHERNO
INNER JOIN users on users.teacherno=teachers.teacherno
INNER JOIN TASKOPTIONS ON TEACHERPLAN.TASK=TASKOPTIONS.CODE
WHERE SCHEDULEPLAN.YEAR=:year
AND SCHEDULEPLAN.TERM=:term
AND SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=:coursegroupno
GROUP BY SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':coursegroupno'    => $coursegroupno,
        );
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind),false);
        if(is_string($rst) or !$rst){
            return '未知的教师';
        }else{
            return $rst['teachers'];
        }
    }
}