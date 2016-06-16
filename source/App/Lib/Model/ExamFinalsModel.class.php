<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3
 * Time: 12:36
 */

/**
 * 期末考试管理模型
 *
 * 修改TESTEXAM
 *
 * Class ExamFinalsModel
 */
class ExamFinalsModel extends CommonModel {

    /**
     * 获取某学年学期的考试批次列表
     * @param $year
     * @param $term
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function listTestBatchByYearTerm($year,$term,$offset=null,$limit=null){
        $fields = '[YEAR] as year,RECNO as recno,FLAG as flag,TERM as term,CONVERT(VARCHAR(20),TESTTIME,20) as testtime';
        $where = '[YEAR] like :year and TERM like :term';
        $csql = $this->makeCountSql('TESTBATCH',array(
            'where'     => $where,
        ));
        $ssql = $this->makeSql('TESTBATCH',array(
            'fields'    => $fields,
            'where'     => $where,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
        );
        return $this->getTableList($csql,$ssql,$bind);
    }

    public function deleteTestRoomByRecno($recno){
        $sql = 'delete from testroom WHERE  recno = '.intval($recno);
        $rst = $this->doneExecute($this->sqlExecute($sql));
//        mist($rst,$sql);
        return $rst;
    }

    /**
     * 自动创建考试批次，将在之前的结果上递增
     * @param $year
     * @param $term
     * @return int|string
     */
    public function createTestBatchAutoaticly($year,$term){
        $sql = 'insert into TESTBATCH(FLAG,[YEAR],TERM)
select
case WHEN  max(FLAG) >= 0 THEN max(FLAG)+1
    WHEN   max(FLAG) is NULL THEN 0
END as FLAG ,:year  as  YEAR,:term as TERM from TESTBATCH';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 自动为某批次添加场次
     * 注意：添加批次可能会失败，需要修改表的组合索引
     * SQL：
CREATE INDEX [flag] ON [dbo].[TESTBATCH]
([FLAG] ASC, [batch] ASC)
WITH (DROP_EXISTING = ON)
ON [PRIMARY]
GO
     * @param $year
     * @param $term
     * @param int $flag 批次
     * @return int|string
     */
    public function createTestBatchPiciAutoaticly($year,$term,$flag){
        $sql = '
insert into TESTBATCH(FLAG,[YEAR],TERM,batch)
 SELECT dbo.getone(flag) as flag,year,term,MAX(batch) as maxbatch from TESTBATCH  GROUP BY [YEAR],TERM,FLAG HAVING YEAR = :year and term = :term and FLAG = :flag;';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':flag' => $flag,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 罗列考试批次场次数据
     * @return array|string
     */
    public function listTestBatch(){
        $sql = 'select [year],term,RECNO as recno,FLAG as flag ,batch,TESTTIME as testtime FROM TESTBATCH ORDER BY FLAG,batch';
        return $this->doneQuery($this->sqlQuery($sql));
    }

    /**
     * 根据ID号修改考试批次和考试时间
     * @param $batch
     * @param $time
     * @param $recno
     * @return int|string
     */
    public function updateTestBatchByRecno($batch,$time,$recno){
        $sql = 'UPDATE TESTBATCH SET batch=:batch,TESTTIME = :time WHERE RECNO = :recno';
        $bind = array(
            ':batch'    => $batch,
            ':time' => $time,
            ':recno'=> $recno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    public function initFinalsExam($year,$term){
        $this->startTrans();
        //删除排考课程
        $sql = 'delete from testcourse';
        $rst = $this->doneExecute($this->sqlExecute($sql));
        if(is_string($rst)){
            return "清空排考课程失败!";
        }
        //重新插入排考课程
        $sql = '
insert into TestCourse(CourseNo)
select RTRIM(S.COURSENO)+RTRIM(S.[GROUP]) AS kh,C.COURSENAME,0,0,NULL,RTRIM(S.COURSENO)+RTRIM(S.[GROUP])
from SCHEDULEPLAN AS S
INNER JOIN COURSES AS C ON S.COURSENO=C.COURSENO
WHERE YEAR=:YEAR AND TERM=:TERM AND EXAM=1';

    }

    /**
     * 更新课程考试批次
     * @param $coursegroup
     * @param $batch
     * @return int|string
     */
    public function updateCourseBatch($coursegroup,$batch){
        $sql = 'update testcourse set batch=:batch where courseno=:coursegroup;';
        $bind = array(
            ':batch'    => $batch,
            ':coursegroup'  => $coursegroup,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 罗列考场列表
     * @param $batch
     * @param $flag
     * @param string $roomno
     * @param $offset
     * @param $limit
     * @return array|string
     */
    public function listTestRooms($batch,$flag,$roomno='%',$offset,$limit){
        $fields = '
trm.roomno,
RTRIM(crm.JSN) as roomname,
crm.TESTERS as testers,
trm.batch,
trm.flag,
trm.recno';
        $join = 'INNER JOIN CLASSROOMS crm on crm.ROOMNO = trm.ROOMNO';
        $where = 'trm.ROOMNO like :roomno and CAST(trm.batch as VARCHAR) like :batch and CAST(trm.flag as VARCHAR) like :flag';
        $csql =$this->makeCountSql('TESTROOM trm',array(
            'join'  => $join,
            'where' => $where,
        ));
        $ssql = $this->makeSql('TESTROOM trm',array(
            'fields'=> $fields,
            'join'  => $join,
            'where' => $where,
        ),$offset,$limit);
        $bind = array(
            ':roomno'   => $roomno,
            ':batch'    => $batch,
            ':flag'     => $flag,
        );
        $rst = $this->getTableList($csql,$ssql,$bind);
//        mist($rst,$csql,$ssql,$bind);
        return $rst;
    }

    public function listCourseBatches($year,$term,$classno,$batch,$offset=null,$limit=null){
        $fields = "
sp.courseno+sp.[group] AS coursegroup,
RTRIM(courses.coursename) as coursename,
sp.recno as sprecno,
sp.exam,
examoptions.value as examtype,
schools.name school,
rtrim(classes.classname)+'/' as classname,
rtrim(classes.classno) as classno,
sp.ATTENDENTS attendents,
testcourse.batch";
        $join = '
inner join scheduleplan sp on testcourse.courseno=rtrim(sp.courseno)+rtrim(sp.[group])
inner join courses on courses.courseno=sp.courseno
inner join courseplan cp on cp.courseno=sp.courseno and cp.year=sp.year and cp.term=sp.term and cp.[group]=sp.[group]
inner join examoptions on  examoptions.name=cp.examtype
inner join schools on courses.school=schools.school
inner join classes on classes.classno=cp.classno';
        $where = '
sp.year=:year and
sp.term=:term and
classes.classno like :classno
and testcourse.batch like :batch';
        $csql = $this->makeCountSql('testcourse',array(
            'join'      => $join,
            'where'     => $where,
        ));
        $ssql = $this->makeSql('testcourse',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':classno' => $classno,
            ':batch' => $batch,
        );
        return $this->getTableList($csql,$ssql,$bind);
    }

    /**
     * 期末考试管理 地点设置 列表数据源
     *
     * 改编自
     * 'select':'exam/Two_one_select.SQL','count':'exam/Two_one_count.SQL'
     * 修改，舍去班级的获取
     *
     * @param $year
     * @param $term
     * @param string $school
     * @param string $coursegroup
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function listCoursesForArrange($year,$term,$school='%',$coursegroup='%',$offset=null,$limit=null){
        $fields = "
tp.COURSeNO as coursegroup,
dbo.getone(RTRIM(COURSES.COURSENAME)) as coursename,
dbo.getone(tp.ATTENDENTS) as attendents, -- 引述scheduleplan的对应字段
dbo.getone(RTRIM(tp.ROOMNO1)) as ROOMNO1,
dbo.getone(RTRIM(tp.ROOMNO2)) as ROOMNO2,
dbo.getone(RTRIM(tp.ROOMNO3)) as ROOMNO3,
dbo.getone(tp.seats1) as seats1,
dbo.getone(tp.seats2) as seats2,
dbo.getone(tp.seats3) as seats3,
dbo.getone(tp.FLAG) as batch,
dbo.getone(tp.cc) as flag,
dbo.getone(RTRIM('['+RTRIM(CAST(tp.FLAG as CHAR(2)))+'-'+RTRIM(CAST(tp.cc as CHAR(2)))+']'+tb.TESTTIME)) as testtime,-- 保留2位的长度
dbo.getone(ISNULL(RTRIM((RTRIM(tp.监考教师1)+ISNULL(TEACHER1.NAME,' ')) +','+RTRIM((RTRIM(tp.监考教师2)+ISNULL(TEACHER2.NAME,' '))) +','+ RTRIM((RTRIM(tp.监考教师3)+ISNULL(TEACHER3.NAME,' ')))),'')) AS ST1,
dbo.getone(ISNULL(RTRIM((RTRIM(tp.监考教师4)+ISNULL(TEACHER4.NAME,' ')) +','+ RTRIM((RTRIM(tp.监考教师5)+ISNULL(TEACHER5.NAME,' ')))+','+ RTRIM((RTRIM(tp.监考教师6)+ISNULL(TEACHER6.NAME,' ')))),'')) AS ST2,
dbo.getone(ISNULL(RTRIM((RTRIM(tp.监考教师7)+ISNULL(TEACHER7.NAME,' ')) +','+ RTRIM((RTRIM(tp.监考教师8)+ISNULL(TEACHER8.NAME,' '))) +','+RTRIM((RTRIM(tp.监考教师9)+ISNULL(TEACHER9.NAME,' ')))),'')) AS ST3,
dbo.getone(ISNULL(RTRIM(tp.监考教师1)+ISNULL(TEACHER1.NAME,' '),'')) T1,
dbo.getone(ISNULL(RTRIM(tp.监考教师2)+ISNULL(TEACHER2.NAME,' '),'')) T2,
dbo.getone(ISNULL(RTRIM(tp.监考教师3)+ISNULL(TEACHER3.NAME,' '),'')) T3,
dbo.getone(ISNULL(RTRIM(tp.监考教师4)+ISNULL(TEACHER4.NAME,' '),'')) T4,
dbo.getone(ISNULL(RTRIM(tp.监考教师5)+ISNULL(TEACHER5.NAME,' '),'')) T5,
dbo.getone(ISNULL(RTRIM(tp.监考教师6)+ISNULL(TEACHER6.NAME,' '),'')) T6,
dbo.getone(ISNULL(RTRIM(tp.监考教师7)+ISNULL(TEACHER7.NAME,' '),'')) T7,
dbo.getone(ISNULL(RTRIM(tp.监考教师8)+ISNULL(TEACHER8.NAME,' '),'')) T8,
dbo.getone(ISNULL(RTRIM(tp.监考教师9)+ISNULL(TEACHER9.NAME,' '),'')) T9,
dbo.getone(tp.R15) as recno,
dbo.getone(RTRIM(tp.rem)) as rem,
dbo.GROUP_CONCAT_MERGE('['+cp.CLASSNO+']'+RTRIM(CLASSES.CLASSNAME),'/') AS classes ";
        $join = '
INNER JOIN COURSES on COURSES.COURSENO = LEFT(tp.COURSeNO, 7)
INNER JOIN TESTBATCH tb on tb.batch = tp.flag and tb.flag = tp.cc -- testplan 中flag为批次，cc为批次
INNER JOIN SCHEDULEPLAN sp on sp.recno = tp.r10
INNER JOIN COURSEPLAN cp on cp.year = sp.year and cp.term = sp.term and cp.courseno = sp.courseno and cp.[group] = sp.[group]
INNER JOIN CLASSES ON CLASSES.CLASSNO = cp.CLASSNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER1 ON tp.监考教师1=TEACHER1.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER2 ON tp.监考教师2=TEACHER2.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER3 ON tp.监考教师3=TEACHER3.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER4 ON tp.监考教师4=TEACHER4.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER5 ON tp.监考教师5=TEACHER5.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER6 ON tp.监考教师6=TEACHER6.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER7 ON tp.监考教师7=TEACHER7.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER8 ON tp.监考教师8=TEACHER8.TEACHERNO
LEFT OUTER JOIN (SELECT TEACHERNO,NAME FROM TEACHERS )AS TEACHER9 ON tp.监考教师9=TEACHER9.TEACHERNO  ';
        $where = '
WHERE tp.[year] = :year and tp.term = :term
and COURSES.SCHOOL LIKE :school -- 开课学院
and tp.COURSeNO like :coursegroup ';
        $group = 'tp.COURSeNO';
        $csql = $this->makeCountSql('TESTPLAN tp',array(
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ));
        $ssql = $this->makeSql('TESTPLAN tp',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
            'group'     => $group,
        ),$offset,$limit);
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':school'   => $school,
            ':coursegroup'  => $coursegroup,
        );

        $list = $this->getTableList2($csql,$ssql,$bind);
//        mist($list,$csql,$ssql,$bind);

        return $list;
    }

    /**
     * @param $recno
     * @param $roomfield
     * @param $roomname
     * @param $seatfield
     * @param $seatnum
     * @return int|string
     */
    public function updateTestPlanRoom($recno,$roomfield,$roomname,$seatfield,$seatnum){
        if(false === stripos($roomfield,'roomno') or false === stripos($seatfield,'seats')){
            return "错误的字段名称'{$roomfield}'、'{$seatfield}'!";
        }
        $sql = "update testplan set {$roomfield}=:roomname,{$seatfield}=:seatnum where R15 = :recno";
        $bind = array(
            ':roomname' => $roomname,
            ':seatnum'  => $seatnum,
            ':recno'    => $recno,
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 添加考场记录
     *
     * 以下三者为主键
     * @param int $batch
     * @param int $flag
     * @param string $roomno
     *
     * @param $seats
     * @param $building
     * @param $no
     * @param $roomname
     * @return int|string
     */
    public function createTestRoomRecord($batch,$flag,$roomno,$seats,$building,$no,$roomname){
        $insertSql = 'insert into TESTROOM(ROOMNO,KW,louNO,menpaiNO,ROOMNAME,status,batch,flag) values
            (:roomno,:seats,:building,:no,:roomname,1,:batch,:flag)';
        $bind = array(
            ':roomno'  => $roomno,
            ':seats'    => $seats,
            ':building' => $building,
            ':no'       => $no,
            ':roomname' => $roomname,
            ':batch'    => $batch,
            ':flag'     => $flag,
        );
        $rst = $this->doneExecute($this->sqlExecute($insertSql,$bind));

//        mist($rst,$insertSql,$bind);

        return $rst;
    }



}