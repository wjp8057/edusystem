<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/10
 * Time: 9:37
 */
class StatusModel extends CommonModel{

    /**
     * 获取学生注册历史记录
     * @param $studentno
     * @return array|string
     */
    public function getStudentRegisterHistory($studentno){
        $sql = '
SELECT REGDATA.YEAR,
REGDATA.TERM,
REGDATA.REGDATE,
REGDATA.REGCODE,
RTRIM(REGCODEOPTIONS.VALUE) AS REGVALUE
FROM REGDATA
LEFT OUTER JOIN REGCODEOPTIONS ON REGDATA.REGCODE=REGCODEOPTIONS.NAME
WHERE RTRIM(REGDATA.STUDENTNO)=:studentno ';
        $bind = array(':studentno'  => $studentno);
        return $this->doneQuery($this->sqlQuery($sql,$bind));
    }

    /**
     * 获取学生注册信息详细信息
     * @param $studentno
     * @return array|string
     */
    public function getStudentRegisterInfo($studentno){
        $sql = '
SELECT
STUDENTS.STUDENTNO        as studentno,
RTRIM(STUDENTS.NAME)      as studentname,
STUDENTS.SEX              as sexcode,
RTRIM(SEXCODE.NAME)       as sexname,
CONVERT(varchar(10),STUDENTS.ENTERDATE,20) as enterdate,
STUDENTS.YEARS            as years,
RTRIM(STUDENTS.CLASSNO)   as classno,
RTRIM(CLASSES.CLASSNAME)  as classname,
STUDENTS.TAKEN            as token,
STUDENTS.PASSED           as passed,
STUDENTS.POINTS           as points,
STUDENTS.REG              as reg,
STUDENTS.WARN             as warn,
STUDENTS.STATUS           as status ,
RTRIM(STATUSOPTIONS.VALUE)  as statusname,
RTRIM(STUDENTS.CONTACT)   as contact,
STUDENTS.GRADE            as grade,
STUDENTS.SCHOOL           as school,
RTRIM(SCHOOLS.NAME)       as schoolname,
RTRIM(CS.NAME)	 		  as classschool,
PERSONAL.MAJOR            as major,
PERSONAL.ID               as id,
PERSONAL.PARTY            as party,
PERSONAL.NATIONALITY      as nationality,
CONVERT(varchar(10),PERSONAL.BIRTHDAY,20) as birthday,
PERSONAL.PHOTO            as photo,
RTRIM(PERSONAL.CLASS)     as class  -- 对应的表示classcode
FROM STUDENTS
INNER JOIN PERSONAL ON PERSONAL.STUDENTNO=STUDENTS.STUDENTNO
LEFT OUTER JOIN CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO
LEFT OUTER JOIN SCHOOLS CS ON CLASSES.SCHOOL=CS.SCHOOL
LEFT OUTER JOIN SCHOOLS ON STUDENTS.SCHOOL=SCHOOLS.SCHOOL
LEFT OUTER JOIN STATUSOPTIONS ON STUDENTS.STATUS=STATUSOPTIONS.NAME
LEFT OUTER JOIN SEXCODE ON STUDENTS.SEX=SEXCODE.CODE
where RTRIM(STUDENTS.STUDENTNO) =  :studentno';
        $bind = array(':studentno'  => $studentno);
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

    /**
     * 获取学生信息
     * @param string $studentno
     * @param bool $onlycount 仅仅获取学生数量时候设置为true
     * @return array|int|string
     */
    public function getStudentInfo($studentno,$onlycount=false){
        return $this->getTable('STUDENTS',array(
            'studentno' => $studentno,
        ),$onlycount);
    }

    /**
     * 获取学生个人信息，通过学号或者准考证号
     * @param string $studentno
     * @param string $examno
     * @return array|string
     */
    public function getStudentPersonalInfo($studentno=null,$examno=null){
        $bind = array(
            ':no'   => $studentno,
        );
        $filter = ' RTRIM(personal.STUDENTNO) = :no ';
        if(!empty($examno)){
            $filter = ' RTRIM(personal.EXAMNO) = :no ';
            $bind[':no'] = $examno;
        }
        $sql = "
SELECT
    RTRIM(personal.STUDENTNO) 	as STUDENTNO,
    RTRIM(personal.NAME) 		as studentname,
    CONVERT(varchar(10),personal.BIRTHDAY,20) as BIRTHDAY,
    personal.NATIONALITY 		as NATIONALITY,
    personal.PARTY				as PARTY,
    RTRIM(personal.EXAMNO)		as EXAMNO,
    RTRIM(personal.CLASS)		as CLASS,
    personal.FEATURE,
    personal.PLANCLASS,
    personal.POSTCODE,
    personal.ADDRESS,
    personal.TEL,
    personal.MIDSCHOOL,
    personal.MAJOR,
    personal.YEARS,
    personal.SCHOOL,
    personal.SCORE,
    personal.REM,
    personal.ID,
    personal.PROVINCE,
    personal.BRANCH,
    CONVERT(varchar(10),personal.DAYOFENROLL,20) as enterdate,
    --查看，无法修改
    RTRIM(SEXCODE.NAME) 		as sexname,
    RTRIM(nationalitycode.NAME)	as nationalityname,
    RTRIM(partycode.NAME)		as partyname,
    RTRIM(classcode.NAME) 	    as classcodename,
    RTRIM(featurecode.NAME)     as featurename,
    RTRIM(planclasscode.NAME)	as planclasscodename,
    RTRIM(majorcode.NAME) 	    as majorcodename,
    RTRIM(schools.NAME) 		as schoolname,
    RTRIM(provincecode.NAME) 	as provincecodename,
    RTRIM(branchcode.NAME) 	    as branchcodename
FROM personal
LEFT OUTER JOIN nationalitycode ON personal.NATIONALITY = nationalitycode.CODE
LEFT OUTER JOIN partycode ON personal.PARTY = partycode.CODE
LEFT OUTER JOIN classcode ON personal.CLASS = classcode.CODE
LEFT OUTER JOIN featurecode ON personal.FEATURE = featurecode.CODE
LEFT OUTER JOIN planclasscode ON personal.PLANCLASS = planclasscode.CODE
LEFT OUTER JOIN majorcode ON personal.MAJOR = majorcode.CODE
LEFT OUTER JOIN schools ON personal.SCHOOL = schools.SCHOOL
LEFT OUTER JOIN provincecode ON personal.PROVINCE = provincecode.CODE
LEFT OUTER JOIN branchcode ON personal.BRANCH = branchcode.CODE
LEFT OUTER JOIN SEXCODE ON PERSONAL.SEX=SEXCODE.CODE
WHERE  {$filter} ";
//        mist($sql,$bind);
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

    /**
     * 修改学生某学年学期的注册状态
     * @param $studentno
     * @param $year
     * @param $term
     * @param $regcode
     * @return int|string
     */
    public function updateStudentRegdata($studentno,$year,$term,$regcode){
        return $this->updateRecords('REGDATA',array(
            'REGCODE'   => $regcode,
            'REGDATE'   => date('Y-m-d'),
        ),array(
            'STUDENTNO' => $studentno,
            'YEAR'  => array($year,true),
            'TERM'  => array($term,true),
        ));
    }
    /**
     * 创建学生某学年学期的注册状态
     * @param $studentno
     * @param $year
     * @param $term
     * @param $regcode
     * @return int|string
     */
    public function createStudentRegdata($studentno,$year,$term,$regcode){
        return $this->createRecord('REGDATA',array(
            'REGCODE'   => $regcode,
            'REGDATE'   => date('Y-m-d'),
            'STUDENTNO' => $studentno,
            'YEAR'  => array($year,true),
            'TERM'  => array($term,true),
        ));
    }
    /**
     * 获取学生某学年学期的数据
     * @param $studentno
     * @param $year
     * @param $term
     * @return array|int|string
     */
    public function getStudentRegdata($studentno,$year,$term){
        return $this->doneQuery($this->getTable('REGDATA',array(
            'STUDENTNO' => $studentno,
            'YEAR'  => array($year,true),
            'TERM'  => array($term,true),
        )),false);
    }

    /**
     * 修改学生 信息
     * @param $studentno
     * @param $fields
     * @return int|string
     */
    public function updateStudentInfo($studentno,$fields){
        return $this->updateRecords('STUDENTS',$fields,array(
            'STUDENTNO' => $studentno
        ));
    }

    /**
     * 修改学生个人信息
     * @param $studentno
     * @param $fields
     * @return int|string
     */
    public function updatePersonInfo($studentno,$fields){
        return $this->updateRecords('PERSONAL',$fields,array(
            'STUDENTNO' => $studentno
        ));
    }

    /**
     * 通过班级号获取学生的全部信息
     * @param $classno
     * @return array|int|string
     */
    public function getStudentListByClassno($classno){
        return $this->getTable('STUDENTS',array(
            'CLASSNO'   => $classno,
        ));
    }

}