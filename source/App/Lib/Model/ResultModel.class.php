<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 9:08
 */

/**
 * Class ResultModel 成绩管理模型
 */
class ResultModel extends CommonModel {

    public function getPersonalResultInfo($studentno){
        $sql = '';
    }

    /**
     * 获取期末考试课程信息
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @return array|string
     */
    public function getFinalsCourseInfo($year,$term,$coursegroupno){
        $sql = '
SELECT  DISTINCT
scores.COURSENO+scores.[GROUP] AS coursegroupno,
RTRIM(COURSES.COURSENAME) as coursename,
COURSES.SCHOOL as schoolno,
convert(varchar(10),scores.[DATE],20) as examtime,
RTRIM(SCHOOLS.NAME) AS schoolname,
isnull(scheduleplan.ps,0) as ps
FROM scores
left outer JOIN COURSES ON scores.COURSENO=COURSES.COURSENO
left outer JOIN SCHOOLS ON COURSES.SCHOOL=SCHOOLS.SCHOOL
left outer join  scheduleplan on scheduleplan.courseno+scheduleplan.[group]=scores.courseno+scores.[group]
	and scheduleplan.year=scores.year and scheduleplan.term=scores.term
WHERE scores.YEAR=:year
AND scores.TERM=:term
AND scores.COURSENO+scores.[GROUP]=:coursegroupno';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':coursegroupno'    => $coursegroupno,
        );
//        mist($sql,$bind);
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }


    /**
     * 获取补考成绩输入课程选择列表
     * @param $year
     * @param $term
     * @param $schoolno
     * @param $courseno
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getResitCourseTableList($year,$term,$schoolno,$courseno,$offset=null,$limit=null){
        $fields = '
MAKEUP.courseno as courseno,
dbo.getone(RTRIM(COURSES.COURSENAME)) as coursename,
SUM(ISNULL(MAKEUP.lock, 0)) as lock,
count(*) as num,
dbo.getone(SCHOOLS.SCHOOL) as schoolno,
dbo.getone(RTRIM(SCHOOLS.NAME)) as schoolname';
        $join = '
        INNER JOIN COURSES on COURSES.COURSENO = MAKEUP.courseno
LEFT OUTER JOIN SCHOOLS on COURSES.SCHOOL = SCHOOLS.SCHOOL';
        $where = '[year] = :year and term = :term and COURSES.SCHOOL like :schoolno and COURSES.COURSENO like :courseno';
        $group = 'MAKEUP.courseno';
        $order = 'MAKEUP.courseno';
        $csql = $this->makeCountSql('MAKEUP',array(
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
            'order'     => $order,
        ));
        $ssql = $this->makeSql('MAKEUP',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
            'order'     => $order,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':schoolno' => $schoolno,
            ':courseno' => $courseno
        );
        return $this->getTableList($csql,$ssql,$bind);
    }

    /**
     * 获取期末考成绩输入课程选择列表
     * @param $year
     * @param $term
     * @param $teacherno
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getFinalsCourseTableList($year,$term,$teacherno,$offset=null,$limit=null){
        $fields = "
RTRIM(COURSENOGROUP) AS coursenogroup,
dbo.getOne(RTRIM(COURSENAME)) AS coursename,
dbo.getOne(CREDITS) AS credits,
dbo.getOne(WEEKHOURS) AS weekhours,
dbo.getOne(WEEKEXPEHOURS) AS weekexpehours,
dbo.getOne(RTRIM(COURSETYPE)) AS coursetype,
dbo.getOne(RTRIM(EXAMTYPE)) AS examtype,
dbo.getOne(RTRIM(SCHOOLNAME)) AS schoolname,
dbo.getOne(RTRIM(CLASSNONAME)) AS classnoname,
dbo.GROUP_CONCAT_MERGE(RTRIM(TEACHERNONAME),',') AS teachernoname,
dbo.getOne(RTRIM(REM)) AS rem,
dbo.GROUP_CONCAT_MERGE(RTRIM(DAYNTIME),',') AS dayntime";
        $where = "
[YEAR] = :year
AND TERM = :term
and TEACHERNO = :teacherno";
        $group = 'COURSENOGROUP';
        $csql = $this->makeCountSql('VIEWSCHEDULE',array(
            'where' => $where,
            'group' => $group,
        ));
        $ssql = $this->makeSql('VIEWSCHEDULE',array(
            'fields'    => $fields,
            'where' => $where,
            'group' => $group,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':teacherno'    => $teacherno,
        );
        $rst = $this->getTableList($csql,$ssql,$bind);
        return $rst;
    }
    /**
     * 获取期末成绩输入界面的 学生列表数据
     *
     *
    $sql = "
        SELECT
        scores.studentno,
        students.name as studentname,
        ISNULL(RTRIM(SCORES.PS), '') as ps,
        ISNULL(RTRIM(SCORES.qm), '') as finalscore,
        case
        when [testscore] = '' then ISNULL(RTRIM(scores.EXAMSCORE), '')
        when [testscore] is null then ISNULL(RTRIM(scores.EXAMSCORE), '')
        else ISNULL(RTRIM(scores.testscore), '')
        end as finals,
        ISNULL(convert(varchar(10),scores.edate,20),'') as examdate,
        convert(varchar(10),scores.date,20) as  filldate
        FROM scores
        INNER JOIN students on students.studentno=scores.studentno
        WHERE YEAR= 2014
        AND TERM= 2
        AND scores.courseno+scores.[group] ='008A00A2G   '";
     * @param $year
     * @param $term
     * @param $courseno
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getFinalsStudentlist($year,$term,$courseno,$offset=null,$limit=null){
        $fields = '
scores.studentno as studentno,
rtrim(students.name) as studentname,
rtrim(ISNULL(qm, \'\')) as finalscore,
rtrim(examrem) as status, -- 状态
RECNO as recno,
convert(varchar(10),scores.EDATE,20) as examdate, -- 考试日期
convert(varchar(10),scores.DATE,20) as filldate, -- 填表日期
scores.lock';
        $join = 'INNER JOIN students on scores.studentno=students.studentno';
        $where = '
YEAR = :year
AND TERM= :term
AND COURSENO+[group] = :courseno';
        $order = 'SCORES.studentno';
        $csql = $this->makeCountSql('scores',array(
            'where' => $where,
            'join'  => $join,
        ));
        $ssql = $this->makeSql('scores',array(
            'fields'    => $fields,
            'where' => $where,
            'join'  => $join,
            'order' => $order,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':courseno' => $courseno,
        );
        return $this->getTableList($csql,$ssql,$bind);
    }



    /**
     * 获取补考成绩输入界面学生列表数据
     * @param $year
     * @param $term
     * @param $courseno
     * @param $offset
     * @param $limit
     * @return array|string
     */
    public function getResitStudentList($year,$term,$courseno,$offset=null,$limit=null){
        $fields = "
scores.studentno as studentno,
RTRIM(students.name) as studentname,
case
    when [testscore2] = '' then cast(scores.EXAMSCORE2 as char(5))
    when [testscore2] is null then cast(scores.EXAMSCORE2 as char(5))
	else RTRIM(scores.testscore2)
  end as resitscore,
SCORES.RECNO as scorerecno,
makeup.lock as makeuplock,
convert(varchar(10),makeup.date,20) as filldate,
convert(varchar(10),makeup.edate,20) as examdate";
        $join = "
inner join students on scores.studentno=students.studentno
inner join makeup on makeup.studentno=scores.studentno and makeup.courseno=scores.courseno
	and makeup.year=scores.year and makeup.term=scores.term";
        $where = "
scores.YEAR=:year
AND scores.TERM=:term
AND makeup.COURSENO=:courseno
AND SCORES.[GROUP] != 'BY'";
        $csql = $this->makeCountSql('SCORES',array(
            'join'      => $join,
            'where'     => $where,
        ));
        $ssql = $this->makeSql('SCORES',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':courseno' =>$courseno,
        );
        return $this->getTableList($csql,$ssql,$bind);
    }

    /**
     * 修改学生补考成绩
     * @param $examscore
     * @param $testscore
     * @param $recno
     * @return int|string
     */
    public function updateStudentResitResult($examscore,$testscore,$recno){
        $sql = 'update scores set examscore2=:examscore,testscore2=:testscore,date2=getdate() where scores.RECNO=:recno ; ';
        $bind = array(
            ':examscore'    => $examscore,
            ':testscore'    => $testscore,
            ':recno'        => $recno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }
    /**
     * 修改学生期末成绩
     * @param $examscore
     * @param $testscore
     * @param $edate
     * @param $recno
     * @param string $ps
     * @return int|string
     */
    public function updateStudentFinalsResultByRecno($examscore,$testscore,$edate,$recno,$ps=''){
        $sql = 'update scores set examscore=:examscore,testscore=:testscore,[date]=getdate(),edate=:edate,qm=:qm,lock=1,ps=:ps
                from scores where scores.RECNO=:recno and lock=0;';
        $qm = intval($examscore)? $examscore :trim($testscore);
        $testscore = in_array(trim($testscore),array('缺考','缓考','违纪','q','h','w'))?'':trim($testscore);
        $bind = array(
            ':examscore'    => $examscore,
            ':testscore'    => $testscore,
            ':edate'        => $edate,
            ':qm'           => $qm,
            ':ps'           => $ps,
            ':recno'        => $recno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 锁定学生的考试日期
     * @param $courseno
     * @param $year
     * @param $term
     * @param $studentno
     * @param string $edate 考试日期，可选
     * @return int|string
     */
    public function lockStudentResit($courseno,$year,$term,$studentno,$edate=null){
        $sqldate = isset($edate)?'edate=:edate,':'';
        $sql = "
update makeup
set lock=1, {$sqldate} [date]=getdate()
where courseno=:courseno and year=:year and term=:term AND STUDENTNO=:studentno";
        $bind = isset($edate)?array(':edate'    => $edate):array();
        $bind = array_merge($bind,array(
            ':courseno' => $courseno,
            ':year' => $year,
            ':term' => $term,
            ':studentno'    => $studentno,));
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 判断学生补考是否锁定
     * @param $courseno
     * @param $year
     * @param $term
     * @param $studentno
     * @return array|string
     */
    public function isStudentResitLocked($courseno,$year,$term,$studentno){
        $sql = '
select lock from makeup
where courseno=:courseno and year=:year and term=:term AND STUDENTNO=:studentno';
        $bind = array(
            ':courseno' => $courseno,
            ':year' => $year,
            ':term' => $term,
            ':studentno'    => $studentno,);
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind),false);
        if(is_string($rst) ){
            return $rst;
        }
        return $rst['lock'];
    }

    /**
     * 判断学生期末考试成绩输入是否锁定
     * @param $recno
     * @return string|bool
     */
    public function isStudentFinalsLockedByRecno($recno){
        $sql = 'SELECT top 1 LOCK from SCORES WHERE RECNO = :recno';
        $bind = array( ':recno' => $recno);
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind),false);
        if(is_string($rst) or !$rst){
            return "查询学生期末考试成绩是否锁定输入失败！{$rst}";
        }else{
            return $rst['LOCK'];
        }
    }
    /**
     * 解锁学生的期末考试成绩输入
     * @param $recno
     * @return int|string
     */
    public function unlockStudentFinalsByRecno($recno){
        return $this->lockStudentFinalsByRecno($recno,false);
    }

    /**
     * 锁定学生的期末考试成绩输入
     * @param $recno
     * @param bool $tolock
     * @return int|string
     */
    public function lockStudentFinalsByRecno($recno,$tolock=true){
        $tolock = $tolock?1:0;//值已经确定，无需banding
        $sql = "UPDATE SCORES SET LOCK = {$tolock} WHERE RECNO = :recno";
        $bind = array( ':recno' => $recno);
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     *  解锁 学生的期末考试成绩输入
     * @param $year
     * @param $term
     * @param $courseno
     * @return int|string
     */
    public function unlockStudentResitStatusByCourseno($year,$term,$courseno){
        $sql = 'update makeup set lock=0 where [year]=:year and term=:term and courseno = :courseno';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':courseno' => $courseno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }


    /**
     * 更具学年学期学院号获取还没输入成绩的课程
     * @param $year
     * @param $term
     * @param $school
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getCourseListWhichScoreInputness($year,$term,$school,$offset=null,$limit=null){
        $filter = (defined('SCHOOL_CODE') and ('nbcity' === SCHOOL_CODE))?"and scores.[group] not in ('BY','ZX')":'';
        $fields = '
scores.courseno + scores.[group] as coursengoupno,
RTRIM(COURSES.COURSENAME)  as coursename,
COUNT(DISTINCT STUDENTNO)  nessnum,
RTRIM(COURSEAPPROACHES.VALUE) AS approach,
RTRIM(EXAMOPTIONS.VALUE) AS examtype,
RTRIM(classes.classname) as classname';
        $join = '
INNER JOIN courseplan on courseplan.year=scores.year and courseplan.term=scores.term and courseplan.courseno=scores.courseno and courseplan.[group]=scores.[group]
INNER JOIN COURSES ON SCORES.COURSENO = COURSES.COURSENO
INNER JOIN EXAMOPTIONS ON COURSEPLAN.EXAMTYPE = EXAMOPTIONS.NAME
INNER JOIN COURSEAPPROACHES ON COURSEPLAN.COURSETYPE = COURSEAPPROACHES.NAME
INNER JOIN classes on classes.classno=courseplan.classno';
        $where = "
scores.year = :year
AND scores.term = :term
AND ((examscore IS NULL AND testscore IS NULL) OR TESTSCORE='缓考') --筛选条件，缓考和未输入的都将被列出的关键
And courseplan.SCHOOL like :school
{$filter}";
        $group = 'scores.courseno + scores.[group], coursename,COURSEAPPROACHES.VALUE,EXAMOPTIONS.VALUE,classes.classname';
        $csql = $this->makeCountSql('SCORES',array(
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ));
        $ssql = $this->makeSql('SCORES',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':school'   => $school,
        );
//        mist($csql,$ssql,$bind);
        return $this->getTableList2($csql,$ssql,$bind);
    }
    public function getCoursesWithOpenTableList($year,$term,$school,$offset=null,$limit=null){
        $filter = (defined('SCHOOL_CODE') and ('nbcity' === SCHOOL_CODE))?"and scores.[group] not in ('BY','ZX')":'';
        $fields = '
COURSEPLAN.COURSENO + COURSEPLAN.[GROUP] AS coursegroupno,
rtrim(COURSES.COURSENAME) AS coursename,
rtrim(COURSEAPPROACHES.[VALUE]) AS approach,
rtrim(EXAMOPTIONS.[VALUE]) AS examtype,
SUM(SCORES.lock) AS lock';
        $join = '
INNER JOIN COURSEAPPROACHES ON COURSEPLAN.COURSETYPE = COURSEAPPROACHES.NAME
INNER JOIN COURSES ON COURSES.COURSENO = COURSEPLAN.COURSENO
INNER JOIN EXAMOPTIONS ON EXAMOPTIONS.NAME = COURSEPLAN.EXAMTYPE
INNER JOIN SCORES ON SCORES.[YEAR] = COURSEPLAN.[YEAR] AND SCORES.TERM = COURSEPLAN.TERM AND COURSEPLAN.COURSENO = SCORES.COURSENO AND SCORES.[GROUP] = COURSEPLAN.[GROUP]';
        $where = "
scores.year= :year
and scores.term= :term
and courseplan.school= :school
{$filter}";
        $group = 'COURSEPLAN.COURSENO + COURSEPLAN.[GROUP],COURSES.COURSENAME, COURSEAPPROACHES.[VALUE],EXAMOPTIONS.[VALUE]';
        $csql = $this->makeCountSql('COURSEPLAN',array(
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ));
        $ssql = $this->makeSql('COURSEPLAN',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':school'   => $school,
        );
        $rst = $this->getTableList2($csql,$ssql,$bind);
//        mist($rst);
        return $rst;
    }

    /**
     * 修改期末成绩输入状态
     * @param $year
     * @param $term
     * @param $courseno
     * @return int|string
     */
    public function updateFinalsLackStatus($year,$term,$courseno){
        $sql = 'UPDATE SCORES SET LOCK=0 WHERE YEAR=:year AND TERM=:term AND COURSENO+[GROUP]=:courseno';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':courseno' => $courseno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 获取毕业重修 课程列表
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @param $school
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getRetakeCourseList($year,$term,$coursegroupno,$school,$offset=null,$limit=null){
        $fields = '
scores.courseno,
scores.[group],
scores.courseno+scores.[group] as coursegroupno,
rtrim(courses.coursename) as coursename,
count(scores.studentno) as num,
sum(isnull(lock,0)) as lock';
        $join = 'inner join courses on courses.courseno=scores.courseno';
        $where = "
scores.year=:year
and scores.term=:term
and courses.school=:school
and scores.courseno+scores.[group] like :courseno
and scores.[group]='BY' ";
        $group = 'scores.courseno,scores.[group],courses.coursename';
        $order = 'scores.courseno,scores.[group]';
        $csql = $this->makeCountSql('scores',array(
            'where'     => $where,
            'join'      => $join,
            'group'     => $group,
        ));
        $ssql = $this->makeSql('scores',array(
            'fields'    => $fields,
            'where'     => $where,
            'join'      => $join,
            'group'     => $group,
            'order'     => $order,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':school'   => $school,
            ':courseno' => $coursegroupno,
        );
        $rst = $this->getTableList2($csql,$ssql,$bind);
//        mist($rst,$csql,$ssql,$bind);
        return $rst;
    }

    /**
     * 免于体侧名单输入 学生列表数据
     * @param $year
     * @param $term
     * @param $studentname
     * @return array|string
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getAvoidList($year,$term,$studentname,$offset=null,$limit=null){
        $fields = "[Year] as year ,
RTRIM(Term) as term ,
studentno,
RTRIM([name]) as studentname,
RTRIM(classname) as classname ,
convert(varchar(10),[time],20) as applydate,
RTRIM(ISNULL(Reason, '')) as reason";
        $where = '
year = :year
and term = :term
and name like :studentname';
        $order = '[Year]';

        $csql = $this->makeCountSql('免于测试名单',array(
            'where'     => $where,
        ));
        $ssql = $this->makeSql('免于测试名单',array(
            'fields'    => $fields,
            'where'     => $where,
            'order'     => $order,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':studentname'  => $studentname,
        );
        $rst = $this->getTableList($csql,$ssql,$bind);
//        mist($rst,$csql,$ssql,$bind);
        return $rst;
    }

    /**
     * 免于体侧名单输入 增加记录
     * @param $fields
     * @return int|string
     */
    public function createAvoidRecord($fields){
        return $this->createRecord('免于测试名单',$fields);
    }


}