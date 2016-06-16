<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/


/**
 * Class ResultsAction
 */
class ResultsAction extends RightAction
{
    private $objPHPExcel;
    private $md;        //存放模型对象
    private $base;      //路径

    /**
     * @var InputAction
     */
    private $inputAction = null;

    /**
     *   学期课表
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new ResultModel();
        vendor("PHPExcel.PHPExcel");
        $this->objPHPExcel = new PHPExcel();
        $this->base='Results/';
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));



        $this->inputAction = new InputAction();
    }

	//个人成绩界面
    public function Results_personal($studentno=null){
        if(IS_POST){
            $this->inputAction->selectPersonalResultInfo($studentno);
        }
        $this->inputAction->pagePersonalResult();
    }
    public function Three_two($year=null,$term=null,$schoolno=null,$courseno=null){
        if(IS_POST){
            $this->inputAction->listResitSelect($year,$term,$schoolno,$courseno);
        }
        $this->inputAction->pageResitSelect();
    }
    public function Three_BKCJSR_StudentList($year=null,$term=null,$school='%',$courseno='%'){

        if(REQTAG === 'Four_one_daying_youbian'){
            $this->inputAction->pageResitInputForPrint($year,$term,$courseno);
            exit;
        }elseif(REQTAG === 'pageResitInputForPrint_datasourse'){
            $this->inputAction->listResitInputForPrint($year,$term,$courseno,$_POST['page']);
            exit;
        }

        if($this->_hasJson){
            if(isset($_GET['tag']) && ($_GET['tag'] == 'bkcjsource')){
                $this->inputAction->listResitStudent($year,$term,$courseno);
            }
            $count=$this->md->sqlFind($this->md->getSqlMap($_POST['Sqlpath']['count']),$_POST['bind']);
//            $str=$this->md->getSqlMap($_POST['Sqlpath']['count']);
            $data = array();
            if($data['total']=$count['ROWS']){
                $data['rows']=$this->md->sqlQuery($this->md->getSqlMap($_POST['Sqlpath']['select']),array_merge($_POST['bind'],array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+800)));
            }else{
                $data['rows']=array();
            }
            $this->ajaxReturn($data,'JSON');
            exit;
        }

        $this->assign('TT',$this->md->sqlFind($this->md->getSqlMap($this->base.'Three_two_daying_title.SQL'),array(':COURSENO'=>$_GET['COURSENO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'])));
        $this->assign('shuju',$_GET);
        $this->inputAction->pageResitInput($year,$term,$school,$courseno);
    }


    public function daying_query(){
        $start=($_POST['page2']-1)*120+1;
        $end=$_POST['page2']*120;

        $count=$this->md->sqlFind($this->md->getSqlMap($_POST['Sqlpath']['count']),$_POST['bind']);

        if($data['total']=$count['ROWS']){
            $data['rows']=$this->md->sqlQuery($this->md->getSqlMap($_POST['Sqlpath']['select']),array_merge($_POST['bind'],array(':start'=>$start,':end'=>$end)));
        }else{
            $data['total']=0;
            $data['rows']=array();
        }
        $data['lv']=$this->hegelv($_POST['bind'][':YEAR'],$_POST['bind'][':TERM'],$_POST['bind'][':COURSENO']);

        $this->ajaxReturn($data,'JSON');
    }


    public function Three_three_StudentList($year=null,$term=null,$coursegroupno=null,$scoretype=null){

        if( isset($_GET['list']) && (trim($_GET['list']) == 1)){
            $array=array();
            $count=$this->md->sqlFind($this->md->getSqlMap($this->base.'Three_three_count_StudentList.SQL'),
                array(':year'=>$_POST['bind'][':year'],':term'=>$_POST['bind'][':term'],':courseno'=>$_POST['bind'][':courseno']));

            if($count['ROWS'] > 0){
                $array['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Three_three_select_StudentList.SQL'),
                    array(':year'=>$_POST['bind'][':year'],':term'=>$_POST['bind'][':term'],':courseno'=>$_POST['bind'][':courseno'],':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+800));
            }else{
                $array['rows']=array();
            }
            $array['total'] = $count['ROWS'];
            $this->ajaxReturn($array,'JSON');
            exit;
        }
        $this->inputAction->pageFinalsInput($year,$term,$coursegroupno,$scoretype);
    }
    public function Three_three($year=null,$term=null,$teacherno=null){
        if(IS_POST){
            $this->inputAction->listFinalsSelect($year,$term,$teacherno);
        }
        $this->inputAction->pageFinalsSelect();
    }
    /**
     * @param null $rows
     * @param null $year
     * @param null $term
     * @param null $examdate
     * @param null $courseno
     */
    public function Three_two_sub($rows=null,$year=null,$term=null,$examdate=null,$courseno=null){
        if($this->_hasJson){
            $this->md->startTrans();
            $courseList=$this->md->sqlQuery("select qm from scores where year=:year and term =:term and courseno=:courseno and [group]=:gp",
                array(':year'=>$_POST['year'],':term'=>$_POST['term'],':courseno'=>substr($_POST['kh'],0,7),':gp'=>substr($_POST['kh'],7)));
            foreach($courseList as $val){
                if(trim($val['qm'])==''){
                    $this->md->rollback();
                    exit('请输完所有成绩并保存后再最终提交,现在不能最终提交');
                }
            }

            $bool=$this->md->sqlExecute("update scores
                set lock=1 where courseno=:courseno and [group]=:gp and year=:year and term=:term",
                array(':courseno'=>substr($_POST['kh'],0,7),':gp'=>substr($_POST['kh'],7),
                    ':year'=>$_POST['year'],':term'=>$_POST['term']));
            if($bool){
                $this->md->commit();
                exit('提交成功');
            }else{
                $this->md->rollback();
                exit('修改失败');
            }

        }

        //--  期末成绩获取  --//
        if ($_GET['tag'] == 'qmcjhq'){
            $this->inputAction->listFinalsInput($year,$term,$courseno);
        }

        //--  期末成绩输入 --//
        if ($_GET['tag'] == 'qmcjsr'){
            $this->inputAction->updateFinalsScoreInBatch($_POST['rows'],$_POST['sp'],$_POST['examtime']);
        }

        //--  补考成绩输入 --//
        if ($_GET['tag'] == 'bkcjsr'){
            $this->inputAction->updateResitScoreInBatch($rows,$year,$term,$examdate,$courseno);
        }
    }
    public function Three_three_daying($year=null,$term=null,$courseno=null,$page=1){
        if(IS_POST){
            $this->inputAction->listFinalInputForPrint($year,$term,$courseno,$page);
        }
        $this->inputAction->pageFinalsInputForPrint($year,$term,$courseno,$page);
    }
    public function kf_course($year=null,$term=null,$courseno=null){
        $this->inputAction->updateResitLockStatus($year,$term,$courseno);
    }
    public function Three_four($year=null,$term=null,$school=null){
        if(IS_POST){
            $this->inputAction->listCoursesWhichScoreInputness($year,$term,$school);
        }
        $this->inputAction->pageCoursesWhichScoreInputness();
    }
    public function Three_one($year=null,$term=null,$school=null){
        if(IS_POST){
            $this->inputAction->listCoursesWithOpen($year,$term,$school);
        }
        if(REQTAG === 'exportexcel'){
            $this->inputAction->exportCoursesWithOpen($year,$term,$school);
        }
        $this->inputAction->pageCoursesWithOpen();
    }
    public function one_excel($year,$term,$coureno){
        $this->inputAction->exportCoursesWithOpen($year,$term,$coureno);
    }
    public function Three_CJSR_StudentList(){

        if(!isset($_GET['page'])){
            $this->assign('page',1);
        }else{
            $this->assign('page',$_GET['page']);
        }
        $this->assign('course_info',$this->md->sqlFind("select schools.name as schoolname,'{$_GET['COURSENO']}' as coursenogroup,{$_GET['YEAR']} as year,({$_GET['YEAR']})+1 as year2 ,{$_GET['TERM']} as term,coursename from courses inner join schools on schools.school=courses.school where courseno=substring(('{$_GET['COURSENO']}'),1,7)"));
        $this->assign('teachername_info',$this->md->sqlFind($this->md->getSqlMap($this->base.'Three_one_Title_TeacherNAME.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        $this->assign('classname_info',$this->md->sqlFind($this->md->getSqlMap($this->base.'Three_one_Title_ClassNAME.SQL'),array(':COURSENO'=>$_GET['COURSENO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'])));

        $this->assign('renshu',$this->md->sqlFind("SELECT count(*) as xkrs FROM scores WHERE YEAR ={$_GET['YEAR']} AND TERM ={$_GET['TERM']} AND  COURSENO+[group]='{$_GET['COURSENO']}'"));
        $this->assign('shuju',$_GET);
        $this->display();
    }
    public function kf_cs($year,$term,$courses){
        $this->inputAction->updateFinalsLackStatusInBatch($year,$term,$courses);
    }

    public function Three_five($year=null,$term=null,$coursegroupno=null,$school=null){
        if(IS_POST){
            $this->inputAction->listRetakeSelect($year,$term,$coursegroupno,$school);
        }
        $this->inputAction->pageRetakeSelect();
    }
    public function Three_BYQBK_StudentList($year=null,$term=null,$coursegroupno=null,$schoolno=null,$scoretype=null,$lock=null){
        $this->inputAction->pageRetakeInput($year,$term,$coursegroupno,$scoretype);
    }

    public function Three_six($year=null,$term=null,$studentname=null){
        if(IS_POST){
            $this->inputAction->listAvoidSelect($year,$term,$studentname);
        }
        $this->inputAction->pageAvoidSelect();
    }
    public function add_miance($year=null,$term=null,$studentno=null,$name=null,$classname=null,$time=null,$reason=null){
        if(REQTAG === 'checkstudentno'){
            $this->inputAction->selectStudentInfo($studentno);
        }
        $this->inputAction->createAvoidRecord($year,$term,$studentno,$name,$classname,$time,$reason);
    }






















//TODO:151013-XXX 修订线 **************************************************************************************************************

    public function Four_three_daying(){
        $this->display();
    }


    //todo:
    public function huizongPrint(){
        if($this->_hasJson){
            $coursename=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectCourse.SQL'),
                array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

           // print_r($coursename);

            $data_List=array();

            //todo:查询出学分
            $credit=$this->md->sqlQuery("
SELECT STUDENTS.STUDENTNO as xh,STUDENTS.NAME AS xm,SELECTIONS.TOTALCREDITS,lsselection.xkxf as xkxf
FROM STUDENTS
LEFT OUTER JOIN
(
  SELECT STUDENTNO,SUM(COURSES.CREDITS) AS TOTALCREDITS
  FROM SCORES
  JOIN COURSES ON SCORES.COURSENO=COURSES.COURSENO
  WHERE YEAR=:Y1
  AND TERM=:T1
  AND (SCORES.EXAMSCORE>=60 OR SCORES.TESTSCORE ='优秀' OR SCORES.TESTSCORE ='良好' OR        SCORES.TESTSCORE ='及格' OR SCORES.TESTSCORE ='中等' OR SCORES.TESTSCORE ='合格')
  GROUP BY STUDENTNO
)AS SELECTIONS ON STUDENTS.STUDENTNO=SELECTIONS.STUDENTNO
left outer join
(
  SELECT STUDENTNO,SUM(COURSES.CREDITS) AS xkxf
  FROM SCORES
  JOIN COURSES ON SCORES.COURSENO=COURSES.COURSENO
  WHERE YEAR=:YEAR
  AND TERM=:TERM
  GROUP BY STUDENTNO
)AS lsSELECTION ON STUDENTS.STUDENTNO=lsSELECTION.STUDENTNO
WHERE STUDENTS.CLASSNO=:CLASSNO
ORDER BY STUDENTS.STUDENTNO	    ",array(':Y1'=>$_POST['YEAR'],':T1'=>$_POST['TERM'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));
           // echo '<pre>';
           // print_r($credit);
         // echo '====================credit==============================';
            //todo:查询出 这个班上的课程
            $course=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectCourse.SQL'),array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

           /// print_r($course);
            //todo:查询出 学生所对应的课程列表  和成绩
            $student=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectStudent_course.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));

            //print_r($student);
            $inum=count($credit);
            $jnum=count($student);

            //todo:循环学分列表
            for($i=0;$i<$inum;$i++){                            //todo:学分的
                $data_List[$i]=$credit[$i];
                for($j=0;$j<$jnum;$j++){                                                            //todo:课程列表和成绩(最多的那些)
                    if($credit[$i]['xh']!=$student[$j]['STUDENTNO']){
                        continue;
                    }

                    if(trim($student[$j]['TESTSCORE'])){              //todo:是要显示合格的

                        $strr='TESTSCORE';
                    }else{

                        $strr='em';


                    }
                    $data_List[$i][$student[$j]['COURSENOGROUP']]=$student[$j][$strr];

                }
            }
            $arr['total']=count($data_List);
            $arr['rows']=$data_List;
            $arr['coursename']=$coursename;

      /*      echo '<pre>';
            print_r($arr);*/
            echo json_encode($arr);
            exit;
        }
        $classname = $this->md->sqlFind("select CLASSNAME from CLASSES where CLASSNO = '{$_GET['CLASSNO']}';");
        $this->assign('year',$_GET['YEAR']);
        $this->assign('term',$_GET['TERM']);
        $this->assign('classname',$classname["CLASSNAME"]);
        $this->assign('classno',$_GET['CLASSNO']);
        $this->display();
    }


    //todo:个人成绩页面


    //todo:不及格名单查询
    public function Fail(){
        $this->display();
    }

    //todo:班级学期成绩汇总表
    public function summary(){
        if($this->_hasJson){
            $data_List=array();
            //todo:查询出学分
            $credit=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectCredit.SQL'),array(':Y1'=>$_POST['YEAR'], ':T1'=>$_POST['TERM'], ':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));

            //todo:查询出 这个班上的课程
            $course=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectCourse.SQL'),array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
            //todo:查询出 学生所对应的课程列表  和成绩
            $student=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_three_selectStudent_course.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));

