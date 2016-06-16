<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-7-9
 * Time: 下午12:44
 */
class ArrangeTestAction extends RightAction {
    /**
     * @var ExamArrangeModel
     */
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");
    /**
     * 自动排考需要的信息数组
     * @var array
     */
    private $dataInfo = array();

    public function __construct(){
        parent::__construct();
        $this->model = new ExamArrangeModel();
    }

    public function auto(){
        $this->setDataInfo();
        $this->assign('dataInfo',$this->dataInfo);
        $this->assign('batches',$this->model->getBatchList());
        $this->__done();
    }

    /**
     * 自动排考
     * @param null $miniFlag 该批次需要的最小的场次
     * @param null $maxAttendance 批次最大人数
     * @param null $batch 批次
     * @param null $year
     * @param null $term
     * @param bool|false $redo 是否清空之前的数据
     */
    public function arrangeTest($miniFlag=null, $maxAttendance=null, $batch=null, $year=null, $term=null,$redo=false){
        set_time_limit(70);

        $arrangeModel = new ExamArrangeModel();

        //获取当前要排考的批次，0为全部批次,将被转为-1
        $batch = intval($batch);
        $batch = 0===$batch?-1:$batch;

        //注意整形
        $maxAttendance = intval($maxAttendance);
        $miniFlag = intval($miniFlag);

        if($redo){
            $arrangeModel->resetTestCourseArrangment($batch);
        }

        $testcourselist = $arrangeModel->getTestCourseListForArrange($batch);
        if(is_string($testcourselist)){
            $this->failedWithReport("查询排考课程失败！{$testcourselist}");
        }elseif(empty($testcourselist)){
            $this->failedWithReport('没有需要排考的课程!');
        }

        foreach($testcourselist as $testcourse){

            $courseno = $testcourse['coursegroup'];
            $message = null;

            //场次1到 最小需要的场次之间的遍历,原先统一是15
            for($curflag=1;$curflag<=$miniFlag;$curflag++) {
                //错误信息,最后通过isset检测

                //找到批次已排课人数
                $arrageNum = $arrangeModel->getStudentSumByFlagAndBatch($batch,$curflag);
                if(is_string($arrageNum)){
                    $this->failedWithReport($arrageNum);
                }
                //检查是否超出单场限制，如果是则视情况而定作出处理
//                mist($arrageNum ,$testcourse['attendents'],$maxAttendance);
                $this->message['message'] .= "第{$batch}批次第{$curflag}场次已排{$arrageNum}人,检测能否排入{$testcourse['attendents']}人";
                if(($arrageNum + $testcourse['attendents']) > $maxAttendance){
                    if($curflag === $miniFlag){
                        //已经是最后一场可以排了,由于排不进
                        $message = "课程{$testcourse['coursename']}需要考试的人数是{$testcourse['attendents']}，无法排进所有场次，请增加场次！";
                        break;
                    }else{
                        //如果该课程需要考试的学生人数 + 已经排好的人数 > 单场人数限制，寻找下一场进行安排
                        $this->message['message'] .= '【否】...<br />';
                        continue;
                    }
                }else{
                    $this->message['message'] .= '【是】...<br />';
                }

                //检测课程考试是否有冲突
                $conflict = $arrangeModel->isTestCourseHasConflictByFlagAndBatch($courseno,$curflag,$testcourse['minflag']);
                if(is_string($conflict)){
                    $this->failedWithReport($conflict);
                }
                if($conflict > 0) continue;

                //检查考试批次场次是否存在，不存在时创建
                $rst = $arrangeModel->createTestBatchIfNotExist($year,$term,$batch,$curflag);
                if(is_string($rst)){
                    $this->failedWithReport("创建场次批次出错！{$rst}");
                }

                //更新课程和学生信息
                $rst = $arrangeModel->updateArrange($curflag,$courseno,$batch);
                if(is_string($rst)){
                    $message = "课程{$testcourse['coursename']}无法排考！";
                    break;
                }elseif($rst){
                    $message = "[{$courseno}] {$testcourse['coursename']}排考试成功！<br />";
                    break;
                }else{
                    $message = "[{$courseno}] {$testcourse['coursename']}排考试失败！<br />";
                    break;
                }
            }

            $this->message['message'] .= "{$message}<br />";
        }
        $this->__done();
    }

