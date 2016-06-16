<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3
 * Time: 14:15
 */

class ExamArrangeModel extends CommonModel {


    /**
     * 获取考试学生列表
     * @param int $batchno -1表示全部获取，0表示未设置批次的，正整数表示对应的批次
     * @return array|string
     */
    public function getTestStudentlistByBatch($batchno=-1){
        if($batchno < 0){//获取全部
            $sql = 'select ts.* from TestStudent ts';
        }else{
            $sql = 'select ts.* from TestStudent ts INNER JOIN TESTCOURSE tc ON ts.CourseNo=tc.CourseNo WHERE tc.batch= '.intval($batchno);
        }
        return $this->doneQuery($this->sqlQuery($sql));
    }

    /**
     * 获取考试的课程列表
     * @param int $batchno -1表示全部获取，0表示未设置批次的，正整数表示对应的批次
     * @return array|string
     */
    public function getTestCourselistByBatch($batchno=-1){
        $sql = 'select * from TestCourse '.($batchno<0?'':'where batch= '.intval($batchno));
        return $this->doneQuery($this->sqlQuery($sql));
    }

    /**
     * 获得所有的批次数组
     * @return array|string
     */
    public function getBatchList(){
        $sql = 'SELECT DISTINCT batch from TESTCOURSE WHERE batch > 0 ORDER BY batch';
        return $this->doneQuery($this->sqlQuery($sql));
    }

    /**
     * 获取某批次的所有场次信息
     * @param int $batchno -1表示全部获取，0表示未设置批次的，正整数表示对应的批次
     * @param bool $getsum 是否返回批次数量，默认为否
     * @return array|string
     */
    public function getBatchFlags($batchno=-1,$getsum=false){
        $sql = 'select recno,flag,testtime,year,term,batch from TESTBATCH '.($batchno<0?'':' where batch='.intval($batchno));
        $rst = $this->doneQuery($this->sqlQuery($sql));
        return $getsum?count($rst):$rst;
    }

    /**
     * 获取某场次的最大批次
     * @param int $batchno -1表示全部获取，0表示未设置批次的，正整数表示对应的批次
     * @return string|int 获取错误信息或者该场次的最大批次
     */
    public function getBatchMaxFlag($batchno=-1){
        if($batchno >= 0){
            $filter = ' WHERE c.batch = '.intval($batchno);
        }else{
            $filter = '';
        }
        $sql = "
select top 1 COUNT(*) as cnt from (
	select studentno,ts.courseno,tc.batch from TESTSTUDENT ts
	INNER JOIN TESTCOURSE tc on tc.courseno = ts.courseno
	group by Studentno,ts.courseno,tc.batch
) c {$filter}
group by c.Studentno ";
        $rst = $this->doneQuery($this->sqlQuery($sql));
        if(is_string($rst) or !$rst){

            return "查询失败！{$sql}{$rst}";
        }else{
            return intval($rst[0]['cnt']);
        }
    }


    public function getBatchCourseStudentMaxSum($batchno=-1){
        if($batchno >= 0){
            $filter = 'WHERE tc.batch = '.intval($batchno);
        }else{
            $filter = '';
        }
        $sql = "
SELECT TOP 1 COUNT(*) as maxnum from TESTSTUDENT ts
INNER JOIN TESTCOURSE tc on ts.courseno = tc.courseno
{$filter}
GROUP BY ts.courseno ORDER BY maxnum DESC";
        $rst = $this->doneQuery($this->sqlQuery($sql));

        if(is_string($rst) or !$rst){
            return "查询失败！{$rst}";
        }else{
            return intval($rst[0]['maxnum']);
        }
    }

    /**
     * 获取某批次所有参加考试的课程
     * @param int $batchno 0为未安排，-1表示获取全部，正整数表示对应的批次
     * @return array|string
     */
    public function getTestCourseListForArrange($batchno=-1){
        //批次信息
        if($batchno >= 0){
            $filter = ' and tc.batch =  '.intval($batchno);
        }else{
            $filter = '';
        }
        $sql = "
select
RTRIM(coursename) as coursename,
b.*
from courses
inner join (
	SELECT
	tc.courseno2 as coursegroup,-- 排的时候当作一个课号，即等价课号
	COUNT(DISTINCT ts.studentno) as attendents, -- 人数算作总人数
	MIN(tc.flag) as minflag   -- 这些课程中最小的场次，什么用？？？
	from TESTCOURSE tc
	INNER JOIN TESTSTUDENT ts on (tc.courseno = ts.courseno or tc.courseno2 = ts.courseno )
    WHERE tc.lock = 0 {$filter}
	GROUP BY tc.courseno2
) as b on COURSES.courseno = SUBSTRING(b.coursegroup,1,7) order by b.minflag";

//        mist($sql);
        return $this->doneQuery($this->sqlQuery($sql));
    }

