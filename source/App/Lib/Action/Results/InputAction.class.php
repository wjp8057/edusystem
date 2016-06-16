<?php
/**
 * Created by Linzh.
 * User: Administrator
 * Date: 2015/10/16
 * Time: 8:57
 */

/**
 * Class InputAction 成绩输入 子模块
 *
 * 国语英文对照：
 *  补考       - resit
 *  期末考     - finals
 *  重修考     - retake
 */
class InputAction extends RightAction {
    /**
     * @var ResultModel
     */
    private $model = null;


    /**
     * 每次构造的时候都初始化并不见得高效
     * 后期建议用 __get 来获取Model实例
     */
    public function __construct(){
        parent::__construct();

        $this->model = new ResultModel();

        //预分配通用信息
        $this->assign('yearterm',$this->model->getYearTerm('J'));
        $this->assign('school',$_SESSION['S_USER_INFO']['SCHOOL']);
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
    }


    public function selectPersonalResultInfo($studentno){

    }

    /**
     * 学生个人成绩
     */
    public function pagePersonalResult(){
        $this->display('Input/pagePersonalResult');
    }

    /**
     * 补考成绩输入课程列表查询界面
     */
    public function pageResitSelect(){
        $this->xiala('schools','schools');
        $this->display('Input/pageResitSelect');
    }

    /**
     * 补考成绩输入 学生列表界面
     * @param $year
     * @param $term
     * @param string $school
     * @param string $courseno
     */
    public function pageResitInput($year,$term,$school='%',$courseno='%'){
        $detail = $this->model->getResitCourseTableList($year,$term,$school,$courseno);
        if(0 === $detail['total']){
            $this->exitWithReport("第[{$year}]学年第[{$term}]学期学院号为[{$school}]课号为[{$courseno}]的课程不存在，请联系教务处管理员!");
        }
        $this->assign('year_a',$year);
        $this->assign('year_b',intval($year)+1);
        $this->assign('term',$term);
        $this->assign('detail',$detail['rows'][0]);
        $this->assign('params',$_GET);
        $this->display('Input/pageResitInput');
    }

    /**
     * 补考成绩输入 学生列表打印界面
     * @param $year
     * @param $term
     * @param $courseno
     * @param int $page
     */
    public function pageResitInputForPrint($year,$term,$courseno,$page=1){
        $courseModel = new CourseModel();
        $courseinfo = $courseModel->getCourseInfo($courseno);
        if(is_string($courseinfo)){
            exit("获取课程[{$courseno}]的信息失败!{$courseinfo}");
        }
        $this->assign('page',$page);
        $this->assign('courseinfo',$courseinfo);
        $this->assign('year_a',$year);
        $this->assign('year_b',intval($year)+1);
        $this->assign('term',$term);
        $this->assign('courseno',$courseno);
        $this->display('Input/pageResitInputForPrint');
    }
    public function pageFinalsInputForPrint($year,$term,$courseno,$page=1){

        $teacherModel = new TeacherModel() ;
        $teachers = $teacherModel->getTeachersBySchedulePlan($year,$term,$courseno);

        $courseModel = new CourseModel();
        $courseinfo = $courseModel->getCourseInfo($courseno);
        if(is_string($courseinfo)){
            $this->exitWithReport("获取课程[{$courseno}]的信息失败!{$courseinfo}");
        }

        $classModel = new ClassesModel();
        $classes = $classModel->getClassesnameByCourseno($year,$term,$courseno);
        if(is_string($classes)){
            $this->exitWithReport("获取课程[{$courseno}]的班级信息失败!{$classes}");
        }

        $this->assign('page',$page);
        $this->assign('year_a',$year);
        $this->assign('year_b',intval($year)+1);
        $this->assign('term',$term);
        $this->assign('courseno',$courseno);
        $this->assign('teachers',$teachers);
        $this->assign('courseinfo',$courseinfo);
        $this->assign('classes',$classes['classname']);

        $this->display('Input/pageFinalsInputForPrint');
    }
    /**
     * 任课老师期末成绩输入 课程选择界面
     */
    public function pageFinalsSelect(){
        $this->display('Input/pageFinalsSelect');
    }