    /**
     * 导入到TestPlan
     * @param $examtype
     * @param $year
     * @param $term
     * @param $importtype
     */
    public function import($examtype,$year,$term,$importtype){

        $arrangeModel = new ExamArrangeModel();

        $rst = $arrangeModel->isTestTimeSetted();
        if(true !== $rst){
            $this->failedWithReport($rst);
        }

        $num = 0;

        $arrangeModel->startTrans();

        switch($examtype){
            case 'M':               //末
                if($importtype){
                    $rst = $arrangeModel->cleartestPlanByYearTerm($year,$term);
                    if(is_string($rst)){
                        $this->failedWithReport("清空旧的记录失败！{$rst}");
                    }
                }

                //往TestPlan插入数据
                $num = $arrangeModel->importFinals($year,$term);
                if(is_string($num)){
                    $this->failedWithReport("导入失败！{$num}");
                }
//                $num=$this->model->sqlExecute($this->model->getSqlmap("exam/insertTestPlan_M.SQL"),array(':YONE'=>$_POST['year'],':TONE'=>$_POST['term'],':examType'=>$_POST['examtype'],':YTWO'=>$_POST['year'],':TTWO'=>$_POST['term']));
                break;
            case 'C':               //初
                /**
                 * insert into TestPlan(year,term,FLAG,DATE,COURSENO,ATTENDENTS,R10,rem,examType)
                select :YEAR,:TERM,T.FLAG,TB.TESTTIME,T.COURSENO,renshu.rs,'','','C'
                FROM TestCourse as T inner join (select courseno,count(*) as rs from TestStudent group by courseno) as renshu on T.courseno=renshu.courseno
                inner join TESTBATCH TB ON T.flag=TB.FLAG
                 *
                 * 存在的问题，Unique key 'dd' 使用了R10字段，但是这里给R10统一插入了''
                 */
                $num=$this->model->sqlExecute($this->model->getSqlmap("exam/insertTestPlan_C.SQL"),array(':YEAR'=>$_POST['year'],':TERM'=>$_POST['term']));
                break;
            case 'B'://毕业前补考
                $num=$this->model->sqlExecute($this->model->getSqlmap("exam/insertTestPlan_B.SQL"),array(':YONE'=>$_POST['year'],':TONE'=>$_POST['term'],':YTWO'=>$_POST['year'],':TTWO'=>$_POST['term']));
                break;
        }
        $arrangeModel->commit();
        $this->successWithReport("成功插入{$num}条记录！");
    }