    public function resetTestCourseArrangment($batch=-1){
        if($batch < 0){
            $usql = 'update TESTCOURSE SET Lock = 0,Flag = 0';
        }else{
            $usql = 'update TESTCOURSE SET Lock = 0,Flag = 0 WHERE batch = '.intval($batch);
        }
        return $this->doneExecute($this->sqlExecute($usql));
    }



    /**
     * 检测考生中是否已经存在 要存在这场次批次的考试
     * @param string $coursegroup 课号组号
     * @param int $flag 场次
     * @param int $batch 批次
     * @return bool|string
     */
    public function isTestCourseHasConflictByFlagAndBatch($coursegroup,$flag,$batch){
        $sql = '
select
	count(*) as cnt
from teststudent tb1
where courseno = :coursegroup and exists (
	select 1 from teststudent tst
	INNER JOIN TESTCOURSE tc on (tc.CourseNo = tst.courseno or tc.CourseNo2 = tst.courseno)
	where tst.studentno=tb1.studentno and  tc.batch = :batch and tc.Flag = :flag)';
        $bind = array(
            ':coursegroup'  => $coursegroup,
            ':batch'        => $batch,
            ':flag'         => $flag,
        );
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind),false);
        if(is_string($rst) or !$rst){
            return "检测排考课程是否存在冲突失败!{$rst}";
        }
        return $rst['cnt'];
    }

    /**
     * 根据考试批次和场次获取批次详细信息
     * @param int $flag 场次
     * @param int $batch 批次
     * @return array|string
     */
    public function getTestBatchByFlagAndBatch($flag,$batch){
        $sql = 'select * from Testbatch where flag=:flag and batch=:batch';
        $bind = array(
            ':flag' => $flag,
            ':batch'=> $batch,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

    /**
     * 创建考试批次场次
     * @param $year
     * @param $term
     * @param $flag
     * @param $batch
     * @return bool|string false时表示未创建或创建失败，可能记录已经存在的原因
     */
    public function createTestBatchIfNotExist($year,$term,$batch,$flag){
        $item = $this->getTestBatchByFlagAndBatch($flag,$batch);
        if(is_string($item)){
            return $item;
        }elseif(!empty($item)){
            //已经存在该批次场次了
            return false;
        }

        $sql = 'INSERT INTO TESTBATCH ([FLAG],[YEAR], [TERM], [batch]) VALUES (:flag,:year,:term,:batch);';
        $bind = array(
            ':flag' => $flag,
            ':year' => $year,
            ':term' => $term,
            ':batch'=> $batch,
        );
        $rst = $this->doneExecute($this->sqlExecute($sql,$bind));
        if(is_string($rst)){
            return $rst;
        }
        return $rst?true:false;
    }

    /**
     * ???
     * @param $flag
     * @param $coursegroup
     * @param $batch
     * @return int|string
     */
    public function updateArrange($flag,$coursegroup,$batch){
        $this->startTrans();

        //设置场次的同时锁定（标记为已经排课）
        $cusql = 'update testcourse set flag=:flag,lock=1 where lock=0 and  courseno2 = :coursegroup and batch=:batch;';
        $cubind = array(
            ':flag'         => $flag,
            ':coursegroup'  => $coursegroup,
            ':batch'        => $batch,
        );
        $curst = $this->doneExecute($this->sqlExecute($cusql,$cubind));
        if(is_string($curst)){
            return $curst;
        }

        //将该课程的所有学生设置为已排
        //只要将课程设置为已经排号的即可
        $susql = 'update teststudent set flag=:flag,pici=:batch,lock=1 where lock=0 and courseno2 = :coursegroup';
        $subind = array(
            ':flag' => $flag,
            ':batch'    => $batch,
            ':coursegroup'  => $coursegroup,
        );
        $surst = $this->doneExecute($this->sqlExecute($susql,$subind));
        if(is_string($surst)){
            return $surst;
        }
        $this->commit();
        return intval($curst)+intval($surst);
    }

    /**
     * 获取某批次的某场次即将参与考试的人数
     * @param int $batch 批次
     * @param int $flag 场次
     * @return int|string string表示错误信息，int表示参与的人数
     */
    public function getStudentSumByFlagAndBatch($batch,$flag){
        $sql = '
SELECT COUNT(*) as c
FROM TESTSTUDENT tst
INNER JOIN TESTCOURSE tc on tc.CourseNo = tst.CourseNo
WHERE tc.batch = :batch and tc.Flag = :flag';
        $bind = array(
            ':batch'=> $batch,
            ':flag' => $flag,
        );
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind));
        if(is_string($rst)){
            return "查询某批次场次已派人数失败!{$rst}";
        }elseif(empty($rst)){
            return 0;//该批次场次没有人
        }
        return intval($rst[0]['c']);
    }

    /**
     * 检查某批次的时间是否已经设置了
     * @param int $batchno -1表示检查全部批次,自然数表示对应的批次
     * @return bool|string  返回true表示已经设置了，返回string类型表示发生了错误或者未设置
     */
    public function isTestTimeSetted($batchno=-1){
        if($batchno < 0){//全部检查
            $rst = $this->doneQuery($this->sqlQuery('select * from TESTBATCH'));
        }else{
            $rst = $this->doneQuery($this->sqlQuery('select * from TESTBATCH WHERE batch = '.intval($batchno)));
        }
        if(is_string($rst)){
            return "查询批次信息失败！{$rst}";
        }else{
            foreach($rst as $item){
                if(trim($item['TESTTIME']) === ''){
                    return "第{$item['batch']}批次第{$item['FLAG']}场次时间未设置！";
                }
            }
        }
        return true;
    }

    /**
     * 清空某学年学期的排课计划
     * @param $year
     * @param $term
     * @return int|string
     */
    public function cleartestPlanByYearTerm($year,$term){
        return $this->doneExecute($this->sqlExecute('DELETE from TESTPLAN WHERE [year] = :year and term = :term',array(
            ':year' => $year,
            ':term' => $term,
        )));
    }

    /**
     * 期末考试计划倒入到排课表中
     * @param $year
     * @param $term
     * @return int|string
     */
    public function importFinals($year,$term){
        $sql = '
insert into TestPlan(year,term,FLAG,DATE,COURSENO,ATTENDENTS,R10,rem,examType,cc)
select SP.[YEAR],SP.TERM,TC.batch,TB.TESTTIME,SP.COURSENO+SP.[GROUP] as coursegroup,SP.ATTENDENTS,SP.RECNO,\'\',\'M\',TC.Flag -- 将TC.FLAG 调整为TC.batch
from TESTCOURSE TC
inner join TESTBATCH TB ON TC.batch=TB.batch and TC.Flag = TB.FLAG-- 调整flag和batch对应的意义
inner join SCHEDULEPLAN SP ON TC.COURSENO=SP.COURSENO+SP.[group] AND SP.YEAR=TB.[YEAR] AND SP.TERM=TB.TERM
where TB.[YEAR] = :year and TB.TERM = :term
and NOT EXISTS (SELECT 1 from TESTPLAN tp WHERE tp.R10 = SP.RECNO) -- 修改为附加的方式';
        $bind = array(
            ':year' => $year,
            ':term' => $term,
        );

        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 获取某批次的所有课程和对应的考生数目
     * @param $batch
     * @param $flag
     * @param $year
     * @param $term
     * @return array|string
     */
    public function getCourselistbyBatchAndFlag($batch,$flag,$year,$term){
        $sql = '
SELECT
tp.courseno,
tp.R10 as recno,
COUNT(DISTINCT tst.Studentno) as c
 FROM TESTPLAN tp
INNER JOIN TESTCOURSE tc on tc.CourseNo = tp.COURSeNO
INNER JOIN  TESTSTUDENT tst ON  tst.courseno = tp.courseno
WHERE tc.batch = :batch and tc.Flag = :flag and tp.[year] = :year and tp.term = :term
GROUP BY tp.courseno,tp.R10
ORDER BY c DESC'; //按照人数从多到少排序
        $bind = array(
            ':batch'    => $batch,
            ':flag'     => $flag,
            ':year'     => $year,
            ':term'     => $term,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind));
    }

    /**
     * 判断某批次场次房间号是否被占用
     * @param $roomno
     * @param $batch
     * @param $flag
     * @return array|string
     */
    public function isTestRoomOccupyed($roomno,$batch,$flag){
        $sql = 'SELECT * from TESTROOM WHERE ROOMNO = :roomno and batch = :batch and flag = :flag';
        $bind = array(
            ':roomno'   => $roomno,
            ':batch'    => $batch,
            ':flag'     => $flag,
        );
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind));
        if(is_string($rst)){
            return $rst;
        }
        return count($rst)>0;
    }


}