            $inum=count($credit);
            $jnum=count($student);

            //todo:循环学分列表
            for($i=0;$i<$inum;$i++){                            //todo:学分的
                $data_List[$i]=$credit[$i];
                for($j=0;$j<$jnum;$j++){                                                            //todo:课程列表和成绩(最多的那些)
                    if($credit[$i]['xh']!=$student[$j]['STUDENTNO']){
                        continue;
                    }

                    if(trim($student[$j]['TESTSCORE'])){              //todo:是要显示合格的

                        $strr='TESTSCORE';
                    }else{

                        $strr='em';


                    }
                    $data_List[$i]['a'.$student[$j]['COURSENOGROUP']]=$student[$j][$strr];

                }
            }
            $arr['total']=count($data_List);
            $arr['rows']=$data_List;

           echo json_encode($arr);
           exit;

        }

        $this->display();
    }



//todo:班级学期积点分汇总表
    public function JDF_summary(){
        if($this->_hasJson){

            $data_List=array();
            //todo:查询出积点和 (每个学生一条的数据)
             $credit=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_Four_selectStudent_course.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));
            //todo:查询出 这个班上的课程
            $course=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_Four_selectCourse.SQL'),array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
            //todo:查询出 学生所对应的课程列表  和成绩
            $student=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_Four_selectJDF.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));



            $inum=count($credit);
            $jnum=count($student);

            //todo:循环学分列表
            for($i=0;$i<$inum;$i++){                            //todo:学分的
                $data_List[$i]=$credit[$i];
                for($j=0;$j<$jnum;$j++){                                                            //todo:课程列表和成绩(最多的那些)


                    if($credit[$i]['xh']!=$student[$j]['STUDENTNO']){
                        continue;
                    }


                    $data_List[$i]['a'.$student[$j]['COURSENOGROUP']]=$student[$j]['JDF'];

                }
            }
            $arr['total']=count($data_List);
            $arr['rows']=$data_List;
            echo json_encode($arr);
            exit;

        }

        $this->display();
    }


    //todo:辅修成绩查询
    public function one_six(){
        $this->display();
    }

    //todo:省级以上统考成绩查询
    public function CET4(){

        $this->xiala('schools','schools');
        $this->display();
    }




    //todo:班级素质学分排名
    public function class_Quality_Credit(){


        $this->display();
    }


    //todo:毕业前补考的页面
    public function  biye_bukao(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function shuchu($title){
        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename = $title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    //todo;查询成绩课程的页面
    public function Course_score(){


        if(isset($_POST['COURSENO2']) && $_POST['COURSENO2'] != ''){
            $bind=  array( ':COURSENO'=>doWithBindStr($_POST['COURSENO2']),':YEAR'=>$_POST['YEAR2'],':TERM'=>$_POST['TERM2'],':STUDENTNO'=>doWithBindStr($_POST['STUDENTNO2']));
            $data= $this->md->sqlQuery($this->md->getSqlMap('Results/One_nine_selectExcel.SQL'),  $bind);



            $title="成绩列表";
            //$this->objPHPExcel->getActiveSheet()->setTitle($title);  //移到后面
            //合并单元格
            $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:M1");   //todo:标题合并单元格
            //设置默认字体和大小
            $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
            $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(20);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(20);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
            //设置个别列内容居中
            $this->objPHPExcel->getActiveSheet()->getStyle("A:M")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置宽度
            //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

            //设置单元格字体加粗，居中样式
            $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
            $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

            $this->objPHPExcel->getActiveSheet()->getStyle("A1:M1")->applyFromArray($style);//字体样式
            $this->objPHPExcel->getActiveSheet()->getStyle("A2:M2")->applyFromArray($style);//字体样式
            //单元格内容写入
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:M2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


            $this->objPHPExcel->getActiveSheet()->setTitle($title);
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);

            $row=2;

            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学分");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"姓名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"修课方式");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"考试成绩");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"考查成绩");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"补考成绩");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"补考考查");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"积点分");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"重修");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"听课方式");
            foreach($data as $val){
                $row++;
                $this->objPHPExcel->getActiveSheet()->getStyle("A$row:M$row")->applyFromArray($style2);//字体样式
                //todo:边框设置
                $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:M$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
                $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['kh']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['km']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['xf']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['xh']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['xm']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['xkfs']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['kscj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kccj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['bkcj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['bkkc']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['jdf']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['cx']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['tkfs']));
            }


            $this->shuchu($title);
            exit;
        }
        $this->display();
    }





    //todo:学积分排名的页面
    public function XJF_order(){
        $this->display();
    }

    //todo:班级积点分排名的页面
    public function jidianfen_order(){

        $this->display();
    }


    //todo:健康的页面
    public function health(){
        $this->display();
    }


    //todo:素质汇总的页面
    public function suzhi_gather(){
        $this->display();
    }


    //todo:自主实践成绩查询
    public function practice(){
        $this->xiala('schools','schools');
        $this->display();
    }



    //todo:  Two  查询不及格的页面
    public function Two_Fail(){
        $this->display();
    }

    //todo:  Two  课程成绩分析
    public function Two_Course_CJFX(){
        if($this->_hasJson){
            $bind=array(':CONE'=>doWithBindStr($_POST['COURSENO']),':YONE'=>$_POST['YEAR'],':TONE'=>$_POST['TERM'],
                ':CTWO'=>doWithBindStr($_POST['COURSENO']),':YTWO'=>$_POST['YEAR'],':TTWO'=>$_POST['TERM'],
                ':CTHREE'=>doWithBindStr($_POST['COURSENO']),':YTHREE'=>$_POST['YEAR'],':TTHREE'=>$_POST['TERM'],
                ':CFOUR'=>doWithBindStr($_POST['COURSENO']),':YFOUR'=>$_POST['YEAR'],':TFOUR'=>$_POST['TERM'],
                ':CFIVE'=>doWithBindStr($_POST['COURSENO']),':YFIVE'=>$_POST['YEAR'],':TFIVE'=>$_POST['TERM'],
                ':CSIX'=>doWithBindStr($_POST['COURSENO']),':YSIX'=>$_POST['YEAR'],':TSIX'=>$_POST['TERM'],
                ':CSEVEN'=>doWithBindStr($_POST['COURSENO']),':YSEVEN'=>$_POST['YEAR'],':TSEVEN'=>$_POST['TERM'],
                ':CEIGHT'=>doWithBindStr($_POST['COURSENO']),':YEIGHT'=>$_POST['YEAR'],':TEIGHT'=>$_POST['TERM']);
            $leng= $this->md->sqlQuery($this->md->getSqlMap($_POST['Sqlpath']['count']),$bind);
            $arr= $this->md->sqlQuery($this->md->getSqlMap($_POST['Sqlpath']['select']),array_merge($bind,array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));

           $length=count($arr);
            for($i=0;$i<$length;$i++){
                $arr[$i]['yxbl']=round($arr[$i]['yxrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['lhbl']=round($arr[$i]['lhrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['zdbl']=round($arr[$i]['zdrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['jgbl']=round($arr[$i]['jgrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['bjgbl']=round($arr[$i]['bjgrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['qkbl']=round($arr[$i]['qkrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['tgbl']=$arr[$i]['yxbl']+$arr[$i]['lhbl']+$arr[$i]['zdbl']+$arr[$i]['jgbl'];
            }
            $array['total']=$leng[0]['ROWS'];
            $array['rows']=$arr;
            $this->ajaxReturn($array,'JSON');
            exit;

        }
        $this->display();
    }




    //todo Two学生必修课不及格列表
    public function Two_bujige(){
        if($this->_hasJson){
            $array=  array(':CONE'=>doWithBindStr($_POST['COURSENO']),':YONE'=>$_POST['YEAR'],':TONE'=>$_POST['TERM'],
                ':CTWO'=>doWithBindStr($_POST['COURSENO']),':YTWO'=>$_POST['YEAR'],':TTWO'=>$_POST['TERM'],
                ':CTHREE'=>doWithBindStr($_POST['COURSENO']),':YTHREE'=>$_POST['YEAR'],':TTHREE'=>$_POST['TERM'],
                ':CFOUR'=>doWithBindStr($_POST['COURSENO']),':YFOUR'=>$_POST['YEAR'],':TFOUR'=>$_POST['TERM'],
                ':CFIVE'=>doWithBindStr($_POST['COURSENO']),':YFIVE'=>$_POST['YEAR'],':TFIVE'=>$_POST['TERM'],
                ':CSIX'=>doWithBindStr($_POST['COURSENO']),':YSIX'=>$_POST['YEAR'],':TSIX'=>$_POST['TERM'],
                ':CSEVEN'=>doWithBindStr($_POST['COURSENO']),':YSEVEN'=>$_POST['YEAR'],':TSEVEN'=>$_POST['TERM'],
                ':CEIGHT'=>doWithBindStr($_POST['COURSENO']),':YEIGHT'=>$_POST['YEAR'],':TEIGHT'=>$_POST['TERM']);
            $arr= $this->md->sqlQuery($this->md->getSqlMap('Results/Two_two_select.SQL'),
                $array);

            $length=count($arr);
            $xuankerenshu=0;            //todo:选课人数
            $youxiurenshu=0;            //todo:优秀人数
            $lianghaorenshu=0;          //todo:良好人数
            $zhongdengrenshu=0;         //todo:中等人数
            $jigerenshu=0;              //todo:及格人数
            $bujigerenshu=0;            //todo:不及格人数
            $quekaorenshu=0;            //todo:缺考人数
            for($i=0;$i<$length;$i++){
                $xuankerenshu+=$arr[$i]['xkrs'];
                $youxiurenshu+=$arr[$i]['yxrs'];
                $lianghaorenshu+=$arr[$i]['lhrs'];
                $zhongdengrenshu+=$arr[$i]['zdrs'];
                $jigerenshu+=$arr[$i]['jgrs'];
                $bujigerenshu+=$arr[$i]['bjgrs'];
                $quekaorenshu+=$arr[$i]['qkrs'];
                $arr[$i]['yxbl']=round($arr[$i]['yxrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['lhbl']=round($arr[$i]['lhrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['zdbl']=round($arr[$i]['zdrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['jgbl']=round($arr[$i]['jgrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['bjgbl']=round($arr[$i]['bjgrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['qkbl']=round($arr[$i]['qkrs']/$arr[$i]['xkrs']*100,2);
                $arr[$i]['tgbl']=$arr[$i]['yxbl']+$arr[$i]['lhbl']+$arr[$i]['zdbl']+$arr[$i]['jgbl'];
            }
            $arr2['kh']='合计';
            $arr2['xkrs']=$xuankerenshu;
            $arr2['yxrs']=$youxiurenshu;
            $arr2['lhrs']=$lianghaorenshu;
            $arr2['zdrs']=$zhongdengrenshu;
            $arr2['jgrs']=$jigerenshu;
            $arr2['bjg']=$bujigerenshu;
            $arr2['qkrs']=$quekaorenshu;
            $arr2['yxbl']=round($youxiurenshu/$xuankerenshu*100,2);
            $arr2['lhbl']=round($lianghaorenshu/$xuankerenshu*100,2);
            $arr2['zdbl']=round($zhongdengrenshu/$xuankerenshu*100,2);
            $arr2['jgbl']=round($jigerenshu/$xuankerenshu*100,2);
            $arr2['bjgbl']=round($bujigerenshu/$xuankerenshu*100,2);
            $arr2['qkbl']=round($quekaorenshu/$xuankerenshu*100,2);
            $arr2['tgbl']=$arr2['yxbl']+$arr2['lhbl']+$arr2['zdbl']+$arr2['jgbl'];
            $array2['total']=$length;
            $array2['rows']=array($arr2);
            $this->ajaxReturn($array2,'JSON');
            exit;
        }
       $this->xiala('schools','schools');
        $this->display();
    }


    //todo: Two  退学警告名单
    public function Two_Tuixue(){
        if($this->_hasJson){
            $array=  array(':CONE'=>$_POST['COURSENO'],':YONE'=>$_POST['YEAR'],':TONE'=>$_POST['TERM'],
                ':CTWO'=>$_POST['COURSENO'],':YTWO'=>$_POST['YEAR'],':TTWO'=>$_POST['TERM'],
                ':CTHREE'=>$_POST['COURSENO'],':YTHREE'=>$_POST['YEAR'],':TTHREE'=>$_POST['TERM'],
                ':CFOUR'=>$_POST['COURSENO'],':YFOUR'=>$_POST['YEAR'],':TFOUR'=>$_POST['TERM'],
                ':CFIVE'=>$_POST['COURSENO'],':YFIVE'=>$_POST['YEAR'],':TFIVE'=>$_POST['TERM'],
                ':CSIX'=>$_POST['COURSENO'],':YSIX'=>$_POST['YEAR'],':TSIX'=>$_POST['TERM'],
                ':CSEVEN'=>$_POST['COURSENO'],':YSEVEN'=>$_POST['YEAR'],':TSEVEN'=>$_POST['TERM'],
                ':CEIGHT'=>$_POST['COURSENO'],':YEIGHT'=>$_POST['YEAR'],':TEIGHT'=>$_POST['TERM']);
           $arr= $this->md->sqlQuery($this->md->getSqlMap('Results/Two_three_select.SQL'),$array);
            $length=count($arr);
                      $xuankerenshu=0;            //todo:选课人数
                      $youxiurenshu=0;            //todo:优秀人数
                      $lianghaorenshu=0;          //todo:良好人数
                      $zhongdengrenshu=0;         //todo:中等人数
                      $jigerenshu=0;              //todo:及格人数
                      $bujigerenshu=0;            //todo:不及格人数
                      $quekaorenshu=0;            //todo:缺考人数
                      for($i=0;$i<$length;$i++){
                          $xuankerenshu+=$arr[$i]['xkrs'];
                          $youxiurenshu+=$arr[$i]['yxrs'];
                          $lianghaorenshu+=$arr[$i]['lhrs'];
                          $zhongdengrenshu+=$arr[$i]['zdrs'];
                          $jigerenshu+=$arr[$i]['jgrs'];
                          $bujigerenshu+=$arr[$i]['bjgrs'];
                          $quekaorenshu+=$arr[$i]['qkrs'];
                          $arr[$i]['yxbl']=round($arr[$i]['yxrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['lhbl']=round($arr[$i]['lhrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['zdbl']=round($arr[$i]['zdrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['jgbl']=round($arr[$i]['jgrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['bjgbl']=round($arr[$i]['bjgrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['qkbl']=round($arr[$i]['qkrs']/$arr[$i]['xkrs']*100,2);
                          $arr[$i]['tgbl']=$arr[$i]['yxbl']+$arr[$i]['lhbl']+$arr[$i]['zdbl']+$arr[$i]['jgbl'];
                      }
                      $arr2['kh']='合计';
                      $arr2['xkrs']=$xuankerenshu;
                      $arr2['yxrs']=$youxiurenshu;
                      $arr2['lhrs']=$lianghaorenshu;
                      $arr2['zdrs']=$zhongdengrenshu;
                      $arr2['jgrs']=$jigerenshu;
                      $arr2['bjg']=$bujigerenshu;
                      $arr2['qkrs']=$quekaorenshu;
                      $arr2['yxbl']=round($youxiurenshu/$xuankerenshu*100,2);
                      $arr2['lhbl']=round($lianghaorenshu/$xuankerenshu*100,2);
                      $arr2['zdbl']=round($zhongdengrenshu/$xuankerenshu*100,2);
                      $arr2['jgbl']=round($jigerenshu/$xuankerenshu*100,2);
                      $arr2['bjgbl']=round($bujigerenshu/$xuankerenshu*100,2);
                      $arr2['qkbl']=round($quekaorenshu/$xuankerenshu*100,2);
                      $arr2['tgbl']=$arr2['yxbl']+$arr2['lhbl']+$arr2['zdbl']+$arr2['jgbl'];
                      $array2['total']=$length;
                      $array2['rows']=array($arr2);
                      $this->ajaxReturn($array2,'JSON');
            exit;
        }
        $this->xiala('schools','schools');
        $this->display();
    }




    //todo: Two  成绩分析 (次)
    public function Two_analyse_ci(){
        $this->display();
    }


    //todo:  Two 成绩分析 (专业累计)
    public function Two_analyse_major(){
        $dpart = '';
        $rtn = array();
        $cutoffscore = null;

//select distinct 学号 from 英语B级统考成绩 $partone
        if(isset($_POST['tag']) ){
            $tag = trim($_POST['tag']);
            switch($tag){
                case 'cpt':
                    $dpart = " select distinct 学号 from 计算机统考成绩 where 等级 like '%一级%' ";
                    $cutoffscore = 60;
                    break;
                case 'appa':
                    $dpart = 'select distinct 学号 from 英语A级统考成绩';
                    $cutoffscore = 60;
                    break;
                case 'appb':
                    $dpart = 'select distinct 学号 from 英语B级统考成绩';
                    $cutoffscore = 60;
                    break;
                case 'enthree':
                    $dpart = 'select distinct 学号 from 英语CET3统考成绩';
                    $cutoffscore = 60;
                    break;
                case 'enfour':
                    $dpart = 'select distinct 学号 from 英语CET4统考成绩';
                    $cutoffscore = 425;
                    break;
                case 'enfive':
                    $dpart = 'select distinct 学号 from 英语CET6统考成绩';
                    $cutoffscore = 425;
                    break;
                default:;
            }
            $sql = "select * from(select row_number() over(order by temp.name) as row,temp.name as zy,temp.total cjrs,isnull(temp3.pass,0) tgrs from (select majorcode.name,count(*) total from personal
inner join students on students.studentno=personal.studentno
inner join majorcode on majorcode.code=personal.major
inner join
(
$dpart
group by 学号
) as temp2 on temp2.学号=students.studentno
where substring(students.classno,1,2)=:GONE and students.studentno not like 'PT%'
group by majorcode.name ) as temp
left outer join (
select majorcode.name,count(*) pass from personal
inner join students on students.studentno=personal.studentno
inner join majorcode on majorcode.code=personal.major
inner join
(
$dpart
group by 学号
having max(成绩)>= $cutoffscore
) as temp2 on temp2.学号=students.studentno
where substring(students.classno,1,2)=:GTWO and students.studentno not like 'PT%'
group by majorcode.name
) as temp3 on temp3.name=temp.name
) as b";
            $grade = trim($_POST['grade']);
            $bind = array(
                ':GONE'=>$grade,
                ':GTWO'=>$grade,
            );
            $rtn['rows'] = $this->md->sqlQuery($sql,$bind);

//            echo "<pre>";
//                var_dump($this->md->getDbError(),$this->md->getLastSql(),$bind,$sql);
//            var_dump($rtn['rows'] );
//            exit;
            $rtn['total'] = count($rtn['rows']);

            $this->ajaxReturn($rtn,'JSON');
        }
        $this->display();
    }

    /**
     * CET-4按照年纪和班级统计
     */
    public function cetFourAnalyse(){
//INSERT INTO METHODS  VALUES ('RAC4', 'A', '毕业生CET-4考试成绩统计', 'Results/Results/cetFourAnalyse');

        if(isset($_GET['tag']) && trim($_GET['tag']) == 'getlist'){
            $rtn = array();
            $majorno = trim($_POST['major']);
            $gradeno = trim($_POST['grade']);
            $bind = array(
                ':gradeone'=>$gradeno,
                ':gradetwo'=>$gradeno,
                ':majornoone'=>$majorno,
                ':gradethree'=>$gradeno,
                ':majornotwo'=>$majorno,
            );
            $sql = "select * from
(select row_number() over(order by temp.name) as row,temp.name as zy,temp.total cjrs,isnull(temp3.pass,0) tgrs ,:gradeone as nj
from
(select majorcode.name,count(*) total from personal
inner join students on students.studentno=personal.studentno
inner join majorcode on majorcode.code=personal.major
inner join
	(
	select distinct 学号 from 英语CET4统考成绩
	group by 学号
	) as temp2 on temp2.学号=students.studentno
where substring(students.classno,1,2) like :gradetwo and students.studentno not like 'PT%'
and PERSONAL.MAJOR LIKE :majornoone
group by majorcode.name ) as temp
left outer join (
	select majorcode.name,count(*) pass from personal
	inner join students on students.studentno=personal.studentno
	inner join majorcode on majorcode.code=personal.major
	inner join
	(
		select distinct 学号 from 英语CET4统考成绩
		group by 学号
		having max(成绩)>=425
	) as temp2 on temp2.学号=students.studentno
	where substring(students.classno,1,2) like :gradethree and students.studentno not like 'PT%'
and PERSONAL.MAJOR LIKE :majornotwo
	group by majorcode.name
	) as temp3 on temp3.name=temp.name
) as b";
            $rtn['rows'] = $this->md->sqlQuery($sql,$bind);
//            echo "<pre>";
//            var_dump($rtn,$sql,$bind,$this->md->getDbError());
            $rtn['total']= count($rtn['rows']);
            $this->ajaxReturn($rtn,'JSON');
        }

        $this->assign('majors',$this->md->sqlQuery('SELECT * from MAJORCODE'));
        $this->display();
    }


    //todo;成绩输入页面
    public function Three_CJSR(){
        if($this->_hasJson){

        }
        $this->assign('school',$this->md->sqlFind("select SCHOOL from TEACHERS where TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'"));
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->xiala('schools','schools');
        $this->display();
    }

    //todo:开放课程



    //todo:查看和开放课程 的提交到数据库操作




    //todo:成绩输入页面------>任课教师期末成绩输入---->学生列表页面









    //todo;成绩输入页面---->开放与查看课程-->学生列表



    //todo;成绩输入页面---->补考成绩输入-->学生列表


    //todo:成绩输入页面-->毕业前补考->学生列表



    public function Four_one(){
        $this->xiala('schools','schools');
        $this->display();
    }

    //todo:打印页面
    public function Four_one_daying(){
        if(!isset($_GET['page'])){
            $this->assign('page',1);
        }else{
            $this->assign('page',$_GET['page']);
        }
        //todo:标题--->课程信息
        $this->assign('haha',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_four_title_course_info.SQL'),array(':cone'=>$_GET['COURSENO'],':yone'=>$_GET['YEAR'],':ytwo'=>$_GET['YEAR'],':term'=>$_GET['TERM'],':ctwo'=>$_GET['COURSENO'])));
        //todo:标题--->教师姓名等信息
        $this->assign('haha2',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_teachername.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        //todo:标题--->班级名字
        $this->assign('haha3',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_youbian_Classname.SQL'),array(':COURSENO'=>$_GET['COURSENO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'])));

        $this->assign('shuju',$_GET);
        $this->assign('year2',$_GET['YEAR']+1);
        $this->display();
    }

    //todo:打印页面 ----右边



    //todo:
    public function Four_two(){
        $this->xiala('schools','schools');
        $this->display();
    }


    public function Four_two_daying(){
        $this->setpage();
        //todo:标题--->课程信息
        $this->assign('haha',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        //todo:标题--->教师姓名等信息
        $this->assign('haha2',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_teachername.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        $this->assign('shuju',$_GET);

        $this->assign('year2',$_GET['YEAR']+1);
        $this->display();
    }

    public function Four_two_daying_youbian(){
        $this->setpage();
        //todo:标题--->课程信息
       $this->assign('haha',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        //todo:标题--->教师姓名等信息
       $this->assign('haha2',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_teachername.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        //todo:标题--->班级名字
       $this->assign('haha3',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_youbian_Classname.SQL'),array(':COURSENO'=>$_GET['COURSENO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'])));

        $this->assign('shuju',$_GET);
        $this->assign('year2',$_GET['YEAR']+1);
        $this->display();
    }


    public function setpage(){
        if(!isset($_GET['page'])){
            $this->assign('page',1);
        }else{
            $this->assign('page',$_GET['page']);
        }
    }


    //todo:毕业前补考成绩
    public function Four_four(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function Four_four_daying(){
        $this->setpage();
        //todo:标题--->课程信息
        $this->assign('haha',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_four_title_course_info.SQL'),array(':cone'=>$_GET['COURSENO'],':yone'=>$_GET['YEAR'],':ytwo'=>$_GET['YEAR'],':term'=>$_GET['TERM'],':ctwo'=>$_GET['COURSENO'])));
        //todo:标题--->教师姓名等信息
      $this->assign('haha2',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_teachername.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
       $this->assign('shuju',$_GET);

        $this->assign('year2',$_GET['YEAR']+1);

        $this->display();
    }


    public function Four_four_daying_youbian(){
        if(!isset($_GET['page'])){
            $this->assign('page',1);
        }else{
            $this->assign('page',$_GET['page']);
        }
        //todo:标题--->课程信息
        $this->assign('haha',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_four_title_course_info.SQL'),array(':cone'=>$_GET['COURSENO'],':yone'=>$_GET['YEAR'],':ytwo'=>$_GET['YEAR'],':term'=>$_GET['TERM'],':ctwo'=>$_GET['COURSENO'])));
        //todo:标题--->教师姓名等信息
      $this->assign('haha2',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_teachername.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$_GET['COURSENO'])));
        //todo:标题--->班级名字
     $this->assign('haha3',$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_two_Title_youbian_Classname.SQL'),array(':COURSENO'=>$_GET['COURSENO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'])));

        $this->assign('shuju',$_GET);
        $this->assign('year2',$_GET['YEAR']+1);
        $this->display();
    }

    /**
     * @function 获取列表JSON数据
     * @param $bind Array
     * @param $countpath String
     * @param $selectpath String
     * @return mixed Array
     */
    private function getListJson($bind,$countpath,$selectpath,$limit = null){
        $total = $this->md->sqlQuery($this->md->getSqlMap($countpath),$bind);
        $json['total'] = $total[0]['ROWS'];
        if($json['total']>0){
            $sql = $this->md->getSqlMap($selectpath);
            $bind[':start'] = $this->_pageDataIndex;
            $bind[':end'] = $this->_pageDataIndex + ($limit?$limit:$this->_pageSize);
            $json["rows"] = $this->md->sqlQuery($sql, $bind);
        }else{
            $json["rows"] = array();
        }
        $json['total'] = count($json["rows"]);
        return $json;
    }



    

    public function BYQ_chengjiUpdate(){
       $this->md->startTrans();
        foreach($_POST['bind']['rows'] as $key => $val){
            $scsql = " update scores set examscore=:examscore,testscore=:testscore,[date]=getdate(),edate=:edate,lock=1
                    from scores where scores.RECNO=:recno and lock = 0 ";
            $scbind = array(":examscore"=>trim($val['examscore']),":testscore"=>trim($val['testscore']),":edate"=>$_POST['kaoshiriqi'],":recno"=>trim($key));
            $boolsc = $this->md->sqlExecute($scsql,$scbind);
            if($boolsc < 1){
                $this->md->rollback();
                exit("成绩修改出错了！");
            }
        }
        $this->md->commit();
        exit('修改成功');
    }


    public function excel22(){
        //TaskMonitorModel::init(session("S_USER_NAME"), 'Excel导出');
        ini_set("max_execution_time", "1800");
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(24);
        //todo:设置单元格字体加粗，居中样式
        $titleStyle=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
       //todo:垂直居中
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

        // 设置文件属性
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Documsent")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        if($_REQUEST['tabletype']==1)$title=array('宁 波 城 市 职 业 技 术 学 院 毕 业 生 成 绩 总 表','宁 波 大 学 毕 业 生 成 绩 总 表');
        else $title=array('宁 波 城 市 职 业 技 术 学 院 平 时 成 绩 总 表','宁 波 大 学 平 时 成 绩 总 表');
        $rowFontSize='';//设置字体大小
        $rowNum=0;//根据字体大小设置显示行数
        $rowheight = 0;//行高
        $biye=false;
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.8);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.7);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0);
        switch($_GET['fontsize']){
            case 'big';
                $rowFontSize=11;
                $rowNum=34;
                $rowheight=17.9;
                break;
            case 'normal':
                $rowFontSize=10;
                $rowNum=46;
                $rowheight=13.2;
                break;
            case 'small':
                $rowFontSize=9;
                $rowNum=56;
                $rowheight=11;
                break;
        }
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($rowheight);

        // 重命名工作表名称
        $objPHPExcel->getActiveSheet()->setTitle('成绩表');

        //设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);


        //todo:判断是毕业成绩 还是 平时成绩
        if($_GET['tabletype']=='1'){            //todo:毕业成绩
            $biye=array();
            $arr=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Excel_biye.SQL'),array(':STUDENTNO'=>$_GET['STUDENTNO'],':CLASSNO'=>$_GET['CLASSNO']));
            foreach($arr as $val){
                $biye[$val['STUDENTNO']]=$val;
            }
            $biyepd=true;
        }

        //todo:查询出要导出的数据-->每个学生的课程
       $content=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Excel_one.SQL'),array(':STUDENTNO'=>doWithBindStr($_GET['STUDENTNO']),':CLASSNO'=>doWithBindStr($_GET['CLASSNO'])));
        //todo:查询出每个学生的信息
       $student=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Excel_two.SQL'),array(':STUDENTNO'=>doWithBindStr($_GET['STUDENTNO']),':CLASSNO'=>doWithBindStr($_GET['CLASSNO'])));
        //todo:查询打印的老师的姓名
       $teachername=$this->md->sqlFind($this->md->getSqlMap($this->base.'Excel_three.SQL'),array(':USERNAME'=>$_SESSION['S_USER_NAME']));
        //todo:查询出 创新学分
       $chuangxin=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Excel_chuangxin.SQL'),array(':STUDENTNO'=>doWithBindStr($_GET['STUDENTNO']),':CLASSNO'=>doWithBindStr($_GET['CLASSNO'])));

        $chuangxinarr=array();
       //todo:组合创新技能学分
        foreach($chuangxin as $key=>$val){
            $chuangxinarr[$val['STUDENTNO']]=$val['TOTAL'];
        }

        //todo:打印日期
        $data=date('Y-m-d');
        $arr=array();
        $arr2=array();

       //todo:组合数据吧  按学号分组
        foreach($content as $val){
            if(!is_array($arr[$val['studentno']])){
                $arr[$val['studentno']]=array();
            }
            //todo: 按学年学期分组
            if(!is_array($arr[$val['studentno']][$val['year'].'_'.$val['term']])){
                $arr[$val['studentno']][$val['year'].'_'.$val['term']]=array();
            }
            array_push($arr[$val['studentno']][$val['year'].'_'.$val['term']],$val);

        }
        //todo:设置单元格字体加粗，居中样式
        $titleStyle=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));


        $count = 0;
        //TaskMonitorModel::run(session("S_USER_NAME"),"Excel导出", count($student)-1);


        $currentIndex=1;//标记真实的当前的行数
        foreach($student as $val){
            /*- 设置大标题 -*/
            $currentStudentIndex=1;//相对每个学生的当前行数 - 学生浮标
            $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:J$currentIndex");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex",$title[$_GET['title']]);
            $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize(17);//设置A1字体大小
//            $objPHPExcel->getActiveSheet()->getStyle("A$num")->getFont()->setName(iconv('UTF-8','GB2312','隶书'));//**设置无效..
            $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight(20);//设置当前行行高
            $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);//设置学年学期字体加粗
            $currentIndex++;


            /*- 设置学生信息第一、二行,学生浮标不自增 -*/
            $studentname =  trim($val['NAME']);
            $degree = trim($val['degree']);
            if($biyepd){//判断是成绩类型
                //毕业生成绩总表
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:J$currentIndex");   //标题合并单元格
                $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight($rowFontSize+3);//设置个别行高
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","学号:{$val['STUDENTNO']}   姓名:{$studentname}     学位:$degree     结论:{$biye[$val['STUDENTNO']]['byj']} 毕业证书号:{$biye[$val['STUDENTNO']]['zsbh']}");
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                $currentIndex++;
                $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:J$currentIndex");   //todo:标题合并单元格
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight($rowFontSize+3);//设置个别行高
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","学院:".($_REQUEST["title"]==1?"职业技术教育学院":$val['SCHOOLNAME'])." 专业名:{$biye[$val['STUDENTNO']]['zy']} 班级:{$val['CLASSNAME']}");
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                $currentIndex++;
            }else{
                //平时学生成绩总表
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I$currentIndex","");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J$currentIndex","");
                $currentIndex++;
                $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:J$currentIndex");   //todo:标题合并单元格
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight($rowFontSize+3);//设置个别行高
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","学号:{$val['STUDENTNO']} 姓名:{$val['NAME']} 学院:".($_REQUEST["title"]==1?"职业技术教育学院":$val['SCHOOLNAME'])." 班级:{$val['CLASSNAME']}");
                //todo:设置边框
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                $currentIndex++;
            }
//            $panduan=false;
//
//            $demoarr=array_values($arr[$val['STUDENTNO']]);                //todo:按自然下标取得值
//            $demoarr2=array_keys($arr[$val['STUDENTNO']]);                  //todo:按自然下标取得键


            /*- 设置学生成绩部分 遍历课程 -*/
            $toBeContinued=true;//决定是否在标题部分加入“续”字
            foreach($arr[$val['STUDENTNO']] as $k=>$v){

                $year_term=explode('_',$k);
                $year_term_two=$year_term[0]+1;

                //设置学生第几学年学期的标题部分 ， 学生浮标自增2
                if($currentStudentIndex > $rowNum){
                    //在第二页设置
//                    varsPrint(__LINE__,$currentIndex,$rowNum,$year_term[0],$year_term[1],$val['STUDENTNO']);ex
                    $objPHPExcel->getActiveSheet(0)->mergeCells("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),"$year_term[0]--{$year_term_two}学年 第{$year_term[1]}学期");
                    $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);
                    $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J".($currentIndex-$rowNum))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                    $currentIndex++;
                    $currentStudentIndex++;
                    $objPHPExcel->getActiveSheet(0)->getStyle("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle("A".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("B".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("C".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("D".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("E".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小

                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),"类别");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".($currentIndex-$rowNum),"课程名称");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".($currentIndex-$rowNum),"学分");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".($currentIndex-$rowNum),"成绩");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".($currentIndex-$rowNum),"补考");

                    $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("G".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("H".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("I".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("J".($currentIndex-$rowNum))->applyFromArray($titleStyle);

                }else{
                    //第一页设置
                    $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:E$currentIndex");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","$year_term[0]--{$year_term_two}学年 第{$year_term[1]}学期");
                    $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);
                    $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                    $currentIndex++;
                    $currentStudentIndex++;

                    $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("B$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("C$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("D$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("E$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小

                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","类别");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$currentIndex","课程名称");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C$currentIndex","学分");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$currentIndex","成绩");
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E$currentIndex","补考");

                    $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("B$currentIndex")->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("C$currentIndex")->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("D$currentIndex")->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getStyle("E$currentIndex")->applyFromArray($titleStyle);

                }
                $currentIndex++;
                $currentStudentIndex++;

                //设置成绩部分
                foreach($v as $cc){
                    $kscj=trim($cc['kscj'])=='0'?$cc['kccj']:$cc['kscj'];

                    if($currentStudentIndex > $rowNum && $toBeContinued){
                        //第二页设置
//                        varsPrint(__LINE__,$currentIndex,$rowNum,$year_term[0],$year_term[1],$val['STUDENTNO']);exit;
                        $objPHPExcel->getActiveSheet(0)->mergeCells("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum));
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),"$year_term[0]--{$year_term_two}学年 第{$year_term[1]}学期(续)");
                        $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);
                        $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $currentIndex++;
                        $currentStudentIndex++;
                        $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                        $objPHPExcel->getActiveSheet()->getStyle("G".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                        $objPHPExcel->getActiveSheet()->getStyle("H".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                        $objPHPExcel->getActiveSheet()->getStyle("I".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                        $objPHPExcel->getActiveSheet()->getStyle("J".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),"类别");
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".($currentIndex-$rowNum),"课程名称");
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".($currentIndex-$rowNum),"学分");
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".($currentIndex-$rowNum),"成绩");
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".($currentIndex-$rowNum),"补考");
                        $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $objPHPExcel->getActiveSheet()->getStyle("G".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $objPHPExcel->getActiveSheet()->getStyle("H".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $objPHPExcel->getActiveSheet()->getStyle("I".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $objPHPExcel->getActiveSheet()->getStyle("J".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                        $currentIndex++;
                        $currentStudentIndex++;
                        $toBeContinued=false;

                    }

                    $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("B$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("C$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("D$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("E$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("F$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("G$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("H$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("I$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小
                    $objPHPExcel->getActiveSheet()->getStyle("J$currentIndex")->getFont()->setSize($rowFontSize);//设置A1字体大小



                    if($currentStudentIndex>$rowNum){
                        //todo:设置边框
                        $objPHPExcel->getActiveSheet(0)->getStyle("A".($currentIndex-$rowNum).":J".($currentIndex-$rowNum))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),$cc['xkfs']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".($currentIndex-$rowNum),$cc['km']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".($currentIndex-$rowNum),$cc['xf']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".($currentIndex-$rowNum),$kscj);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".($currentIndex-$rowNum),$cc['bkks'] );

                    }else{
                        //todo:设置边框
                        $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex",$cc['xkfs']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$currentIndex",$cc['km']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C$currentIndex",$cc['xf']);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$currentIndex",$kscj);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E$currentIndex",$cc['bkks'] );
                    }
                    $currentIndex++;
                    $currentStudentIndex++;
                }
            }

            /*- 打印创新学分部分  只显示总分 -*/
            if($currentStudentIndex>$rowNum){
                $objPHPExcel->getActiveSheet(0)->mergeCells("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum));   //todo:标题合并单元格
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),'创新、技能、素质学分共计'.$chuangxinarr[$val['STUDENTNO']].'分');
                $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->getFont()->setSize($rowFontSize);//设置A1字体大小
                $objPHPExcel->getActiveSheet()->getStyle("F".($currentIndex-$rowNum))->applyFromArray($titleStyle);
                $objPHPExcel->getActiveSheet(0)->getStyle("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $currentIndex++;
                $currentStudentIndex++;
                $objPHPExcel->getActiveSheet(0)->mergeCells("F".($currentIndex-$rowNum).":J".($currentIndex-$rowNum));   //todo:标题合并单元格
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".($currentIndex-$rowNum),'--以下空白--');

                $currentIndex++;
                $currentStudentIndex++;
            }else{
                $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:E$currentIndex");   //todo:标题合并单元格
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex",'创新、技能、素质学分共计'.$chuangxinarr[$val['STUDENTNO']].'分');
                $objPHPExcel->getActiveSheet()->getStyle("A".($currentIndex))->getFont()->setSize($rowFontSize);//设置A1字体大小
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->applyFromArray($titleStyle);
                $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $currentIndex++;
                $currentStudentIndex++;
                $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:E$currentIndex");
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex",'--以下空白--');
                $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $currentIndex++;
                $currentStudentIndex++;
            }
//            varsPrint($currentStudentIndex , $rowNum);exit;
            //成绩数目过少，填充
            if($currentStudentIndex < $rowNum){
                $c = $rowNum - $currentStudentIndex + 1;//填充数目
                for($i = 0 ; $i < $c;$i++){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex",' ');
                    $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $currentIndex++;
                    $currentStudentIndex++;
                }
            }


            /*- 撤回第一部分的足部 设置论文标题 -*/
            if($currentStudentIndex>$rowNum){
                $currentIndex=$currentIndex-($currentStudentIndex-1-$rowNum);
            }
            $objPHPExcel->getActiveSheet(0)->getStyle("A$currentIndex:J$currentIndex")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet(0)->mergeCells("A$currentIndex:F$currentIndex");
            $objPHPExcel->getActiveSheet(0)->mergeCells("H$currentIndex:J$currentIndex");
            if($biyepd){
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("A$currentIndex")->getFont()->setSize(9);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$currentIndex)->getAlignment()->setWrapText(true);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$currentIndex","毕业论文题目：{$biye[$val['STUDENTNO']]['lwtm']}               指导老师:{$biye[$val['STUDENTNO']]['zdls']}");
            }
            $objPHPExcel->getActiveSheet()->getStyle("H$currentIndex")->getFont()->setSize($rowFontSize);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H$currentIndex","教务处盖章：");
            $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight(30);
            $currentIndex++;
            $objPHPExcel->getActiveSheet()->getStyle("B$currentIndex")->getFont()->setSize(9);
            $objPHPExcel->getActiveSheet()->getStyle("G$currentIndex")->getFont()->setSize(9);
            $objPHPExcel->getActiveSheet()->getRowDimension($currentIndex)->setRowHeight(12);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$currentIndex","制表人：{$teachername['NAME']}");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G$currentIndex","制表日期：$data");
            $currentIndex++;

            //TaskMonitorModel::next(session("S_USER_NAME"),++$count,false,$val,2);
        }


        //TaskMonitorModel::done(session("S_USER_NAME"));

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',"成绩单.xls"));
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    //todo:



    public function hegelv($year,$term,$courseno){
        $youxiu=0;$lianghao=0;$zhongdeng=0;$jige=0;$bujige=0;
        $array=array();
        $courseList=$this->md->sqlQuery("select scores.studentno as  xh,students.name as xm,year,year+1 as year2 ,term,
            scores.ps as ps,scores.qm qm, case[testscore] when '' then cast(cast(scores.EXAMSCORE as int) as char(3))
            else RTRIM(testscore) end AS zp,
            convert(varchar(10),scores.edate,20) as kssj,
            datename(yyyy,scores.date)+'-'+rtrim(cast(datepart(mm,scores.date) as char(2)))+'-'+DATENAME(DD, scores.date) as tbsj
            FROM scores inner join students on students.studentno=scores.studentno
            WHERE YEAR= :YEAR
            AND TERM= :TERM
            AND scores.courseno+scores.[group] =:COURSENO",array(':YEAR'=>$year,':TERM'=>$term,':COURSENO'=>$courseno));

        foreach($courseList as $val){
            if(is_numeric(trim($val['zp']))){
                $rst = intval($val['zp']);
                if($rst>=90){
                    $youxiu++;
                }else if($rst>=80){
                    $lianghao++;
                }else if($rst>=70){
                    $zhongdeng++;
                }else if($rst>=60){
                    $jige++;
                }else {
                    $bujige++;
                }
            }else{
                $rst = trim($val['zp']);
                if($rst=='优秀'){
                    $youxiu++;
                }else if($rst=='良好'){
                    $lianghao++;
                }else if($rst=='及格'||$rst=='合格'){
                    $jige++;
                }else if($rst=='中等'){
                    $zhongdeng++;
                }else {
                    $bujige++;
                }
            }


        }
        $array['youxiu']=round($youxiu/count($courseList),2)*100;
        $array['lianghao']=round($lianghao/count($courseList),2)*100;
        $array['zhongdeng']=round($zhongdeng/count($courseList),2)*100;
        $array['jige']=round($jige/count($courseList),2)*100;
        $array['bujige']=round($bujige/count($courseList),2)*100;
        return $array;

    }






    //todo:测试试图
    public function demo(){
        $array=array();
        $count=$this->md->sqlFind($this->md->getSqlMap($this->base.'Three_three_count_StudentList.SQL'),
            array(':year'=>$_POST['bind'][':year'],':term'=>$_POST['bind'][':term'],':courseno'=>$_POST['bind'][':courseno']));

        if($array['total']=$count['ROWS']){
            $array['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Three_three_select_StudentList.SQL'),
                array(':year'=>$_POST['bind'][':year'],':term'=>$_POST['bind'][':term'],':courseno'=>$_POST['bind'][':courseno'],':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize));
        }else{
            $array['rows']=array();
        }

        echo json_encode($array);

    }



}