    /**
     * 自动安排考场
     * @param int $batch 批次号
     */
    public function anpai($batch=-1){
//        $arrangeModel = new ExamArrangeModel();
//        $roomModel = new RoomModel();
//        $examfinalModel = new ExamFinalsModel();
//
//        //获得该场次批次数量总数
//        $batches = $arrangeModel->getBatchFlags($batch);
//        //获取教室列表
//        $roomlist = $roomModel->getUsableRoomlist();
//
//
//        $roomOccupy = array();
//
//        //遍历批次
//        foreach($batches as $batch){
//            //获取场次批次
//            $bt = $batch['batch'];
//            $cc = $batch['flag'];
//            $year = $batch['year'];
//            $term = $batch['term'];
//
//            $courselist = $arrangeModel->getCourselistbyBatchAndFlag($bt,$cc,$year,$term);
//
//            foreach($courselist as $testcourse){
//                $attendents = intval($testcourse['c']);
//                $coursegroup = $testcourse['courseno'];
//                $recno = $testcourse['recno'];
//
//                foreach($roomlist as $room){
//                    $roomno = $room['roomno'];
//                    $key = "{$roomno} {$batch} {$cc}";
//                    if(empty($roomOccupy[$key])){
//                        //先检查是否已经被占用了
//                        $ocped = $arrangeModel->isTestRoomOccupyed($roomno,$year,$term);
//                        if($ocped){
//                            $roomOccupy[$key] = true;
//                        }else{
//                            if(intval($room['testers']) > $attendents){
//                                //判断该教室的考位数目是否大于等于这门考试需要的考位数
//                                $roomOccupy[$key] = true;
//                                //修改testplan
//
//                                //修改testroom
//                            }
//                        }
//                    }
//                }
//
//
//
//
//
//            }
//
//
//        }

        //todo:找出批次
        $pici=$this->model->sqlFind("select count(*) as ROWS from TestBATCH");
        $roomList=$this->model->sqlQuery("select TR.KW,TR.ROOMNO,TR.ROOMNAME FROM TESTROOM TR where TR.status=1 order by TR.KW desc");
        $canshuer=array();
        $canshusan=array();
        for($i=0;$i<count($roomList);$i++){
            $canshuer[$roomList[$i]['KW']]+=1;
            if(!is_array($canshusan[$roomList[$i]['KW']])){
                $canshusan[$roomList[$i]['KW']]=array();
            }
            array_push($canshusan[$roomList[$i]['KW']],$roomList[$i]);

        }



        $rm=$roomList;
        //todo:如果是课程优先
        if($_POST['type']==1){
            for($i=0;$i<$pici['ROWS'];$i++){

                //todo:教室列表
                $length=count($roomList);
                //todo:255
                $courseList=$this->model->sqlQuery($this->model->getSqlmap('exam/select_255.SQL'),array(':YTWO'=>$_POST['year'],':TTWO'=>$_POST['term'],':FLAG'=>$i+1,':examType'=>$_POST['examtype']));


                $caner=$canshusan;
                $cansan=$canshuer;

                foreach($courseList as $val){
                    $ii=1;           //安排的教室数
                    // $weianpai=;         //todo:未安排人数


                    $content=getSimilar($val['ATTENDENTS'],$caner,$cansan,$ii);

                    if(!$content){//todo:如果三场都拍不下报错
                        continue;
                        exit('考场数已达到上限,请增加批次');
                    }


                    foreach($content as $key=>$vv){
                        $this->model->sqlExecute($this->model->sqlExecute("update TESTPLAN set ROOMNO{$key}='{$vv['ROOMNAME']}',seats{$key}={$vv['KW']},rs{$key}={$vv['rs']} where R15='{$val['R15']}'"));
                    }


                }


            }
            echo '排考成功';
        }else{
            //   $this->model->startTrans();
            for($i=0;$i<$pici['ROWS'];$i++){


                //todo:255
                $courseList=$this->model->sqlQuery($this->model->getSqlmap('exam/select_255.SQL'),array(':YTWO'=>$_POST['year'],':TTWO'=>$_POST['term'],':FLAG'=>$i+1,':examType'=>$_POST['examtype']));

                foreach($roomList as $val){

                    $seats=$val['KW'];           //todo:座位数

                    foreach($courseList as $key=>$v){
                        if($v['ATTENDENTS']==0){
                            continue;
                        }
                        if($courseList[$key]['rs2']){
                            $ii=3;
                        }else if($courseList[$key]['rs1']){
                            $ii=2;
                        }else{
                            $ii=1;
                        }


                        if($seats-$v['ATTENDENTS']<=0){  //todo:如果考位数小于 选课人数

                            //todo:设置考场
                            $bool=$this->model->sqlExecute("update TESTPLAN set ROOMNO$ii='{$val['ROOMNAME']}',seats$ii={$val['KW']},rs$ii=$seats where R15='{$v['R15']}'");
                            $courseList[$key]['ATTENDENTS']=$v['ATTENDENTS']-$seats;
                            $courseList[$key]["rs$ii"]=$seats;

                            if($courseList[$key]['ATTENDENTS']&&$ii==3){//todo:如果还有人,并且排不下了
                                //    $this->model->rollback();
                                exit("{$courseList[$key]['COURSeNO']}考场拍排不下了");
                            }
                            break;
                        }else{

                            $seats-=$v['ATTENDENTS'];
                            //todo:设置考场

                            $bool=$this->model->sqlExecute("update TESTPLAN set ROOMNO$ii='{$val['ROOMNAME']}',seats$ii={$val['KW']},rs$ii={$v['ATTENDENTS']} where R15='{$v['R15']}'");
                            if($bool){
                                unset($courseList[$key]);
                            }

                        }

                    }

                }
            }
            echo '排考成功';


        }

    }




    /**
     * $arr是教室列表
     */
    //todo:取得最差值值
   public function jiejin($num,$jiaarr,$key){
        $cha=$jiaarr[$key]['KW']-$num;        //todo:先取一个
        $j=$key;
        foreach($jiaarr as $k=>$v){
                if(!$cha){
                    return $k;
                }
                if($jiaarr[$k]['KW']-$num>0&&$jiaarr[$k]['KW']-$num<$cha&&$cha>0){
                    $cha=$jiaarr[$k]['KW']-$num;
                    $j=$k;
                }
        }
        if($j==$key&&$jiaarr[$key]['KW']-$num<0){
            return false;
        }
  //      var_dump($j.'最后出去的$j');
        return $j;
    }


