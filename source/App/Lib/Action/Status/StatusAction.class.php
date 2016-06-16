<?php
class StatusAction extends RightAction{
    private $model1;             //数据库对象实例
    private $xssign=array();        //保存替换前的变量
    /**
     * @var StatusModel
     */
    private $model = null;
    private $yearterm = null;

    public function __construct(){
        parent::__construct();
        $this->model1=new SqlsrvModel();
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));

        $this->model = new StatusModel();
        $this->assign('yearterm',$this->yearterm = $this->model->getYearTerm('C'));

    }

    /**
     * 学生报到注册 页面
     */
    public function pageStudentRegister(){
        $this->xiala('regcodeoptions','regcode');
        $this->xiala('classcode','classcode');                    //来源
        $this->xiala('schools','schools');                        //学院
        $this->xiala('statusoptions','statusoption');           //学籍状态
        $this->xiala('majorcode','majorcode');
        $this->display('pageStudentRegister');
    }
    /**
     * 学生注册信息 显示页面
     * @param $studentno
     */
    public function pageStudentInfo($studentno){
        $this->xiala('classcode','classcode');                    //来源
        $this->xiala('schools','schools');                          //所在学院
        $this->xiala('majorcode','majorcode');
        $this->xiala('statusoptions','statusoptions');           //学籍状态statusoption
        $this->xiala('regcodeopions','regcodeopions');
        $this->assign('studentno',$studentno);
        $this->xiala('nationalitycode','nationality');
        $this->xiala('partycode','party');
        $this->display('pageStudentInfo');
    }

    /**
     * 学生叨叨页面
     */
    public function pageStudentCheckIn(){
        $this->assign('studentno',$_GET['newstudentno']);
        $this->assign('examno',$_GET['examno']);
        $this->display('pageStudentCheckIn');
    }
    /**
     * 获取学生注册历史记录
     * @param string $studentno
     */
    public function listStudentRegHistory($studentno){
        $rst = $this->model->getStudentRegisterHistory($studentno);
        if(is_string($rst)){
            $this->exitWithReport($rst);
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 获取学生详细学籍信息
     * @param $studentno
     */
    public function selectStudentInfo($studentno){
        $rst = $this->model->getStudentRegisterInfo($studentno);
        if(is_string($rst)){
            $this->exitWithReport($rst);
        }
        $this->ajaxReturn($rst,'JSON');
    }

    /**
     * 获取学生详细个人信息
     * @param string $studentno
     * @param string $examno
     */
    public function selectStudentPersonalInfo($studentno=null,$examno=null){
        $rst = $this->model->getStudentPersonalInfo($studentno,$examno);
        if(is_string($rst)){
            $this->exitWithReport("查询学生个人信息失败！{$rst}");
        }
        $this->ajaxReturn($rst,'JSON');
    }
    /**
     * 修改学生学籍状态
     * “Model中有同名方法,Controller中暂时未使用”
     * @param $studentno
     * @param $year
     * @param $term
     * @param $regcode
     */
    public function updateStudentRegdata($studentno,$year,$term,$regcode){
        $rst = $this->model->updateStudentRegdata($studentno,$year,$term,$regcode);
        if(is_string($rst) or !$rst){
            $this->exitWithReport("修改失败!".$rst);
        }
        $this->successWithReport('修改成功！');
    }
    /**
     * 添加学生某学年学期学籍状态
     * “Model中有同名方法,Controller中暂时未使用”
     * @param $studentno
     * @param $year
     * @param $term
     * @param $regcode
     */
    public function createStudentRegData($studentno,$year,$term,$regcode){
        $rst = $this->model->createStudentRegdata($studentno,$year,$term,$regcode);
        if(is_string($rst) or !$rst){
            $this->exitWithReport("修改失败!".$rst);
        }
        $this->successWithReport('修改成功！');
    }
    /**
     * 按照班级号注册学年学期状态
     * @param $classno
     * @param int $year
     * @param int $term
     */
    public function updateStudentRegdataByClassno($classno,$year=null,$term=null){
        if(!(isset($year) and isset($term))){
            $year = $this->yearterm['YEAR'];
            $term = $this->yearterm['TERM'];
        }
        $studentlist = $this->model->getStudentListByClassno($classno);
        if(is_string($studentlist)){
            $this->failedWithReport("获取班级[$classno]的学生列表失败！{$studentlist}");
        }
        if(empty($studentlist)){
            $this->exitWithReport("班级[$classno]的学生数量为零，请确认班级是否存在!");
        }
        $message = '';
        foreach($studentlist as $student){
            $studentno = $student['STUDENTNO'];
            $rst = $this->updateOrCreateStudentRegdata($studentno,$year,$term);
            if(is_string($rst) or !$rst){
                $message .= "{$studentno}[{$rst}],";
            }
        }
        $message = trim($message,',');
        if(!empty($message)){
            $this->exitWithReport("以下学生的注册状态修改失败[$message]!");
        }
        $this->successWithReport("修改班级[$classno]的学生学籍状态成功！");
    }

    /**
    PERSONAL.            as ,
    PERSONAL.               as ,
    PERSONAL.            as ,
    PERSONAL.      as ,
    CONVERT(varchar(10),PERSONAL.,20) as ,
    PERSONAL.            as photo,
    RTRIM(PERSONAL.)     as   -- 对应的表示classcode
     * 修改学生注册信息
     * @param $values
     */
    public function updateStudentRegisterInfo($values){
        if(is_string($values)){
            parse_str($values,$values);
        }
        $studentno = $values['studentno'];
        unset($values['studentno']);
        $studentfields = array(
            'NAME'  => array($values['studentname'],true),
            'SEX'   => $values['sexcode'],
            'ENTERDATE' => $values['enterdate'],
            'YEARS' => $values['years'],
            'CLASSNO'   => $values['classno'],
            'TAKEN' => $values['token'],
            'PASSED' => $values['passed'],
            'POINTS' => $values['points'],
            'REG' => $values['reg'],
            'WARN' => $values['warn'],
            'STATUS' => $values['status'],
            'CONTACT' => $values['contact'],
            'GRADE' => $values['grade'],
            'SCHOOL' => $values['school'],
        );
        $personfields = array(
            'MAJOR' => $values['major'],
            'ID'    => array($values['id'],true),
            'PARTY' => $values['party'],
            'NATIONALITY' => $values['nationality'],
            'BIRTHDAY' => $values['birthday'],
            'CLASS' => $values['class'],
        );
        $this->model->startTrans();
        $rsts = $this->model->updateStudentInfo($studentno,$studentfields);
        if(is_string($rsts)){
            $this->failedWithReport("修改学生个人信息失败！{$rsts}");
        }
        $rstp = $this->model->updatePersonInfo($studentno,$personfields);
        if(is_string($rstp)){
            $this->failedWithReport("修改学生个人信息失败！{$rstp}");
        }
        $this->model->commit();
        $this->successWithReport("修改成功!{$rsts}{$rstp}");
    }

    /**
     * 修改或者注册学生注册状态
     * @param $studentno
     * @param $year
     * @param $term
     * @param string $regcode
     * @return array|int|string
     */
    public function updateOrCreateStudentRegdata($studentno,$year,$term,$regcode='A'){
        $rst = $this->model->getStudentRegdata($studentno,$year,$term);
        if(is_string($rst)){
            return "查询学生注册信息失败 !{$rst}";
        }
        //之前未注册过则直接添加，否则直接创建
        if($rst){
            $rst = $this->model->updateStudentRegdata($studentno,$year,$term,$regcode);
        }else{
            $rst = $this->model->createStudentRegdata($studentno,$year,$term,$regcode);
        }
        return $rst;
    }

    public function studentregister(){
        $this->pageStudentRegister();
    }
    public function xueshengzhuce(){
        $this->pageStudentInfo($_GET['studentno']);
    }
    public function studentNO($studentno=null,$examno=null){
        if(REQTAG === 'selectStudentInfo'){
            $this->selectStudentInfo($_POST['studentno']);
        }elseif(REQTAG === 'listStudentRegHistory'){
            $this->listStudentRegHistory($_POST['studentno']);
        }elseif(REQTAG === 'personalinfo'){
            $this->newStudentNO($studentno,$examno);
        }
    }
    public function insertregcode(){
        $rst = $this->updateOrCreateStudentRegdata($_POST['studentno'],$_POST['year'],$_POST['term'],$_POST['regcode']);
        if(is_string($rst)){
            $this->exitWithReport($rst);
        }
        $this->successWithReport("修改学生注册信息成功！{$rst}");
    }
    public function studentUpdate($values){
        $this->updateStudentRegisterInfo($values);
    }
    public function regesClass($classno){
        $this->updateStudentRegdataByClassno($classno);
    }
    public function xinshengbaodao(){
        $this->pageStudentCheckIn();
    }
    public function newStudentNO($studentno=null,$examno=null){
        $this->selectStudentPersonalInfo($studentno,$examno);
    }

//TODO:151012修订线************************************************************************************************************************//




































    /*
     *获取查询数据(单条)
     */
    public function getarr($url,$bind="",$bool=false){
        if(!$bool)
            $sql=$this->model1->getSqlMap($url);
        else
            $sql=$url;
        $arr=$this->model1->sqlFind($sql,$bind);
        return $arr;
    }

    //获取查询数据(多条)
    public function getQuery($url,$bind='',$bool=false){
        if(!$bool)
            $sql=$this->model1->getSqlMap($url);
        else
            $sql=$url;
        $arr=$this->model1->sqlQuery($sql,$bind);
        return $arr;
    }


    /*
     * 修改注册信息时候用到的方法  正常注册|休学|未报到
     */
    public function updatereg(){
        $arr=$this->model1->sqlFind($this->model1->getSqlMap('status/updateReg.SQL'),array(':STUDENTNO'=>$_POST['STUDENTNO'],':REGCODE'=>$_POST['REGCODE']));
        if($arr)
            echo 'false';
    }

    /*
     * 修改学生学籍信息的时候，所使用的方法
     */


    /*
     * 新建学生的时候  插入学生数据的方法
     */
    public function newStudent(){
        $this->xiala('schools','schools');
        $this->xiala('nationalitycode','nationalitycode');
        $this->xiala('partycode','partycode');
        $this->display();

    }

    /*
     * 新建 学生数据的方法
     */
    public function insertStudent(){
        //todo: 验证输入的学生号是否有重复   重复则不让插入
        if($this->Studentyz($_POST['STUDENTNO'])){
            exit('<font color="red">学生号已经存在</font>');
        };

        //todo:查询身份证号是否存在
        $int=$this->model1->sqlFind('select count(*) from PERSONAL WHERE ID=:id',array(':id'=>$_POST['ID']));
        if($int['']){
            exit('<font color="red">该身份证号已经存在了</font>');
        }
        //todo:判断学生号是不是8位 或是否为空
        if(strlen($_POST['STUDENTNO'])!=9){
            if($_POST['STUDENTNO']=="")
                exit('<font color="red">非法操作！！！！</font>');
            exit('<font color="red">非法操作 学生号需要9位</font>');
        }

        //todo:判断班级号是否存在  不存在则不让插入
        if(!$this->classyz($_POST['CLASSNO']))
            exit('<font color="red">班级号不存在！！</font>');

        $shuju=M('students');
        $shuju2=M('personal');
        //开启事物处理
        $shuju->startTrans();
        //插入学生信息
        $bool=$shuju->add(array('STUDENTNO'=>$_POST['STUDENTNO'],'NAME'=>$_POST['NAME'],'SEX'=>$_POST['SEX'],'ENTERDATE'=>$_POST['ENTERDATE'],'YEARS'=>$_POST['YEARS'],'CLASSNO'=>$_POST['CLASSNO'],'CONTACT'=>$_POST['CONTACT'],'SCHOOL'=>$_POST['SCHOOL'],'PASSWORD'=>$_POST['PASSWORD']));
        //插入personal信息
        $bind2= array('STUDENTNO'=>$_POST['STUDENTNO'],'NAME'=>$_POST['NAME'],'SEX'=>$_POST['SEX'],'ID'=>$_POST['ID'],
            'SCHOOL'=>$_POST['SCHOOL'],'BIRTHDAY'=>$_POST['birthday'],'NATIONALITY'=>$_POST['nationality'],'PARTY'=>$_POST['party']);
        $bool2=$shuju2->add($bind2);
        if($bool&&$bool2){
            $shuju->commit();
            echo '插入成功';
        }else{
            $shuju->rollback();
            var_dump($bool,$bool2,$shuju->getDbError(),$shuju2->getDbError(),$bind2);
            echo '插入失败'.$shuju2->getDbError();
        }
    }

    /*
     * //todo:判断学号是否有重复的方法
     */
    protected function Studentyz($num){
        return  $bool=$this->getarr("select NAME from STUDENTS where STUDENTNO=:STUDENTNO",array(':STUDENTNO'=>$num),true);
    }

    /*
     *todo: 判断班级号存不存在的方法
     */
    protected function classyz($num){
        return  $bool=$this->getarr("select CLASSNAME from CLASSES where CLASSNO=:CLASSNO",array(':CLASSNO'=>$num),true);
    }



    public function queryStudentReg(){

        if($this->_hasJson){
            $count=$this->getarr('status/statusZhuceCount.SQL',array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':SCHOOL'=>$_POST['SCHOOL']));
            if($ct['total']=$count['']){
                $ct['rows']=$this->getQuery('status/statusZhuceQuery.SQL',array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':SCHOOL'=>$_POST['SCHOOL'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $ct['rows']=array();
            }
            $this->ajaxReturn($ct,'JSON');
            exit;
        }

        if($_POST['SCHOOL']){
            //  $yearterm=$this->getarr("course/getCourseYearTerm.sql",array(":TYPE"=>"C"));
            //todo:查询新生总数
            $newStudent=$this->getarr('status/_queryNewStudent.SQL',array(':SCHOOL'=>$_POST['SCHOOL']));
            $this->xssign['yingbaodao']=$newStudent[''];
            $this->xssign['year']=$_POST['YEAR'];
            $this->xssign['term']=$_POST['TERM'];

            //todo:查询应报到学生数
            $yingbaodao=$this->model1->sqlFind($this->model1->getSqlMap('status/_queryZhenStudent.SQL'),array(':SCHOOL'=>"{$_POST['SCHOOL']}"));
            $this->xssign['zaiceAll']=$yingbaodao['']-$newStudent[''];

            //todo:查询已报道新生生总数
            $yibaodaoNew=$this->model1->sqlFind($this->model1->getSqlMap('status/_queryYibaodaoNEWStudent.SQL'),array(':SCHOOL'=>$_POST['SCHOOL']));
            $this->xssign['yibaodaoNew']=$yibaodaoNew[''];

            //todo:查询应宝刀学生总数
            $yibaodao=$this->model1->sqlFind($this->model1->getSqlMap('status/_queryYibaodaoStudent2.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':SCHOOL'=>$_POST['SCHOOL']));
            $this->xssign['yiceAll']=$yibaodao['']-$yibaodaoNew[''];

            $top=$this->replace();

            echo $top;
            exit;
        }
        $yearterm=$this->getarr("course/getCourseYearTerm.sql",array(":TYPE"=>"C"));
        $this->assign('yearterm',$yearterm);                            //todo:学年 日期
        $this->xiala('regcodeoptions','regcodeoptions');          //todo:注册状态
        $this->xiala('statusoptions','statusoptions');            //todo:学籍状态
        $this->xiala('schools','schools');                          //todo:学院信息

        $this->display();
    }


    //todo:查询班级报到情况的方法
    public function classbaodao(){
        if($this->_hasJson){
            $count=$this->getarr('status/Three_ClassBaodaoCount.SQL',array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));
            if($ct['total']=$count['']){
                $ct['rows']=$this->getQuery('status/Three_ClassBaodao.SQL',array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $ct['rows']=array();
            }
            $this->ajaxReturn($ct,'JSON');
            exit;
        }
        //todo:查询班级应报到人数
        $yingbaodao=$this->getarr('status/Three_countZhuceStudent.SQL',array(':CLASSNO'=>$_POST['CLASSNO']));
        $this->xssign['year']=$_POST['YEAR'];
        $this->xssign['term']=$_POST['TERM'];
        $this->xssign['yingbaodao']=$yingbaodao[''];

        //todo:查询班级实报道人数
        $shibaodao=$yingbaodao=$this->getarr('status/Three_countBaodaoStudent.SQL',array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));
        $this->xssign['yibaodao']=$shibaodao[''];
        $str=$this->replace();
        echo $str;
    }


    //todo:替换占位符
    public function replace(){
        $one=array();
        $two=array();
        foreach($this->xssign as $key=>$val){
            array_push($two,$val);
            array_push($one,'x'.$key.'x');
        }
        $str=str_replace($one,$two,$_POST['str']);
        return $str;
    }
    /*
     * 学籍异动的方法
     */
    public function xuejiyidong(){
        if($this->_hasJson){
            $count=$this->getarr('status/statusYidongCount.SQL',array(':STUDENTNO'=>doWithBindStr($_POST['STUDENTNO']),':FILENO'=>doWithBindStr($_POST['FILENO']),':INFOTYPE'=>doWithBindStr($_POST['INFOTYPE'])));
            if($one['total']=$count[''])
                $one['rows']=$this->getQuery('status/statusYidongQuery.SQL',array(':STUDENTNO'=>doWithBindStr($_POST['STUDENTNO']),':FILENO'=>doWithBindStr($_POST['FILENO']),':INFOTYPE'=>doWithBindStr($_POST['INFOTYPE']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            else
                $one['rows']=array();
            $this->ajaxReturn($one,'JSON');
            exit;
        }


        $data= $this->model1->sqlFind($this->model1->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $this->assign('YEARTERM',$data);
        $this->xiala('regcodeoptions','regcode');
        $this->xiala('classcode','classcode');                    //来源
        $this->xiala('schools','schools');                        //学院
        $this->xiala('statusoptions','statusoption');           //学籍状态
        $this->xiala('majorcode','majorcode');
        $this->xiala('infotype','infotype');                    //todo:警告处分  严重警告处分  记过 记大过等
        $this->display();
    }


    /*
     *  todo:查看某个学籍异动的时候 所用到的方法
     */
    public function selectStudentFile(){
        $arr=$this->getarr('status/statusStudentFileEdit.SQL',array(":RECNO"=>$_POST['recno']));
        if(!$arr){
            exit('false');
        }
        echo json_encode($arr);
    }

    /*
     * 修改 某个 学籍异动的时候 所用到的方法
     */    //todo;学籍异动的权限判断还未做
    public function updateStudentFile(){
        $bool=$this->model1->sqlExecute($this->model1->getSqlMap('status/statusStudentFileUpdate.SQL'),array(':INFOTYPE'=>$_POST['INFOTYPE'],':DATE'=>$_POST['DATE'],':FILENO'=>$_POST['FILENO'],':REM'=>$_POST['REM'],':STUDENTNO'=>$_POST['STUDENTNO'],':OLDSTUDENTNO'=>$_POST['OLDSTUDENTNO']));
        if($bool){
            echo '学籍异动修改成功';
        }
    }




    /*
     * 在册学生管理的方法
     */
    public function StatusRegGate(){
        if($this->_hasJson){
            $count=$this->getarr('status/statusZAICEcount.SQL',array(':StudentNo'=>doWithBindStr($_POST['STUDENTNO']),':Name1'=>doWithBindStr($_POST['NAME']),':ClassNo'=>doWithBindStr($_POST['CLASSNO']),':School'=>doWithBindStr($_POST['SCHOOL']),':Status'=>doWithBindStr($_POST['STATUS']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            if($one['total']=$count['']){
                $one['rows']=$this->getQuery('status/statusZAICEquery.SQL',array(':StudentNo'=>doWithBindStr($_POST['STUDENTNO']),':Name1'=>doWithBindStr($_POST['NAME']),':ClassNo'=>doWithBindStr($_POST['CLASSNO']),':School'=>doWithBindStr($_POST['SCHOOL']),':Status'=>doWithBindStr($_POST['STATUS']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $one['rows']=array();
            }
            $this->ajaxReturn($one,'JSON');
            exit;
        }
        $data= $this->model1->sqlFind($this->model1->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));

        $this->assign('YEARTERM',$data);
        $this->xiala('regcodeoptions','regcode');
        $this->xiala('classcode','classcode');                    //来源
        $this->xiala('schools','schools');      //所在学院
        $this->xiala('majorcode','majorcode');
        $this->xiala('statusoptions','statusoptions');           //学籍状态statusoption
        $this->display();
    }

    public function studentExp(){
        vendor("PHPExcel.PHPExcel");
        //创建一个新的对象
        $objPHPExcel = new PHPExcel();

        //设置文件属性
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        //重命名工作表名称
        $title="在册学生列表";
        $objPHPExcel->getActiveSheet()->setTitle($title);

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        //设置默认行高
        //$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);

        //设置默认宽度
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);

        //设置单元格自动换行
        $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

        //设置默认内容垂直居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置单元格加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //标题设置
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
        $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');//合并A1单元格到M1
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高

        //列名设置
        $objPHPExcel->getActiveSheet()->getStyle("A2:J2")->applyFromArray($style);//字体样式
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(18);
        //单元格内容写入
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"性别");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"主修班级");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"学籍状态");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"退学警告次数");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"积点分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"选课学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"完成学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"所在学院");
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("A:J")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //获取信息
        $data=$this->getQuery('status/statusZAICEquery.SQL',array(':StudentNo'=>doWithBindStr($_POST['StudentNo']),':Name1'=>doWithBindStr($_POST['Name']),':ClassNo'=>doWithBindStr($_POST['ClassNo']),':School'=>doWithBindStr($_POST['School']),':Status'=>doWithBindStr($_POST['Status']),':start'=>1,':end'=>300));

       // $data= $this->model1->sqlQuery($this->model1->getSqlMap("status/statusZAICEexp.SQL"),array(':StudentNo'=>doWithBindStr($_POST['STUDENTNO']),':Name1'=>doWithBindStr($_POST['NAME']),':ClassNo'=>doWithBindStr($_POST['CLASSNO']),':School'=>doWithBindStr($_POST['SCHOOL']),':Status'=>doWithBindStr($_POST['STATUS']) ));
        $row=2;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['STUDENTNO']);
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['SEX']);
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['CLASSNAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['STATUSVALUE']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['WARN']);
            $objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['POINTS']);
            $objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['TAKEN']);
            $objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['PASSED']);
            $objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['SCHOOLNAME']);
        }
        //边框设置
        $objPHPExcel->getActiveSheet(0)->getStyle("A2:J$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

        //开始下载
        $filename = $title.".xls";
        $ua = $_SERVER["HTTP_USER_AGENT"];

        header('Content-Type:application/vnd.ms-excel');
        if(preg_match("/MSIE/", $ua)){
            header('Content-Disposition:attachment;filename="'.urlencode($filename).'"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition:attachment;filename*="utf8\'\''.$filename.'"');
        } else {
            header('Content-Disposition:attachment;filename="'.$filename.'"');
        }
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }



    //专门用于查询的方法
    public function Squery(){
           // echo $_POST['Sqlpath']['count'];

        $count=$this->model1->sqlFind($this->model1->getSqlMap($_POST['Sqlpath']['count']),$_POST['bind']);
      //  var_dump($count);
        if($data['total']=$count['ROWS']){
            $data['rows']=$this->model1->sqlQuery($this->model1->getSqlMap($_POST['Sqlpath']['select']),array_merge($_POST['bind'],array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));

        }else{
            $data['total']=0;
            $data['rows']=array();
        }


        $this->ajaxReturn($data,'JSON');
    }


    public function Window_studentinfo(){
        $arr=$this->getarr('status/statusNewStudent.SQL',array(':NEWSTUDENTNO'=>$_GET['STUDENTNO'],':EXAMNO'=>$_GET['EXAMNO']));
        $this->assign('content',$arr);
        $this->display();
    }

    public function Window_studentRegis(){
        if(isset($_GET['EXAMNO'])){
            $studentno=$this->model1->sqlFind('SELECT PERSONAL.STUDENTNO FROM PERSONAL WHERE EXAMNO=:EXAMNO',array(':EXAMNO'=>$_GET['EXAMNO']));
            $_GET['STUDENTNO']=$studentno['STUDENTNO'];
        }
        //todo:查询出学生的注册信息（注册页面的上半部分）
        $arr=$this->getarr('status/statusStudent.SQL',array(':StudentNo'=>$_GET['STUDENTNO']));
        //todo:查询出学生的regDATE信息(注册页面的下半部分)
        $arr2=$this->getQuery('select REGDATA.YEAR,REGDATA.TERM,REGCODEOPTIONS.VALUE AS CODE from REGDATA,REGCODEOPTIONS where REGDATA.REGCODE=REGCODEOPTIONS.NAME AND REGDATA.STUDENTNO=:studentno',array(':studentno'=>$_GET['STUDENTNO']),true);
        $this->xiala('schools','schools');
        $this->assign('regdate',$arr2);
        $this->assign('content',$arr);
        $this->display();

    }

    //todo:添加学籍异动的方法
    public function addxuejiyidong(){
        $this->xiala('infotype','infotype');
        $this->display();
    }

    //todo:按 新生报到情况汇总的统计方法
    public function Studenthz(){
        if($this->_hasJson){
            $total=$this->getarr($_POST['sqlpath']['count']);             //'status/Three_hz_count.SQL'
            if($content['total']=$total['']){
                $content['rows']=$this->getQuery($_POST['sqlpath']['select'],array(':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $content['rwos']=array();
            }
            $this->ajaxReturn($content,'JSON');
            exit;
        }
        $left='status/Three_hz_';
        foreach($_POST['sqlpath'] as $key=>$val){
            $$key=$this->getarr($left.$val.'.SQL');
            $name=$$key;
            $this->xssign[$key]=$name[''];
        }
        $this->xssign['year']=$_POST['YEAR'];
        $this->xssign['term']=$_POST['TERM'];
        $str=$this->replace();
        echo $str;
    }


    //todo:综合查询
    public function zonghe(){
        $all=$this->getarr('status/Three_zonghe_ALL.SQL',array(':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':CLASSNO'=>doWithBindStr($_POST['CLASSNO']),':STATUS'=>doWithBindStr($_POST['STATUS'])));
        $this->xssign['All']=$all['NUMBER'];
        $all2=$this->getarr('status/Three_zonghe_All2.SQL',array(':CLASSNO'=>doWithBindStr($_POST['CLASSNO']),':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':STATUS'=>doWithBindStr($_POST['STATUS']),':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

        $this->xssign['All2']=$all2['NUMBER'];
        $str=$this->replace();
        echo $str;
    }



    /*
     * 查询在册学生数据的方法
     */

    //todo:插入学籍异动的方法 顺便判断权限
    public function insertRegisteries(){
        $abc=$this->model1->sqlFind('select SCHOOL from TEACHERS where TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        if($abc['SCHOOL']!='A1'){
            exit('只有教务处的人才可以添加学籍异动');
        }
        $mol=$this->model1->sqlExecute($this->model1->getSqlMap('status/Five_insertRegistries.SQL'),array(':infotype'=>$_POST[':infotype'],':date'=>$_POST[':date'],':fileno'=>$_POST[':fileno'],':rem'=>$_POST[':rem'],':studentno'=>$_POST[':studentno']));
        if(!$mol){
            return;
        }
        echo '插入成功';
    }

    public function statusexecute(){
        $bol=$this->model1->sqlExecute($this->model1->getSqlMap($_POST['sqlpath']),$_POST['bind']);
        if($bol){
            echo 'true';
        }else{
            var_dump($bol);
        }
    }

    //todo:正常注销 或 开除学籍的方法
    public function zhuxiaoxj(){
        //todo:启动回滚
        $this->model1->startTrans();
        //todo:查询出学生的信息
        $arr=$this->model1->sqlFind('select * from STUDENTS where STUDENTNO=:studentno',array(':studentno'=>$_POST[':studentno']));
        //todo:删除学生表信息
        $arr2=$this->model1->sqlExecute('delete from STUDENTS where STUDENTNO=:studentno',array(':studentno'=>$_POST[':studentno']));
        //todo:删除R32表的信息
        $arr3=$this->model1->sqlExecute('delete from R32 where STUDENTNO=:studentno',array(':studentno'=>$_POST[':studentno']));
        //todo:删除R4表信息
        $arr4=$this->model1->sqlExecute('delete from R4 where STUDENTNO=:studentno',array(':studentno'=>$_POST[':studentno']));
        //todo:删除R28表信息
        $arr5=$this->model1->sqlExecute('delete from R28 where STUDENTNO=:studentno',array(':studentno'=>$_POST[':studentno']));
        //todo:向毕业生表插入信息
        $arr6=$this->model1->sqlExecute($this->model1->getSqlMap('status/Five_insertGRADUATES.SQL'),array(':studentno'=>$arr['STUDENTNO'],':name'=>$arr['NAME'],':sex'=>$arr['SEX'],':graddate'=>date('Y-m-d H:i:s'),':enterdate'=>$arr['ENTERDATE'],':years'=>$arr['YEARS'],':graduation'=>$_POST['code'],':credits'=>0));

        //todo:向LOGS 表插入信息
        $bool=$arr&&$arr6?1:0;
        // $arr7=$this->model1->sqlExecute($this->model1->getSqlMap('status/Five_insertLOGS.SQL'),array(':username'=>$_SESSION['S_USER_NAME'],':emal'=>"",':remotehost'=>$_SERVER['REMOTE_HOST'],':remoteip'=>$_SERVER['REMOTE_ADDR'],':derivedfrom'=>'',':useragent'=>substr($_SERVER['HTTP_USER_AGENT'],0,40),':user'=>'',':rooles'=>$_SESSION['S_ROLES'],':group'=>'',':scriptname'=>$_SERVER['SCRIPT_NAME'],':pathinfo'=>array_pop(explode('/',$_SERVER['ORIG_PATH_INFO'])),':query'=>$_SERVER['QUERY_STRING'],':method'=>$_SERVER['REQUEST_METHOD'],':title'=>'',':time'=>date('Y-m-d H:i:s'),':success'=>$bool));
        //  var_dump($arr7);
        $fiex=substr($_SERVER['HTTP_USER_AGENT'],0,40);
        $pathinfo=array_pop(explode('/',$_SERVER['ORIG_PATH_INFO']));
        $time=date('Y-m-d H:i:s');


        if($bool){
            $this->model1->commit();
            echo '除名成功，学生的成绩记录和异动记录被保留，同时学生也被保留在毕业生库中，其它相关记录被删除！';
        }else{
            $this->model1->rollback();
            echo '除名失败！';
        }
    }




    public function xueshengedit(){
        $this->xiala('classcode','classcode');                    //来源
        $this->xiala('schools','schools');                          //所在学院
        $this->xiala('majorcode','majorcode');
        $this->xiala('statusoptions','statusoptions');           //学籍状态statusoption
        $this->xiala('regcodeopions','regcode');
        $this->assign('info',$_GET['studentno']);
        $this->display();
    }

    public function studentFileEdit(){
        $this->xiala('infotype','infotype');
        $this->assign('info',$_GET['recno']);
        $this->display();
    }




    public function zonghechaxun(){
        $yearterm=$this->getarr("course/getCourseYearTerm.sql",array(":TYPE"=>"C"));
        $this->assign('yearterm',$yearterm);                            //todo:学年 日期
        $this->xiala('regcodeoptions','regcodeoptions');          //todo:注册状态
        $this->xiala('statusoptions','statusoptions');            //todo:学籍状态
        $this->xiala('schools','schools');                          //todo:学院信息
        $this->display();
    }

    public function one(){

        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->display();
    }

    public function one_xueyuanbaodao(){
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->assign('school',$_GET['school']);
        $this->display();
    }

    public function class_baodao(){
        $this->assign('classno',$_GET['classno']);
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->display();

    }

    public function one_xuekehuizong(){
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->display();
    }

    public function one_shengfenhuizong(){
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->display();
    }

    public function one_zonghechaxun(){
        /* obj['SCHOOL']=$('#sc').val();
                                                                                                                                                   obj['CLASSNO']=$('#class').val();
                                                                                                                                                   obj['REGIS']=$('#regiszt').val()
                                                                                                                                                   obj['STATUS']=$('#statuszt').val();
                                                                                                                                                   obj['YEAR']=$('#zhYEAR').val();
                                                                                                                                                   obj['TERM']=$('#zhTERM').val();*/

        $this->assign('school',str_replace('_','',$_GET['school']));
        $this->assign('classno',str_replace('_','',$_GET['classno']));
        $this->assign('regiszt',str_replace('_','',$_GET['regiszt']));
        $this->assign('status',str_replace('_','',$_GET['status']));
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->display();
    }



}



?>