    /**
     * 任课老师期末成绩输入 课程列表获取
     * @param $year
     * @param $term
     * @param $teacherno
     */
    public function listFinalsSelect($year,$term,$teacherno){
        $rst = $this->model->getFinalsCourseTableList($year,$term,$teacherno);
        if(is_string($rst)){
            $this->exitWithReport("查询期末成绩输入课程列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 任课老师期末成绩输入 学生列表界面
     * @param $year
     * @param $term
     * @param string $coursegroupno 课号加组号
     * @param $scoretype
     */
    public function pageFinalsInput($year,$term,$coursegroupno,$scoretype){
        $this->assign('year_a',$year);
        $this->assign('year_b',intval($year)+1);
        $this->assign('term',$term);
        $this->assign('coursegroupno',$coursegroupno);
        $this->assign('scoretype',$scoretype);
        $scoretypetext = null;
        switch($scoretype){
            case 'ten':
                $scoretypetext = '百分制';
                break;
            case 'five':
                $scoretypetext = '五级制';
                break;
            case 'two':
                $scoretypetext = '二级制';
                break;
            default:
                $scoretypetext = '记分制不明确';
        }
        $this->assign('scoretypetext',$scoretypetext);

        $info = $this->model->getFinalsCourseInfo($year,$term,$coursegroupno);
        if(is_string($info)){
            header("Content-type:text/html;charset=utf-8");
            exit( "获取课程详细信息失败，请联系管理员协助解决你的问题！{$info}");
        }

        $this->assign('examtime',$info['examtime']);
        $this->assign('courseinfo',$info);//原先assignTT

        $teacherModel = new TeacherModel() ;
        $tinfo = $teacherModel->getTeachersBySchedulePlan($year,$term,$coursegroupno);

        $this->assign('teachers',$tinfo);//原先assignTT
        $this->display('Input/pageFinalsInput');
    }

    /**
     * 任课老师期末成绩输入 学生列表获取
     * @param $year
     * @param $term
     * @param $courseno
     */
    public function listFinalsInput($year,$term,$courseno){
        $rst = $this->model->getFinalsStudentlist($year,$term,$courseno);
        if(is_string($rst)){
            $this->failedWithReport("获取期末考试成绩学生列表数据失败!{$rst}");
        }
        $this->ajaxReturn($rst,"JSON");
        exit;
    }
    /**
     * 学生列表 for 补考成绩打印界面
     * @param $year
     * @param $term
     * @param $courseno
     * @param int $page
     */
    public function listResitInputForPrint($year,$term,$courseno,$page=1){
        $start  = ($page-1)*120;
        $end    = $page*120;
        $rst = $this->model->getResitStudentList($year,$term,$courseno,$start,$end);
        if(is_string($rst)){
            $this->exitWithReport("查询第[{$year}]学年第[{$term}]学期课号为[{$courseno}]的补考学生列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
        exit;
    }

    /**
     * 学生列表 for 期末成绩打印界面
     * @param $year
     * @param $term
     * @param $courseno
     * @param int $page
     */
    public function listFinalInputForPrint($year,$term,$courseno,$page=1){
        $start  = ($page-1)*120;
        $end    = $page*120;
        $rst = $this->model->getFinalsStudentlist($year,$term,$courseno,$start,$end);
        if(is_string($rst)){
            $this->exitWithReport("查询第[{$year}]学年第[{$term}]学期课号为[{$courseno}]的补考学生列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
        exit;
    }
    /**
     * 获取补考成绩输入课程列表
     * @param $year
     * @param $term
     * @param $schoolno
     * @param $courseno
     */
    public function listResitSelect($year,$term,$schoolno,$courseno){
        $rst = $this->model->getResitCourseTableList($year,$term,$schoolno,$courseno);
        if(is_string($rst)){
            $this->exitWithReport("查询补考成绩输入课程列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 获取补考成绩输入界面学生列表数据
     * @param $year
     * @param $term
     * @param $courseno
     */
    public function listResitStudent($year,$term,$courseno){
        $rst = $this->model->getResitStudentList($year,$term,$courseno);
        if(is_string($rst)){
            $this->exitWithReport("查询第[{$year}]学年第[{$term}]学期课号为[{$courseno}]的补考学生列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
        exit;
    }

    /**
     * 批量 修改期末考试成绩
     * @param $rows
     * @param $sp
     * @param $examtime
     */
    public function updateFinalsScoreInBatch($rows,$sp,$examtime){
        $this->model->startTrans();
        foreach($rows as $key=>$val){
            $recno = $val['_origin']['recno'];
            $rst = $this->model->isStudentFinalsLockedByRecno($recno);
            if(is_string($rst)){
                $this->exitWithReport($rst);
            }elseif($rst){
                continue;
//                $this->exitWithReport("学生期末成绩输入已经上锁，请确认，有问题请联系教务处管理员！{$rst} {$recno}");
            }
            $examscore = $val['finalscore']['examscore'];
            $testscore = $val['finalscore']['testscore'];
            $rst = $this->model->updateStudentFinalsResultByRecno($examscore,$testscore,$examtime,$recno,$sp);
            if(is_string($rst) or !$rst){
                $msg = "修改学生期末成绩失败！{$rst}";
                if($this->model->isShowErrorSwitchOn()){
                    $msg .= "/{$examscore} *{$testscore} -{$examtime} .{$sp}";
                }
                $this->failedWithReport($msg);
            }
        }
        $this->model->commit();
        $this->successWithReport("修改成功!");
    }
    /**
     * 解锁 学生的期末考试成绩输入
     * @param $year
     * @param $term
     * @param $courseno
     */
    public function updateResitLockStatus($year,$term,$courseno){
        $rst = $this->model->unlockStudentResitStatusByCourseno($year,$term,$courseno);
        if(is_string($rst) or !$rst){
            $this->exitWithReport("解锁第{$year}学年第{$term}学期课号为{$courseno}的补考成绩输入失败!{$rst}");
        }
        $this->successWithReport("解锁第{$year}学年第{$term}学期课号为{$courseno}的补考成绩输入成功!{$rst}");
    }
    /**
     * 批量 修改补考成绩
     * @param $rows
     * @param $year
     * @param $term
     * @param $examdate
     * @param $courseno
     */
    public function updateResitScoreInBatch($rows,$year,$term,$examdate,$courseno){
        $this->model->startTrans();
        $count = 0;
        foreach($rows as $recno=>$student){
            $rst = $this->model->isStudentResitLocked($courseno,$year,$term,$student['studentno']);
            if(is_string($rst)){
                $this->exitWithReport("课号[{$courseno}]学年[{$year}]学期[{$term}]学号[{$student['studentno']}]后台确认学生补考是否锁定失败！{$rst}");
            }elseif($rst){
                $this->exitWithReport("课号[{$courseno}]学年[{$year}]学期[{$term}]学号[{$student['studentno']}]后台确认锁定，有问题请联系管理员!{$rst}");
            }
            $rst = $this->model->lockStudentResit($courseno,$year,$term,$student['studentno'],$examdate);
            if(is_string($rst) or !$rst){
                $this->exitWithReport("课号[{$courseno}]学年[{$year}]学期[{$term}]学号[{$student['studentno']}]修改失败，有问题请联系管理员!{$rst}");
            }
            $rst = $this->model->updateStudentResitResult($student['examscore'],$student['testscore'],$recno);
            if(is_string($rst) or !$rst){
                $this->exitWithReport("修改学生补考成绩失败！".($this->model->isShowErrorSwitchOn()?"Params{{examscore:[{$student['examscore']}] testscore:[{$student['testscore']}] RECNO:[{$recno}]}}":$rst));
            }
            ++$count;
        }
        $this->model->commit();
        $this->successWithReport("修改完成,共修改[{$count}]条记录 ！");
    }
    /**
     * 显示还未输入成绩的模块
     */
    public function pageCoursesWhichScoreInputness(){
        $this->xiala('schools','schools');
        $this->display('Input/pageCoursesWhichScoreInputness');
    }

    /**
     * 还未输入成绩 课程列表显示
     * @param null $year
     * @param null $term
     * @param null $school
     */
    public function listCoursesWhichScoreInputness($year=null,$term=null,$school=null){
        $rst = $this->model->getCourseListWhichScoreInputness($year,$term,$school,$this->_pageDataIndex,$this->_pageDataIndex+$this->_pageSize);
        if(is_string($rst)){
            exit("查询未输入成成绩的课程失败!{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 开放与查看课程 页面
     */
    public function pageCoursesWithOpen(){
        $this->xiala('schools','schools');
        $this->display('Input/pageCoursesWithOpen');
    }

    /**
     * 开放与查看课程 页面列表数据
     * @param $year
     * @param $term
     * @param $school
     */
    public function listCoursesWithOpen($year,$term,$school){
        $rst = $this->model->getCoursesWithOpenTableList($year,$term,$school,$this->_pageDataIndex,$this->_pageDataIndex+$this->_pageSize);
        if(is_string($rst)){
            $this->failedWithReport("获取失败!{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }
    /**
     * 批量修改课程的期末成绩输入状态
     * @param $year
     * @param $term
     * @param $courses
     */
    public function updateFinalsLackStatusInBatch($year,$term,$courses){
        $this->model->startTrans();
        foreach($courses as $course){
            $courseno = $course['coursegroupno'];
            $rst = $this->model->updateFinalsLackStatus($year,$term,$courseno);
            if(is_string($rst) or !$rst){
                $this->failedWithReport("修改第{$year}学年第{$term}学期课号为{$courseno}的课程期末成绩输入状态失败！{$rst}");
            }
        }
        $this->model->commit();
        $this->successWithReport("修改课程期末成绩输入状态成功！");
    }

    /**
     * 查看和开放课程导出excel
     * @param $year
     * @param $term
     * @param $school
     */
    public function exportCoursesWithOpen($year,$term,$school){
        //获取数据
        $rst = $this->model->getCoursesWithOpenTableList($year,$term,$school);
        if(is_string($rst)){
            $this->failedWithReport("获取失败!{$rst}");
        }

        //初始化PHPExcel
        $this->model->initPHPExcel();

        //设置对齐信息和数据域
        $data['title'] = '课程选课情况';
        $data['head'] = array(
            'coursegroupno' => array( '课号', 'align' => CommonModel::ALI_CENTER,'width'=>20,'type'=>CommonModel::TYPE_STR ),
            'coursename' => array(0=> '课程名称','align' => CommonModel::ALI_CENTER,'width'=>30),
            'approach' => array( '修课方式', 'align' => CommonModel::ALI_CENTER),
            'examtype' => array( '考核方式', 'align' => CommonModel::ALI_CENTER,'width'=>30),
            'lock' => array( '锁定输入学生数', 'align' => CommonModel::ALI_CENTER,'width'=>30),
        );
        $data['body'] = $rst['rows'];

        //输出Excel文件
        $this->model->fullyExportExcelFile($data, $data['title']);
    }

    /**
     * 毕业重修成绩输入 页面
     */
    public function pageRetakeSelect(){
        $this->xiala('schools','schools');
        $this->display('Input/pageRetakeSelect');
    }

    /**
     * 毕业重修成绩输入 课程列表数据获取
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @param $school
     */
    public function listRetakeSelect($year,$term,$coursegroupno,$school){
        $rst = $this->model->getRetakeCourseList($year,$term,$coursegroupno,$school,$this->_pageDataIndex,$this->_pageDataIndex+$this->_pageSize);
        if(is_string($rst)){
            $this->exitWithReport("查询失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 毕业前补考成绩输入 直接套用期末成绩输入，将标题修改
     * @param $year
     * @param $term
     * @param $coursegroupno
     * @param $scoretype
     */
    public function pageRetakeInput($year,$term,$coursegroupno,$scoretype){
        $isretake = true;
        $this->pageFinalsInput($year,$term,$coursegroupno,$scoretype);
    }

    /**
     * 免于体侧名单输入  ==> 大概是免试的意思吧！！
     */
    public function pageAvoidSelect(){
        $this->display('Input/pageAvoidSelect');
    }
    public function listAvoidSelect($year,$term,$studentname){
        $rst = $this->model->getAvoidList($year,$term,$studentname,$this->_pageDataIndex,$this->_pageDataIndex+$this->_pageSize);
        if(is_string($rst)){
            $this->exitWithReport("查询第{$year}学年第{$term}学期学生名类似{$studentname}的学生列表失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 免于体侧名单输入 增加记录
     * @param $year
     * @param $term
     * @param $studentno
     * @param $name
     * @param $classname
     * @param $time
     * @param $reason
     */
    public function createAvoidRecord($year,$term,$studentno,$name,$classname,$time,$reason){
        //检验学号
        $this->selectStudentInfo($studentno,true);

        $fields = array(
            'year'  => array($year,true),
            'term' => $term,
            'studentno' => $studentno,
            'name' => array($name,true),
            'classname' => $classname,
            'time' => array($time,true),
            'reason' => $reason,
        );
        $rst = $this->model->createAvoidRecord($fields);
        if(is_string($rst) or !$rst){
            $this->exitWithReport("添加失败，改学生本学期肯可能已经添加过免测！{$rst}");
        }
        $this->exitWithReport("添加成功！{$rst}");
    }

    /**
     * 根据学号信息获取学生数据
     * @param $studentno
     * @param bool $innercall 是否是内部调用，true时只返回结果数据
     * @return array|int|string
     */
    public function selectStudentInfo($studentno,$innercall=false){
        $studentModel = new StatusModel();
        $rst = $studentModel->getStudentInfo($studentno);
//        mist($rst[0],array_slice($rst[0],0,17));
        $rst = array_slice($rst[0],0,17);
        if(is_string($rst)){
            $this->failedWithReport("检验学号为[{$studentno}]的学生是否存在失败！{$rst}");
        }elseif(!$rst){
            $this->failedWithReport("学号为[{$studentno}]的学生不存在！{$rst}");
        }
        if($innercall){
            return $rst;
        }
        $this->ajaxReturn($rst,'JSON');
        exit;
    }


}