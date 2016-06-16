<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/15
 * Time: 10:09
 */
class ClassesModel extends CommonModel {
    /**
     * 根据学年学期课号组号获取 对应的班级信息
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @return array|string
     */
    public function getClassesnameByCourseno($year,$term,$coursegroupno){
        $sql = "
select  distinct
dbo.GROUP_CONCAT(RTRIM(classes.classname),',') AS classname
from courseplan
inner join classes on classes.classno=courseplan.classno
where
COURSEPLAN.YEAR= :year
AND COURSEPLAN.TERM= :term
and courseplan.courseno+courseplan.[group]=:coursegroupno";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':coursegroupno'    => $coursegroupno,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

}