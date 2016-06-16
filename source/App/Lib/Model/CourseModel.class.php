<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 15:48
 */
class CourseModel extends CommonModel {
    /**
     * 获取课程的详细信息
     * @param $courseno
     * @return array|string
     */
    public function getCourseInfo($courseno){
        if(strlen(trim($courseno)) > 7){
            $courseno = substr($courseno,0,7);
        }
        $sql = '
select
courses.courseno,
courses.coursename,
schools.name as schoolname,
courses.school as schoolno
from courses
inner join schools on courses.school=schools.school
where courses.courseno= :courseno';
        $bind = array(
            ':courseno' => $courseno,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

}