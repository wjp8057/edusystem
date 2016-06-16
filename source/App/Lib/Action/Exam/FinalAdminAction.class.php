<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class FinalAdminAction extends RightAction {
    private $md;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }

    //todo:安排监考教师的页面
    public function invigilate(){
        $this->xiala('schools','schools');
        $this->assign('type',$_GET['examType']);
        $this->display();
    }



    //todo:统一排考的页面
    public function examCourses(){
        if($this->_hasJson){

            if(trim($_POST['bind'][':status'])=='启用'){
                $status=1;
            }else{
                $status=0;
            }
            $num=$this->md->sqlExecute($this->md->getSqlmap($_POST['exe']),array(':KW'=>$_POST['bind'][':KW'],':status'=>$status,':RECNO'=>$_POST['bind'][':RECNO']));
            exit;
        }
        $this->xiala('schools','schools');
        $this->display();
    }

    //todo:统一排考页面---->提交到数据库的方法
    public function insertPaikao(){
        $this->md->startTrans();
        foreach($_POST['bind'] as $val){
            $bind = array(':exam'=>$val['exam'],':recno'=>$val['recno']);
            $panduan=$this->md->sqlExecute('update SCHEDULEPLAN set exam=:exam where recno=:recno;',$bind);
            if(!$panduan){
                $this->md->rollback();
                exit("课号为{$val['kh']}的课程修改失败了！");
            }
        }
        $this->md->commit();
        exit('已成功完成更新！');
    }

    /**
     * 排考初始化
     */
    public function dataInit(){
        if($this->_hasJson){
            $this->md->startTrans();
            $bind = array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']);
            $rst = $this->isExecuteDone(
                $this->md->sqlExecute('delete from TestCourse'),
                $this->md->SqlExecute('
insert into TestCourse(CourseNo,CourseName,Flag,Lock,classes,CourseNo2)
select RTRIM(S.COURSENO)+RTRIM(S.[GROUP]) AS kh,C.COURSENAME,0,0,NULL,RTRIM(S.COURSENO)+RTRIM(S.[GROUP])
from SCHEDULEPLAN AS S
INNER JOIN COURSES AS C ON S.COURSENO=C.COURSENO
WHERE YEAR=:YEAR AND TERM=:TERM AND EXAM=1',$bind), // exam=1表示需要进行考试
                $this->md->sqlExecute('delete from TestStudent'),
                $this->md->SqlExecute('
insert into TestStudent(CourseNo,Studentno,Flag,lock,CourseNo2)
select R.COURSENO+R.[group] as kh,R.STUDENTNO,0,0,R.COURSENO+R.[group] as kh2
from R32 as R
inner join TestCourse as S on rtrim(R.COURSENO)+rtrim(R.[group])=rtrim(S.COURSENO)
where R.YEAR=:YEAR AND R.TERM=:TERM',$bind),
                $this->md->sqlExecute('delete from Testbatch'));
            if($rst === 0){
                $this->md->commit();
                exit('success');
            }else{
                $this->md->rollback();
                exit('error code:'.$rst);
            }
        }
        $this->display();
    }

    /**
     * 处理返回有问题的情况
     *      如果任意一个参数全等于false，则返回false
     */
    private function isExecuteDone(){
        $params = func_get_args();
        foreach($params as $key=>$param){
            if($param === false){
                return $key+1;//表示第几个失败,不可以是第零个
            }
        }
        return 0;
    }

    /**
     * 排考设置等价课程
     */
    public function equalCourses(){
        if($this->_hasJson){
            //先查询等价课程号存在不存在
            $courseno=$this->md->sqlFind('select COURSENO from TestCourse where COURSENO=:COURSENO',array(':COURSENO'=>$_POST['bind'][':EQCOURSENO']));
            if(!$courseno){//课号不存在
                exit('flase');//课号不存在 您不能修改
            }
            $this->md->startTrans();
            $bind = array(':EQCOURSENO'=>$_POST['bind'][':EQCOURSENO'],':COURSENO'=>$_POST['bind'][':COURSENO']);
            //修改TestCourse 的等价课程
            $one=$this->md->sqlExecute("update TestCourse set CourseNo2=:EQCOURSENO WHERE CourseNo=:COURSENO",$bind );
            $two=$this->md->sqlExecute("update teststudent set courseno2=:EQCOURSENO where courseno=:COURSENO",$bind);
            if($one&&$two){
                $this->md->commit();
                exit('true');
            }
            $this->md->rollback();
            exit('false');
        }
        $this->display();
    }
    //todo:测试
    public function equalCourses2(){
        foreach($_POST['bind'] as $val){
            //先查询等价课程号是否存在于考试课程中
            $courseno=$this->md->sqlFind('select COURSENO from TestCourse where COURSENO=:COURSENO',array(':COURSENO'=>$_POST['eqcourseno']));
            if(!$courseno){//todo:课号不存在
                exit('flase');//课号不存在 不能修改
            }
            $this->md->startTrans();
            //todo:修改TestCourse 的等价课程
            $one=$this->md->sqlExecute("update TestCourse set CourseNo2=:EQCOURSENO WHERE CourseNo=:COURSENO",
                array(':EQCOURSENO'=>$_POST['eqcourseno'],':COURSENO'=>$val['kh']));
            $two=$this->md->sqlExecute("update teststudent set courseno2=:EQCOURSENO where courseno=:COURSENO",
                array(':EQCOURSENO'=>$_POST['eqcourseno'],':COURSENO'=>$val['kh']));
            if(!$one||!$two){
                $this->md->rollback();
                exit('false');
            }
        }

        $this->md->commit();
        exit('true');
    }

    /**
     * 设置考试批次
     */
    public function setBatch(){

        if(isset($_GET['tag']) && $_GET['tag'] == 'addbatch'){
            $examfinalsModel = new ExamFinalsModel();
            $rst = $examfinalsModel->createTestBatchAutoaticly($_POST['YEAR'],$_POST['TERM']);
            if(is_string($rst) or !$rst){
                $this->failedWithReport("添加场次失败！{$rst}");
            }
            $this->successWithReport('添加场次成功！');
        }

        if(isset($_GET['tag']) && $_GET['tag'] == 'addpici'){
            $examfinalsModel = new ExamFinalsModel();
            $rst = $examfinalsModel->createTestBatchPiciAutoaticly($_POST['YEAR'],$_POST['TERM'],$_POST['batch']);
            if(is_string($rst) or !$rst){
                $this->failedWithReport("添加批次失败！{$rst}");
            }
            $this->successWithReport('添加批次成功！');
        }

        if(isset($_GET['tag']) && $_GET['tag'] == 'updatebatch'){
            $this->md->startTrans();
            $examfinalsModel = new ExamFinalsModel();
            foreach($_POST['rows'] as $val){
                $rst = $examfinalsModel->updateTestBatchByRecno($val['batch'],$val['testtime'],$val['recno']);
                if(is_string($rst)){
                    $this->failedWithReport("修改失败！{$rst}");
                }
            }
            $this->md->commit();
            $this->successWithReport('修改成功！');
        }

        if($this->_hasJson){
            $examfinalsModel = new ExamFinalsModel();
            $list = $examfinalsModel->listTestBatch();
            if(is_string($list)){
                $this->failedWithReport("获取失败！{$list}");
            }
            $this->ajaxReturn(array(
                'rows'  => $list,
                'total' => count($list),
            ));
//            $count=$this->md->sqlFind('select count(*) as ROWS from TESTBATCH');
//            if($data['total']=$count['ROWS']){
//                $data['rows']=$this->md->sqlQuery("select * from(select row_number() over(order by FLAG) as row,RECNO,FLAG as cc ,TESTTIME as sjsz FROM TESTBATCH) as b where b.row between :start and :end",array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize));
//            }else{
//                $data['rows']=array();
//            }
//            $this->ajaxReturn($data,'JSON');
        }
        $this->display();
    }



    //考试地点设置
    public function setAddress($rows = null,$batch=null,$flag=null,$year=null,$term=null,$school='%',$coursegroup='%',$roomno='%',$roomname='%',$tprow=null,$roomrow=null,$field=null){

        if(REQTAG === 'listCourses'){
            $finalExamModel = new ExamFinalsModel();
            $list = $finalExamModel->listCoursesForArrange($year,$term,$school,$coursegroup,$this->_pageDataIndex,$this->_pageSize);
            if(is_string($list)){
                $this->failedWithReport("查询列表数据失败！{$list}");
            }else{
                $this->ajaxReturn($list);
            }
        }elseif(REQTAG === 'listroom'){
            $roomModel = new RoomModel();
            $list = $roomModel->listRooms($roomno,$roomname,$this->_pageDataIndex,$this->_pageSize);
            if(is_string($list)){
                $this->failedWithReport("获取教室列表失败！{$list}");
            }else{
                $this->ajaxReturn($list);
            }
        }elseif(REQTAG === 'listtestroom'){
            $finalExamModel = new ExamFinalsModel();
            $list = $finalExamModel->listTestRooms($batch,$flag,$roomno,$this->_pageDataIndex,$this->_pageSize);
            if(is_string($list)){
                $this->failedWithReport("查询列表失败！{$list}");
            }else{
                $this->ajaxReturn($list);
            }
        }elseif(REQTAG === 'deleteTestRoom'){
            $finalExamModel = new ExamFinalsModel();
            $finalExamModel->startTrans();
            foreach($rows as $row){
                $recno = $row['recno'];
                $rst = $finalExamModel->deleteTestRoomByRecno($recno);
                if(is_string($rst) or !$rst){
                    $this->failedWithReport("删除失败！{$rst}");
                }
            }
            $finalExamModel->commit();
            $this->successWithReport('删除成功！');
        }

        if($this->_hasJson){
            return $this->setRoom($tprow,$roomrow,$field);
        }
        $this->display();
    }

    public function Quality_teachers(){

        $arr=array(':teachername'=>doWithBindStr($_POST['bind']['teachername']),':teacherno'=>doWithBindStr($_POST['bind']['teacherno']),':school'=>doWithBindStr($_POST['bind']['school']));
        $count=$this->md->sqlFind("select count(*) as ROWS from(select row_number() over(order by teacherno) as row,teacherno,name from
            teachers where name like :teachername and teacherno like :teacherno and (school like :school))as b",$arr);

        if($data['total']=$count['ROWS']){
            $data['rows']=$this->md->sqlQuery("select * from(select row_number() over(order by teachers.name) as row,teacherno,teachers.name,schools.name as school from
            teachers inner join schools on teachers.school=schools.school where teachers.name like :teachername and teacherno like :teacherno and (teachers.school like :school))as b where b.row between :start and :end",array_merge($arr,
                array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));
        }else{
            $data['total']=0;
            $data['rows']=array();
        }


        $this->ajaxReturn($data,'JSON');
    }
    //todo:监考安排的页面
    public function jiankaoanpai(){

            $arr=array(':year'=>$_POST['year'],':term'=>$_POST['term'],':sone'=>$_POST['bind']['school'],':stwo'=>$_POST['bind']['school'],
            ':teacherno'=>doWithBindStr($_POST['bind']['teacherno']),':name'=>doWithBindStr($_POST['bind']['name']));
        if($this->_hasJson){
            $count=$this->md->sqlFind("select count(*) as ROWS from(select b.*,row_number() over(order by b.name) as row from
                (select distinct temp.name,teacherno,schools.name as schoolname
                from (
                select name,teacherno,school from teachers
                where exists (select * from teacherplan inner join scheduleplan  on scheduleplan.recno=teacherplan.map
                inner join courses on courses.courseno=scheduleplan.courseno
                where teacherplan.year=:year and teacherplan.term=:term and courses.school=:sone and teachers.teacherno=teacherplan.teacherno)
                 union
                 select name,teacherno,school
                 from teachers
                 where school=:stwo)as temp inner join schools on schools.school=temp.school
                 where temp.teacherno like :teacherno and temp.name like :name
                 ) as b) as c",$arr);

            if($data['total']=$count['ROWS']){
                $data['rows']=$this->md->sqlQuery("select c.* from(select b.*,row_number() over(order by b.name) as row from
                        (select distinct temp.name,teacherno,schools.name as schoolname,temp.school
                        from (
                        select name,teacherno,school from teachers
                        where exists (select * from teacherplan inner join scheduleplan  on scheduleplan.recno=teacherplan.map
                        inner join courses on courses.courseno=scheduleplan.courseno
                        where teacherplan.year=:year and teacherplan.term=:term and courses.school=:sone and teachers.teacherno=teacherplan.teacherno)
                         union
                         select name,teacherno,school
                         from teachers
                         where school=:stwo)as temp inner join schools on schools.school=temp.school
                         where temp.teacherno like :teacherno and temp.name like :name
                         ) as b) as c where c.row between :start and :end",array_merge($arr,
                    array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));

            }else{
                $data['total']=0;
                $data['rows']=array();
            }


            $this->ajaxReturn($data,'JSON');

        }

        $courseno=$_GET['COURSENO'];

        //todo:找出教师学院
        $teacherSCHOOL=$this->md->sqlfind('select SCHOOL from TEACHERS where RTRIM(TEACHERNO)=:TEACHERNO',array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));


        if($_GET['examtype']=='C'||$_GET['examtype']=='B'){

            $haha=$this->md->sqlQuery($this->md->getSqlMap('exam/Two_one_select_'.$_GET['examtype'].'.SQL'),
            array(':COURSESCHOOL'=>'%',':CHANGCI'=>'%',':COURSENO'=>doWithBindStr($courseno),':examType'=>$_GET['examtype'],':year'=>$_GET['YEAR'],':term'=>$_GET['TERM'],':start'=>1,':end'=>5));

        }else{
            $haha=$this->md->sqlQuery($this->md->getSqlMap('exam/Two_one_select.SQL'),
             array(':COURSESCHOOL'=>'%',':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':CLASSSCHOOL'=>'%',':CLASSNO'=>'%',':CHANGCI'=>'%',':COURSENO'=>doWithBindStr($courseno),':examType'=>$_GET['examtype'],':start'=>1,':end'=>5));
        }

        //todo:找出teacherList   T1,T2,T3....TNAME1,TNAME2,TNAME3...S1,S2,S3
       $gaga= $this->md->sqlQuery($this->md->getSqlmap('exam/jiankaoanpai_teacherList.SQL'),array(':R15'=>$_GET['R15']));


        //todo:教师所在学院
        $teacherschool=$this->md->sqlFind("select SCHOOL from teachers where teacherno=:teacherno",
        array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO']));

        //todo:查询考试时间
        $TIME=$this->md->sqlQuery("select FLAG,TESTTIME from TESTBATCH order by FLAG");

        //todo:查询教师列表
        $teacher=$this->md->sqlQuery("SELECT RTRIM(TEACHERNO)+'_'+RTRIM(SCHOOL) AS CODE,NAME FROM TEACHERS WHERE SCHOOL='{$teacherSCHOOL['SCHOOL']}' order by name");
       $schoolList=$this->md->sqlQuery('select * from schools');


        $this->assign('teacherSCHOOL',$teacherSCHOOL);
        $this->assign('R15',$_GET['R15']);
        $this->assign('TList',json_encode($gaga[0]));
        $this->assign('teacherList',$gaga);
        $this->assign('myschool',$teacherschool['SCHOOL']);
        $this->assign('schoolList',$schoolList);
        $this->assign('teachers',$teacher);
        $this->assign('time',$TIME);
        $this->assign('info',$haha);

//        varsPrint($teacherSCHOOL,$_GET['R15'],json_encode($gaga[0]),$gaga,$teacherschool['SCHOOL'],$schoolList,$teacher,$TIME,$haha);

        $this->display();
    }


    /**
     * @param array $tprow testplan row
     * @param array $roomrow room row
     * @param string $field 更新的教室字段名称
     * @return mixed
     */
    public  function setRoom($tprow,$roomrow,$field){

        //更新的座位的字段名称
        $seatfieldname = 'seats'.substr($field,6);
        $seatfieldvalue = $roomrow['testers'];//考试座位数
        $recno = $tprow['recno'];
        $roomname = $roomrow['roomname'];


        $batch = $tprow['batch'];
        $flag = $tprow['flag'];

        $examFinalsModel = new ExamFinalsModel();

        $examFinalsModel->startTrans();
        $crst = $examFinalsModel->createTestRoomRecord($batch,$flag,$roomrow['roomno'],$seatfieldvalue,$roomrow['building'],
            $roomrow['no'],$roomrow['roomname']);
        if(is_string($crst) or !$crst){
            $this->failedWithReport("添加考场失败,考场可能已经被占用!{$crst}");
        }
        $rst = $examFinalsModel->updateTestPlanRoom($recno,$field,$roomname,$seatfieldname,$seatfieldvalue);
        if(is_string($rst) or !$rst){
            $this->failedWithReport("更新失败！{$rst}");
        }

        $examFinalsModel->commit();
        $this->successWithReport('更新成功！');
    }


}