    /**
     * 取得批次信息　$batch=0 为全部批次
     * @param int $batch
     */
    public function setDataInfo($batch=0){
        $batch = (0===intval($batch))?-1:intval($batch);//旧的代码中0表示全部，实际上0表示未设置的意思，这里转成-1
        // 计算考生总人数 这里规定前端传递过来的0表示全部，需要转为-1
        $this->dataInfo["totalStudent"] = count($this->model->getTestStudentlistByBatch($batch));
        // 计算课程数
        $this->dataInfo["totalCourse"] = count($this->model->getTestCourselistByBatch($batch));
        // 计算某批次的场次数量
        $this->dataInfo["totalBatch"] = count($this->model->getBatchFlags($batch));
        // 计算需要的最大批次
        $this->dataInfo["maxBatch"] = $this->model->getBatchMaxFlag($batch);
        // 单科考试最大人数
        $this->dataInfo["maxStudent"] = $this->model->getBatchCourseStudentMaxSum($batch);



        // 计算考场数及考位数  todo:无效
        $data = $this->model->sqlFind("select count(*) as NCOUNT,sum(KW)as KW from TESTROOM where status=1");
        $this->dataInfo["totalRoom"] = $data["NCOUNT"];
        $this->dataInfo["totalKw"] = $data["KW"];


        if($this->_hasJson) {
            $this->ajaxReturn($this->dataInfo,"JSON");
        }

    }


    /**
     * 设置考试批次
     * @param null $year
     * @param null $term
     * @param null $classno
     * @param null $batch
     * @param null $flag
     * @param null $rows
     */
    public function setpici($year=null,$term=null,$classno=null,$batch=null,$flag=null,$rows=null){
        //修改批次
        if($this->_hasJson){
            $this->model->startTrans();
            $finalsExamModel = new ExamFinalsModel();
            foreach($rows as $val){
                $rst = $finalsExamModel->updateCourseBatch($val,$flag);
                if(is_string($rst) or !$rst){
                    $this->failedWithReport("更新失败！{$rst}");
                }
            }
            $this->model->commit();
            $this->successWithReport('修改成功！');
        }
        //获取列表数据
        if(REQTAG === 'getlist'){
            $finalsExamModel = new ExamFinalsModel();
            $list = $finalsExamModel->listCourseBatches($year,$term,$classno,$batch,$this->_pageDataIndex,$this->_pageSize);
            if(is_string($list)){
                $this->failedWithReport("查询失败！{$list}");
            }
            $this->ajaxReturn($list);
        }
        $this->display();
    }



}


function getSimilar($rs,&$arr,&$arrIndex,$count=1){

    $maxIndex = getMaxIndex($arrIndex); //取得最大房间号

    $_countRs = $rs;
    $reVal = array();

    if($rs>$maxIndex){          //todo:人数大于教室考位数考位数
        if($count>=3) return false;//如果大于第三次直接退出                //todo:已经不排了,三个考场都排满了
        $arrIndex[$maxIndex]--;
        $reVal[$count] = end($arr[$maxIndex]);
        $reVal[$count]['rs']=$maxIndex;
        array_pop($arr[$maxIndex]);
        $_reVal = getSimilar($rs-$maxIndex,$arr,$arrIndex,$count+1);
        if($_reVal===false) return false;
        else $reVal[$count+1] = $_reVal[$count+1];
    }

    while($_countRs<=$maxIndex){
        if(array_key_exists($_countRs,$arrIndex) && $arrIndex[$_countRs]>0){
            $reVal[$count] = end($arr[$_countRs]);
            $reVal[$count]['rs']=$rs;
            array_pop($arr[$_countRs]);
            $arrIndex[$_countRs]--;
            break;
        }
        $_countRs++;
    }

    return $reVal;
}


/**
 * 许：取得最大下标
 * 林：获取数组中最后一个值大于0的键，不存在获取数组中最后一个值大于0的键时返回false(如果值为false则直接返回false)
 * @param array $arrIndex 数组
 * @return mixed
 */
function getMaxIndex($arrIndex){
    $val = end($arrIndex);
    if($val>0) return key($arrIndex);

    while(true){
        $val = prev($arrIndex);
        if($val===false)
            return false;
        elseif($val>0)
            return key($arrIndex);
    }
    return false;
}