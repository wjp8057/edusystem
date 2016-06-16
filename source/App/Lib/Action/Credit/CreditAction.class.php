<?php
class CreditAction extends RightAction{
    private $md;
    private $base;
    private $objPHPExcel;
    /*
     * 新建课程的方法
     */
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='credit/';
        $this->assign('yearterm',$this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"R")));
        //todo:判断是否在时间范围内
        $this->assign('is_time',$this->is_time());
    }
    public function index(){
        $this->display();
    }



    //todo:修改单个
    public function updateSinge(){
        //不通过某人的申请
        if($this->_hasJson){
            $this->md->startTrans();
            $bool=$this->md->sqlExecute("delete from creditsinglefirm where applydate_id=:id",array(':id'=>$_POST['id']));
            if($bool){
                $this->md->commit();
                exit('操作成功');
            }
            $this->md->rollback();
            exit('操作失败');
        }

        //批量保存申请修改
        $this->md->startTrans();
        foreach($_POST['bind'] as $val){
            $bool=$this->md->sqlExecute("update creditsinglefirm set projectname=:pname,certficatetime=:time,credit=:credit where applydate_id=:id",
            array(':pname'=>$val['projectname'],':time'=>$val['certficatetime'],':credit'=>$val['credit'],':id'=>$val['applydate_id']));
            if(!$bool){
                $this->md->rollback();
                exit($val['NAME'].'的申请没有修改成功，操作已经撤销！');
            }
        }
        $this->md->commit();
        exit('修改成功');

    }


    //todo:认定归档
    public function file(){
        if(isset($_POST['bind'])){
            $this->md->startTrans();
            //todo:清空本学期历史
            $sql01 = 'delete from AddCredit where year = :year and term = :term;  ';
            $bool4 = $this->md->sqlExecute($sql01,$_POST['bind']);
            //todo:单个认定 归档
            $bool1=$this->md->sqlExecute($this->md->getSqlMap($this->base.'one_three_file.SQL'),$_POST['bind']);
            //todo:单个认定归档后 修改其状态
//            $bool2=$this->md->sqlExecute($this->md->getSqlMap($this->base.'one_three_file_update.SQL'),$_POST['bind']);
            //todo:统一认定 归档
            $bool3=$this->md->sqlExecute($this->md->getSqlMap($this->base.'one_three_file_count.SQL'),$_POST['bind']);
            //todo:统一认定归档后,修改其状态      注释原因 ：数据表无状态字段可以修改
//            $bool4=$this->md->sqlExecute($this->md->getSqlMap($this->base.'one_three_file_count_update.SQL'),$_POST['bind']);//array(':year'=>$_POST['bind']['year'],':term'=>$_POST['bind']['term'])
            if($bool1!==false&&$bool3!==false&&$bool4!==false){
                $this->md->commit();
                var_dump($bool1,$bool3,$bool4);
                exit('归档成功');
            }else{
                var_dump($bool1,$bool3,$bool4);
                $this->md->rollback();
                exit('归档过程中出错');
            }
        }
        $this->getdateinfo();
        $this->display();
    }


    //todo:参数设置的页面
    public function Parameters(){
        //todo:添加证书
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'insertcerti') ){
            $sql = 'insert into CreditCertficate(cert_name,credit,status) values(:cert_name,:credit,:status);';
            $bind = array(
                ':cert_name'=>$_POST['cert_name'],
                ':credit'=>$_POST['credit'],
                ':status'=>$_POST['status']
            );
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result."");
        }
        //todo:查找证书
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'searchcerti') ){
            $bind = array(':cert_name'=>doWithBindStr($_POST['cert_name']),
                ':yes'=>$_POST['yes'],
                ':no'=>$_POST['no']);
            $json = $this->getListJson($bind,"credit/one_countCerti.SQL",'credit/one_selectCerti.SQL',800);
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        //todo:修改证书
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'updatecenti') ){
            $sql = 'update CreditCertficate set cert_name=:cert_name,credit=:credit,status=:status where cert_id=:cert_id;';
            $bind = array(':cert_name'=>$_POST['cert_name'],
                ':credit'=>$_POST['credit'],
                ':status'=>$_POST['status'],
                ':cert_id'=>$_POST['cert_id']
            );
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result."");
        }
        //todo:证书详细 编辑时使用
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'centidetail') ){
            $sql = 'select cert_id,cert_name,credit,status from CreditCertficate where cert_id=:cert_id;';
            $bind = array(':cert_id'=>$_POST['cert_id'] );
            $result = $this->md->sqlQuery($sql,$bind);
            $this->ajaxReturn($result,'JSON');
            exit;
        }
        //todo:删除证书
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'deletecenti') ){
            $sql = 'delete from CreditCertficate where cert_id=:id;';
            $bind = array(':id'=>$_POST['id'] );
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result."");
        }

        $this->display();
    }

    /**
     * @function 设置认定时间
     */
    public function applydate(){
        if(isset($_POST['bind'])){
            if($this->md->sqlExecute('update Creditapplydate set status=0')){
                $result = $this->md->sqlExecute('insert into Creditapplydate(year,term,begintime,endtime,status) values(:year,:term,:start,:end,:status);',$_POST['bind']);
                exit($result>0?'success':'insert failure');
            }else{
                exit('update falure');
            }
        }
        $this->getdateinfo();
        $this->display();
    }

    /**
     * @function 选取认定的时间，如果为找到，则在历史记录中选择最近的一次
     */
    public function getdateinfo(){
        $timearr=$this->md->sqlQuery('select status,year,term,convert(varchar(20),begintime,20) as begintime,convert(varchar(20),endtime,20) as endtime from Creditapplydate where status=1');
        if(count($timearr)==1){
            $this->assign('timearr',$timearr[0]);
        }else if(count($timearr)==0){
            $timearr=$this->md->sqlFind('select top 1 status,year,term,convert(varchar(20),begintime,20) as begintime,convert(varchar(20),endtime,20) as endtime from Creditapplydate order by applydate_id desc');
            $this->assign('timearr',$timearr);      //todo:取最后一次操作的
        }
    }





    //todo:单个认定表的页面
    public function Single(){
        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery('select cert_id,credit,cert_name from CreditCertficate where status=1'));
        //todo:提交人和提交学院
       $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }


    //todo:素质单个认定表的页面
    public function Quality(){
        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery('select cert_id,credit,cert_name from CreditCertficate where status=1'));

        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }



    //todo: 技能单个认定 院系审核（创新）
    public function Skillschoolview(){
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'getlist')){
            $bind = array(
                ':year'=>$_POST['year'],
                ':term'=>$_POST['term'],
                ':studentno'=>doWithBindStr($_POST['studentno']),
                ':studentname'=>doWithBindStr($_POST['studentname']),
                ':classno'=>doWithBindStr($_POST['classno']),
                ':projectname'=>doWithBindStr($_POST['projectname']),
                ':cone'=>$_POST['cone'],
                ':ctwo'=>$_POST['ctwo'],
                ':v1'=>$_POST['v1'],
                ':v2'=>$_POST['v2'],
                ':v3'=>$_POST['v3'],
                ':schoolname'=>$_POST['schoolname']
            );
            $json = $this->getListJson($bind,'credit/Two_two_countStudent_list.SQL','credit/Two_two_selectStudent_list.SQL');
            $this->ajaxReturn($json,'JSON');
            exit;
        }

        //todo:通过不通过
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'passsingle')){
//            if(!$this->checkRoleIsExpected('E')){
////                var_dump(session('S_ROLES'),$this->checkRoleIsExpected('E'));
//                exit('Roles Unexpected!');
//            }
//            if(!checkSelfSchool($_SESSION['S_USER_INFO']['TEACHERNO'],$this->getFieldByApplyid($_POST['id'],'studentno'))){
//                exit('Has no permission to modified student of other school!');
//            }
            $sql = 'update CREDITSINGLEFIRM set schoolview=:schoolview,schoolsubtime=getdate() where applydate_id=:id;';
            $bind = array(
                ':schoolview'=>$_POST['schoolview'],
                ':id'=>$_POST['id']
            );
            $result = $this->md->sqlExecute($sql,$bind);
//            var_dump($this->md->getDbError(),$this->md->getLastSql(),$bind);
//            var_dump($result);
            exit($result>0?'success':'failure');
        }

        $this->assign('schoolname2',$this->md->sqlFind('select SCHOOLS.NAME from TEACHERS inner join SCHOOLS on TEACHERS.SCHOOL=SCHOOLS.SCHOOL where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }

    /**
     * 获取申请ＩＤ对应的记录中的学生号
     * @param $applyid
     * @return null 查不到该学生记录
     *          string  学生号
     */
    private function getFieldByApplyid($applyid,$filedname){
        $sql = "select school from students where studentno = (select $filedname from creditsinglefirm where applydate_id = :applyid);";
        $bind = array(':applyid'=>$applyid);
        $res = $this->md->sqlQuery($sql,$bind);
        return $res?$res[0]['school']:null;
    }



    //todo:素质单个认定----------院系审核
    public function Qualityschoolview(){
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'getlist')) {
            $bind = array(
                ':year' => $_POST['year'],
                ':term' => $_POST['term'],
                ':studentno' => doWithBindStr($_POST['studentno']),
                ':studentname' => doWithBindStr($_POST['studentname']),
                ':classno'=>doWithBindStr($_POST['classno']),
                ':projectname' => doWithBindStr($_POST['projectname']),
                ':cone' => $_POST['cone'],
                ':ctwo' => $_POST['ctwo'],
                ':v1' => $_POST['v1'],
                ':v2' => $_POST['v2'],
                ':v3' => $_POST['v3'],
                ':schoolname' => $_POST['schoolname']
            );
            $json = $this->getListJson($bind, 'credit/Two_two_countStudent_list.SQL', 'credit/Two_two_selectStudent_list.SQL');
            $this->ajaxReturn($json, 'JSON');
            exit;
        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'passsingle')){
            $sql = 'update CREDITSINGLEFIRM set schoolview=:schoolview,deanview=:deanview,schoolsubtime=getdate() where applydate_id=:id;';
            $bind = array(
                ':schoolview'=>$_POST['schoolview'],
                ':deanview'=>$_POST['deanview'],
                ':id'=>$_POST['id']
            );
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result>0?'success':'failure');
        }
        $this->assign('schoolname2',$this->md->sqlFind('select SCHOOLS.NAME from TEACHERS inner join SCHOOLS on TEACHERS.SCHOOL=SCHOOLS.SCHOOL where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }

    private function singleExcelExport(){
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
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"]."学期通过学生汇总表";
        $objPHPExcel->getActiveSheet()->setTitle('汇总表');

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置默认内容居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("D:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(35);
        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //列名设置
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        //单元格内容写入
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"学号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B1',"姓名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C1',"项目名称");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D1',"学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E1',"项目认定时间");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F1',"编号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G1',"创建时间");
        //插入查询数据

        $bind = array(
            ':year'=>$_POST['year'],
            ':term'=>$_POST['term'],
            ':studentno'=>doWithBindStr($_POST['studentno']),
            ':studentname'=>doWithBindStr($_POST['studentname']),
            ':classno'=>doWithBindStr($_POST['classno']),
            ':projectname'=>doWithBindStr($_POST['projectname']),
            ':cone'=>$_POST['cone'],
            ':ctwo'=>$_POST['ctwo'],
            ':schoolname'=>$_POST['schoolname']
        );
        $data = $this->md->sqlQuery($this->md->getSqlMap('credit/skilldeanview_export_excel.SQL'),$bind);
        $row=1;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", trim($val['Studentno']));
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['projectname']));
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['credit']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['certficatetime']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['firmno'].' ');
            $objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['createdate']);

        }
        //边框设置
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename =$title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    private function countExcelExport(){
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
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"]."学期通过学生汇总表";
        $objPHPExcel->getActiveSheet()->setTitle('汇总表');

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置默认内容居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("D:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);
        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //列名设置
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        //单元格内容写入
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"学号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B1',"姓名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C1',"项目名称");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D1',"学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E1',"项目认定时间");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F1',"创建时间");
        //插入查询数据

        $bind = array(':projectname'=>doWithBindStr($_POST['projectname']),
            ':subschool'=>doWithBindStr($_POST['subschool']."%"),
            ':ctype'=>$_POST['ctype'],
            ':status'=>doWithBindStr($_POST['status']),
            ':studentno'=>doWithBindStr($_POST['studentno']),
            ':schoolno'=>doWithBindStr($_POST['schoolno']));
        $data = $this->md->sqlQuery($this->md->getSqlMap('credit/qualitycountdean_export_excel.SQL'),$bind);
        $row=1;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", trim($val['studentno']));
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['projectname']));
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['credit']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['certficatetime']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['addtime'].' ');

        }
        //边框设置
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename =$title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    //todo:技能单个认定  教务处终审（创新）
    public function Skilldeanview(){
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'getlist')){
            $bind = array(
                ':year'=>$_POST['year'],
                ':term'=>$_POST['term'],
                ':studentno'=>doWithBindStr($_POST['studentno']),
                ':studentname'=>doWithBindStr($_POST['studentname']),
                ':classno'=>doWithBindStr($_POST['classno']),
                ':projectname'=>doWithBindStr($_POST['projectname']),
                ':cone'=>$_POST['cone'],
                ':ctwo'=>$_POST['ctwo'],
                ':schoolname'=>$_POST['schoolname'],
                ':d1'=>$_POST['d1'],
                ':d2'=>$_POST['d2'],
                ':d3'=>$_POST['d3']
            );
            $json = $this->getListJson($bind,'credit/Two_three_countStudent_list.SQL','credit/Two_three_selectStudent_list.SQL');
            $this->ajaxReturn($json,'JSON');
            exit;
        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'exportexcel')){

            $this->singleExcelExport();

        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'update')){
            $bind = array(
                ':deanview'=>$_POST['deanview'],
                ':id'=>$_POST['id']
            );
            $sql = 'update CreditSinglefirm set deanview=:deanview,deansubtime=getdate() where applydate_id=:id';
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result>0?'success':'failure');
        }
        $this->xiala('schools','school');
        $this->display();
    }

    //todo:素质单个认定-----------教务处终审
    public function Qualitydeanview(){
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'getlist')){
            $bind = array(
                ':year'=>$_POST['year'],
                ':term'=>$_POST['term'],
                ':studentno'=>doWithBindStr($_POST['studentno']),
                ':studentname'=>doWithBindStr($_POST['studentname']),
                ':classno'=>doWithBindStr($_POST['classno']),
                ':projectname'=>doWithBindStr($_POST['projectname']),
                ':cone'=>$_POST['cone'],
                ':ctwo'=>$_POST['ctwo'],
                ':schoolname'=>$_POST['schoolname'],
                ':d1'=>$_POST['d1'],
                ':d2'=>$_POST['d2'],
                ':d3'=>$_POST['d3']
            );
            $json = $this->getListJson($bind,'credit/Two_three_countStudent_list.SQL','credit/Two_three_selectStudent_list.SQL');
            $this->ajaxReturn($json,'JSON');
            exit;
        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'exportexcel')){

            $this->singleExcelExport();

        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'update')){
            $bind = array(
//                ':schoolview'=>$_POST['schoolview'],
                ':deanview'=>$_POST['deanview'],
                ':id'=>$_POST['id']
            );
            $sql = 'update CreditSinglefirm set deanview=:deanview,deansubtime=getdate() where applydate_id=:id';
            $result = $this->md->sqlExecute($sql,$bind);
            exit($result>0?'success':'failure');
        }


        $this->xiala('schools','school');
        $this->display();
    }








    //todo:技能统一认定------------项目创建
    public function Skillcreateproject(){
        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery('select cert_id,credit,cert_name from CreditCertficate where status=1'));
        //todo:报送学院多选
      //  $arr=$this->md->sqlQuery('select rtrim(SCHOOL) as SCHOOL,rtrim(NAME) as NAME from SCHOOLS');
       /* $this->assign('school',$arr);
          $tplarr=array();
        foreach($arr as $key=>$val){
            $tplarr[$val['SCHOOL']]=$val['NAME'];
        }
        $this->assign('tplarr',json_encode($tplarr));*/

        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
              $this->display();
    }

    //todo:素质统一认定------------项目创建
    public function Qualitycreateproject(){
        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery('select cert_id,credit,cert_name from CreditCertficate where status=1'));
        //todo:报送学院多选
        $arr=$this->md->sqlQuery('select rtrim(SCHOOL) as SCHOOL,rtrim(NAME) as NAME from SCHOOLS');
        $this->assign('school',$arr);
        $tplarr=array();
        foreach($arr as $key=>$val){
            $tplarr[$val['SCHOOL']]=$val['NAME'];
        }
        $this->assign('tplarr',json_encode($tplarr));

        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }




    //todo:技能统一:院系认定
    public function SkillcountSchool(){
        //查询学生列表
        if(isset($_GET['list']) && ( trim($_GET['list']) == 1)){
            $bind = array(':projectname'=>doWithBindStr($_POST['projectname']),
                ':subschool'=>doWithBindStr($_POST['subschool']."%"),
                ':ctype'=>$_POST['ctype'],
                ':status'=>doWithBindStr($_POST['status']),
                ':studentno'=>doWithBindStr($_POST['studentno']));
            $json = $this->getListJson($bind,"credit/tuanwei_Three_two_count.SQL",'credit/tuanwei_Three_two_select.SQL');
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        //查看该学号是否属于自己学院
        if(isset($_GET['tag']) && ( trim($_GET['tag']) == 'ourschool')){
            $arr=$this->md->sqlFind('select S.STUDENTNO,S.NAME,C.CLASSNAME from STUDENTS AS S  LEFT OUTER JOIN  CLASSES AS C ON S.CLASSNO=C.CLASSNO
             WHERE S.STUDENTNO=:studentno ',array(':studentno'=>$_POST['studentno']));
            if($arr){
                $arr['time']=date('Y-m-d');
                exit(json_encode($arr));
            }else{
                exit("false");
            }
        }
        //删除学生
        if(isset($_GET['tag']) && ( trim($_GET['tag']) == 'delstudent')){
            $this->md->startTrans();
            foreach($_POST['bind'] as $val){
                $int=$this->md->sqlExecute("delete from creditcount where recno=:recno",array(':recno'=>$val['recno']));
                if(!$int){
                    $this->md->rollback();
                    exit('删除失败');
                }
            }
            $this->md->commit();
            exit('删除成功');
        }

        //todo:学院名称 学院代号 教师名称 登录账户名
        $this->assign('schoolname',$this->md->sqlFind('select SCHOOLS.NAME AS SNAME,TEACHERS.SCHOOL,TEACHERS.NAME,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO inner join SCHOOLS on TEACHERS.SCHOOL=SCHOOLS.SCHOOL where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        $this->display();
    }


    //todo:素质统一:院系认定
    public function QualitycountSchool(){
        if(isset($_GET['list']) && ( trim($_GET['list']) == 1)){
            $bind = array(':projectname'=>doWithBindStr($_POST['projectname']),
                ':subschool'=>doWithBindStr($_POST['subschool']."%"),
                ':ctype'=>$_POST['ctype'],
                ':status'=>doWithBindStr($_POST['status']),
                ':studentno'=>doWithBindStr($_POST['studentno']));
            $json = $this->getListJson($bind,"credit/tuanwei_Three_two_count.SQL",'credit/tuanwei_Three_two_select.SQL');
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        //删除学生
        if(isset($_GET['tag']) && ( trim($_GET['tag']) == 'delstudent')){
            $this->md->startTrans();
            foreach($_POST['bind'] as $val){
                $int=$this->md->sqlExecute("delete from creditcount where recno=:recno",array(':recno'=>$val['recno']));
                if(!$int){
                    $this->md->rollback();
                    exit('删除失败');
                }
            }
            $this->md->commit();
            exit('删除成功');
        }
        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select SCHOOLS.NAME AS SNAME,TEACHERS.SCHOOL,TEACHERS.NAME,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO inner join SCHOOLS on TEACHERS.SCHOOL=SCHOOLS.SCHOOL where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        if(isset($_POST['studentno'])){
            //todo:查询该学生是不是本学院的
            $arr=$this->md->sqlQuery('select S.STUDENTNO,S.NAME,C.CLASSNAME from STUDENTS AS S inner join CLASSES AS C ON S.CLASSNO=C.CLASSNO
             WHERE S.STUDENTNO=:studentno or S.CLASSNO=:classno',array(':studentno'=>$_POST['studentno'],':classno'=>$_POST['classno']));
            $arr['time']=date('Y-m-d');
            exit(json_encode($arr));

        }
        $this->display();
    }


    //todo:技能统一：终审
    public function Skillcountdean(){

        if(isset($_GET['list']) && ($_GET['list'] == 1)){
            $bind = array(':projectname'=>doWithBindStr($_POST['projectname']),
                ':subschool'=>doWithBindStr($_POST['subschool']."%"),
                ':ctype'=>$_POST['ctype'],
                ':status'=>doWithBindStr($_POST['status']),
                ':studentno'=>doWithBindStr($_POST['studentno']),
                ':schoolno'=>doWithBindStr($_POST['schoolno']));
            $json = $this->getListJson($bind,"credit/tuanwei_Three_three_count.SQL",'credit/tuanwei_Three_three_select.SQL');
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'exportexcel')){

            $this->countExcelExport();

        }
        //todo:通过不通过
        if(isset($_GET['tag']) && ($_GET['tag'] == 'pass')) {
            $sql = 'update creditcount set status=:status where recno=:recno';
            $bind = array(
                ':status'=>$_POST['status'],
                ':recno'=>$_POST['recno']
            );
            $res = $this->md->sqlExecute($sql,$bind);
            if($res && $res>0){
                exit('success');
            }else{
                exit('failure');
            }
        }
        //todo:删除
        if(isset($_GET['tag']) && ($_GET['tag'] == 'delete')) {
            $sql = 'delete from creditcount where  recno=:recno';
            $bind = array(
                ':recno'=>$_POST['recno']
            );
            $res = $this->md->sqlExecute($sql,$bind);
            if($res && $res>0){
                exit('success');
            }else{
                exit('failure');
            }
        }

        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,TEACHERS.NAME,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));

        //todo:报送学院多选
        $arr=$this->md->sqlQuery('select rtrim(SCHOOL) as SCHOOL,rtrim(NAME) as NAME from SCHOOLS');
        $this->assign('school',$arr);
        $tplarr=array();
        foreach($arr as $key=>$val){
            $tplarr[$val['SCHOOL']]=$val['NAME'];
        }
        $this->assign('tplarr',json_encode($tplarr));
        $this->xiala('schools','schools');

        $this->display();
    }


    /**
     * @function 获取列表JSON数据
     * @param $bind
     * @param $countpath
     * @param $selectpath
     * @param null $pagesize
     * @return mixed  jsonarray
     */
    private function getListJson($bind,$countpath,$selectpath,$pagesize = null){
        $total = $this->md->sqlQuery($this->md->getSqlMap($countpath),$bind);
        $json['total'] = $total[0]['ROWS'];
//        varDebug($this->md->getSqlMap($countpath));
//        varDebug($total,$this->md->getSqlMap($countpath),$this->md->getLastSql(),$bind);
        $expect = $this->_pageDataIndex + ($pagesize?$pagesize:$this->_pageSize);
        if($json['total']>0){
            $sql = $this->md->getSqlMap($selectpath);
            $bind[':start'] = $this->_pageDataIndex;
            $bind[':end'] = $expect > $json['total']?$json['total']:$expect;
            $json["rows"] = $this->md->sqlQuery($sql, $bind);
        }else{
            $json["rows"] = array();
        }
        return $json;
    }



    //todo:素质统一：终审
    public function Qualitycountdean(){
        if(isset($_GET['list']) && ($_GET['list'] == 1)){
            $bind = array(':projectname'=>doWithBindStr($_POST['projectname']),
                ':subschool'=>doWithBindStr($_POST['subschool']."%"),
                ':ctype'=>$_POST['ctype'],
                ':status'=>doWithBindStr($_POST['status']),
                ':studentno'=>doWithBindStr($_POST['studentno']),
                ':schoolno'=>doWithBindStr($_POST['schoolno']));
            $json = $this->getListJson($bind,"credit/tuanwei_Three_three_count.SQL",'credit/tuanwei_Three_three_select.SQL');
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        if(isset($_GET['tag']) && (trim($_GET['tag']) == 'exportexcel')){

            $this->countExcelExport();

        }
        //todo:通过不通过
        if(isset($_GET['tag']) && ($_GET['tag'] == 'pass')) {
            $sql = 'update creditcount set status=:status where recno=:recno';
            $bind = array(
                ':status'=>$_POST['status'],
                ':recno'=>$_POST['recno']
            );
            $res = $this->md->sqlExecute($sql,$bind);
            if($res && $res>0){
                exit('success');
            }else{
                exit('failure');
            }
        }
        //todo:删除
        if(isset($_GET['tag']) && ($_GET['tag'] == 'delete')) {
            $sql = 'delete from creditcount where  recno=:recno';
            $bind = array(
                ':recno'=>$_POST['recno']
            );
            $res = $this->md->sqlExecute($sql,$bind);
            if($res && $res>0){
                exit('success');
            }else{
                exit('failure');
            }
        }

        //todo:提交人和提交学院
        $this->assign('schoolname',$this->md->sqlFind('select TEACHERS.SCHOOL,TEACHERS.NAME,USERS.USERNAME from TEACHERS inner join USERS on TEACHERS.TEACHERNO=USERS.TEACHERNO where TEACHERS.TEACHERNO=:teacherno',array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO'])));
        //todo:报送学院多选
        $arr=$this->md->sqlQuery('select rtrim(SCHOOL) as SCHOOL,rtrim(NAME) as NAME from SCHOOLS');
        $this->assign('school',$arr);
        $tplarr=array();
        foreach($arr as $key=>$val){
            $tplarr[$val['SCHOOL']]=$val['NAME'];
        }
        $this->assign('tplarr',json_encode($tplarr));
        $this->xiala('schools','schools');
        $this->display();
    }





    //todo:判断是否在时间里
    public function is_time(){

        $timearr=$this->md->sqlQuery('select year,term,convert(varchar(20),begintime,20) as begintime,convert(varchar(20),endtime,20) as endtime from Creditapplydate where status=1');
        if(count($timearr)==1){
             $start=strtotime($timearr[0]['begintime']);
             $end=strtotime($timearr[0]['endtime']);
            if(time()<$start||time()>$end)
               return 'false';// exit('false');

        }
                 return 'true';    // exit('true');

    }




//{'Sqlpath':{'select':'credit/Two_two_selectStudent_list.SQL','count':'credit/Two_two_countStudent_list.SQL'},'bind':{
  //  ':year':$('[name=year]').val(),':term':$('[name=term]').val(),':studentno':$('[name=search_studentno]').val(),':studentname':$('[name=search_name]').val(),':projectname':$('[name=search_projectname]').val(),':projecttype':$('[name=search_projecttype]').val(),
    //                    ':cone':arr[0],':ctwo':arr[1],':v1':'1',':v2':'2',':v3':'1',':schoolname':schoolname
      //              }})




    //todo:导出excel2  教务处终审
    public function exportexcel2(){
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
       $title=$_POST['e_YEAR'].'学年'.$_POST['e_TERM'].'学期审核通过汇总表';
        $objPHPExcel->getActiveSheet()->setTitle($_POST['e_YEAR'].'学年'.$_POST['e_TERM'].'学期审核通过汇总表');

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置默认内容居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("D:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        /*
                //标题设置
                $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
                $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
                $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
                $objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小*/

        //列名设置
        $objPHPExcel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style);//字体样式
        //单元格内容写入
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"学号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B1',"姓名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C1',"项目名称");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D1',"学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E1',"项目认定时间");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F1',"编号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G1',"创建时间");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H1',"终审意见");
        //插入查询数据
        /*$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
            ":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        */

        $data=$this->md->sqlQuery($this->md->getSqlMap('credit/Two_three_selectStudent_list2.SQL'),
            array(':year'=>$_POST['e_YEAR'],':term'=>$_POST['e_TERM'],':studentno'=>doWithBindStr($_POST['e_STUDENTNO']),
                ':studentname'=>doWithBindStr($_POST['e_NAME']),':projectname'=>doWithBindStr($_POST['e_PROJECTNAME']),':projecttype'=>doWithBindStr($_POST['e_PROJECTTYPE']),
                ':cone'=>$_POST['e_CONE'],':ctwo'=>$_POST['e_CTWO'],':schoolname'=>doWithBindStr($_POST['e_SCHOOLNAME']),':d1'=>1,':d2'=>2));


        $row=1;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", trim($val['Studentno']));
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['projectname']));
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['credit']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['certficatetime']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['firmno'].' ');
            $objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['createdate']);
            $objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['deanview']);

        }
        //边框设置
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


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

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }






    //todo:导出excel
    public function exportexcel(){

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
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"]."学期通过学生汇总表";
        $objPHPExcel->getActiveSheet()->setTitle('汇总表');

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置默认内容居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("D:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(35);
        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //列名设置
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        //单元格内容写入
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"学号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B1',"姓名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C1',"项目名称");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D1',"学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E1',"项目认定时间");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F1',"编号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G1',"创建时间");
        //插入查询数据

        $data=$this->md->sqlQuery($this->md->getSqlMap($_POST['select']),    //Excel_one.sql
            array(':YEAR'=>$_POST['e_YEAR'],':TERM'=>$_POST['e_TERM'],':STUDENTNO'=>doWithBindStr($_POST['e_STUDENTNO']),
            ':NAME'=>doWithBindStr($_POST['e_NAME']),':PROJECTNAME'=>doWithBindStr($_POST['e_PROJECTNAME']),':PROJECTTYPE'=>doWithBindStr($_POST['e_PROJECTTYPE']),
            ':CONE'=>$_POST['e_CONE'],':CTWO'=>$_POST['e_CTWO'],':SCHOOLNAME'=>$_POST['e_SCHOOLNAME']));

        $row=1;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", trim($val['Studentno']));
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['projectname']));
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['credit']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['certficatetime']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['firmno'].' ');
            $objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['createdate']);

        }
        //边框设置
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename =$title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function shenqingdan(){
        $this->assign('shenqingdan',$_GET['id']);
        $this->display();
    }

    //todo:批量通过
    public function piliang(){
      /*  echo '<pre>';
        print_r($_POST);
        exit;*/
        $this->md->startTrans();
        foreach($_POST['bind'] as $val){
           $bool= $this->md->sqlExecute("update Creditbatchfirm set Final_status = 1,Final_time=getdate() where batchfirm_id=:id",
            array(':id'=>$val['batchfirm_id']));
            if($bool===false){
                $this->md->rollback();
                exit('批量处理中,出现错误');
            }
        }
        $this->md->commit();
        exit('批量通过成功');
    }



    public function select_file(){
        if(isset($_GET['list']) && ($_GET['list'] == 1)){
            $bind = array(':studentno'=>doWithBindStr($_POST['studentno']),
                ':projectname'=>doWithBindStr($_POST['projectname']),
                ':lx'=>doWithBindStr($_POST['lx']),
                ':lb'=>doWithBindStr($_POST['lb']),
                ':year'=>$_POST['year'],
                ':term'=>$_POST['term'],
                ':schoolno'=>doWithBindStr($_POST['schoolno'])
            );
            $json = $this->getListJson($bind,"credit/countAddcredit.SQL",'credit/selectAddcredit.SQL');
            $this->ajaxReturn($json,"JSON");
            $this->xiala('schools','schools');
            exit;
        }

        if($this->_hasJson){
            set_time_limit(0);
            $this->md=new SqlsrvModel();
            vendor("PHPExcel.PHPExcel");
            $this->objPHPExcel = new PHPExcel();
            $this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $title="{$_POST['year_e']}学年第{$_POST['term_e']}学期学分认定汇总表";
            $this->objPHPExcel->getActiveSheet()->setTitle($title);
            //合并单元格
            $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:J1");   //todo:标题合并单元格
            //设置默认字体和大小
            $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
            $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(40);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(16);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(20);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(12);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(12);
            //设置个别列内容居中
            $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置宽度
            //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

            //设置单元格字体加粗，居中样式
            $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
            $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

            $this->objPHPExcel->getActiveSheet()->getStyle("A1:J1")->applyFromArray($style);//字体样式
            $this->objPHPExcel->getActiveSheet()->getStyle("A2:J2")->applyFromArray($style);//字体样式
            //单元格内容写入
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:J2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


            $bind = array(':studentno'=>doWithBindStr($_POST['studentno_e']),':projectname'=>doWithBindStr($_POST['projectname_e']),
                ':lx'=>doWithBindStr($_POST['lx_e']),':lb'=>doWithBindStr($_POST['lb_e']),':year'=>$_POST['year_e'],':term'=>$_POST['term_e'],
                ':schoolno'=>doWithBindStr($_POST['sc_e']));
            $data=$this->md->sqlQuery($this->md->getSqlMap('Credit/ExcelAddcredit.SQL'),$bind  );

//            varDebug($bind,$data);

            $row=2;

            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"项目名称");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"班级");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"类型");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"认定时间");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"学院");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"证书时间");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"类别");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"学分");


            foreach($data as $val){
                $row++;
                $this->objPHPExcel->getActiveSheet()->getStyle("A$row:J$row")->applyFromArray($style2);//字体样式
                //todo:边框设置
                $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:J$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
                $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['reason']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['name']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",' '.trim($val['studentno']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['clsnm']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",(trim($val['lx']) == 1)?'创新':'素质');
                $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['rd_time']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['bs']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['zssj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['lb']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['credit']));

            }
            $this->shuchu($title);


        }
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


    public function insertstudents(){
        $boolean=true;
        if(isset($_POST['bind']['insert'])){
            $this->md->startTrans();
            foreach($_POST['bind']['insert'] as $val){
                $name=$this->md->sqlFind('select name from students where studentno=:studentno',array(':studentno'=>$val['studentno']));
                if(!$name){
                    exit("{$val['studentno']}该学生不存在,请检查您所填写的学号是否正确");
                }

                $pd=$this->md->sqlExecute("insert into creditcount(studentno,year,term,certficatetime,credittype,credit,subschool,projectname,status)
                values(:studentno,:year,:term,:certficatetime,:credittype,:credit,:subschool,:projectname,3)",
                    array(':studentno'=>$val['studentno'],':year'=>$_POST['year'],':term'=>$_POST['term'],':certficatetime'=>$val['certficatetime'],
                        ':credittype'=>$_POST['credittype'],':credit'=>$val['credit'],':subschool'=>$_POST['subschool'],':projectname'=>$val['projectname']));
                if(!$pd){
                    $this->md->rollback();
                    exit("{$val['studentno']}学生{$val['projectname']}证书插入失败");
                }
            }
        }
        if(isset($_POST['bind']['update'])){
            $this->md->startTrans();
            foreach($_POST['bind']['update'] as $val){
                $pd=$this->md->sqlExecute("update creditcount set projectname=:projectname,credit=:credit,certficatetime=:time where recno=:recno",
                    array(':projectname'=>$val['projectname'],':credit'=>$val['credit'],':time'=>$val['certficatetime'],
                        ':recno'=>$val['recno']));
                if(!$pd){
                    $this->md->rollback();
                    exit("{$val['studentno']}学生{$val['projectname']}证书修改失败");
                }
            }
        }
        if($boolean){
            $this->md->commit();
            exit('操作成功');
        }
    }

    public function updateStudents(){

        $pd=$this->md->sqlExecute("update creditcount set projectname=:projectname,credit=:credit,certficatetime=:time where recno=:recno",
        array(':projectname'=>$_POST['bind']['projectname'],':credit'=>$_POST['bind']['credit'],':time'=>$_POST['bind']['certficatetime'],
        ':recno'=>$_POST['bind']['recno']));
       if($pd){
           exit('修改成功');
       }else{
           exit('修改失败');
       }
    }


    public function delstudents(){
        $this->md->startTrans();
        foreach($_POST['bind'] as $val){
            $int=$this->md->sqlExecute("delete from creditcount where recno=:recno",array(':recno'=>$val['recno']));
            if(!$int){
                $this->md->rollback();
                exit('删除失败');
            }
        }
             $this->md->commit();
             exit('删除成功');
        }
}
?>