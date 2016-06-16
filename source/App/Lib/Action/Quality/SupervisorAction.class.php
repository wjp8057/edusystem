<?php
/**
 * 督导评教
 * User: shencp
 * Date: 2015-05-14
 * Time: 下午12.59
 */
class SupervisorAction extends RightAction{
    private $model;
    private $objPHPExcel;
    private $style;
    /**
     * 构造函数
     **/
    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    /******************************督导评教*********************************/
    //督导评教-列表
    public function evaluation(){
        if($this->_hasJson){
            $supervisor=trim($_POST["supervisor"]);
            $supervisor=$supervisor==""?"%":$supervisor;
            $bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"],
                ":COURSENO"=>doWithBindStr($_POST["courseno"]),":COURSENAME"=>doWithBindStr($_POST["coursename"]),
                ":TEACHERNO"=>doWithBindStr($_POST["teacher"]),":NAME"=>doWithBindStr($_POST["teacher"]),":SUPERVISOR"=>$supervisor);

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Quality/Q_evaluation_ddpj.SQL");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->assign("teacherno",$_SESSION["S_USER_INFO"]["TEACHERNO"]);
        $this->display();
    }
    //获取验证教师号或课号是否存在
    public function ajaxGetCount(){
        if(trim($_POST["type"])=="teacher"){
            $count = $this->model->sqlCount("select count(*) from teachers where teacherno = :teacherno",array(":teacherno"=>trim($_POST["value"])));
        }else{
            $courseNo = substr(trim($_POST["value"]),0,7); //7位课号
            $group = substr(trim($_POST["value"]),7); //2位组号
            $count = $this->model->sqlCount("select count(*) from scheduleplan where [year]=:year and term=:term and courseno=:courseno and [group]=:group",
                array(":year"=>$_POST["year"],":term"=>$_POST["term"],":courseno"=>$courseNo,":group"=>$group) );
        }
        echo $count;
    }
    //保存督导评教信息
    public function save(){
        $score=$_POST["score"];
        $count = $this->model->sqlCount("select count(*) from evaluation_ddpj where teacherno=:teacherno and courseno=:courseno and year=:year and term=:term and supervisor=:supervisor",
            array(":teacherno"=>$_POST["teacherno"],":courseno"=>$_POST["courseno"],":year"=>$_POST["year"],":term"=>$_POST["term"],":supervisor"=>$_SESSION["S_USER_INFO"]["TEACHERNO"]));
        if(intval($count) > 0){
            echo -1;
            return;
        }else{
            $bind = array(":teacherno"=>$_POST["teacherno"],":courseno"=>$_POST["courseno"],
                ":year"=>$_POST["year"],":term"=>$_POST["term"],":score"=>$score,":ratio"=> 0,":supervisor"=>$_SESSION["S_USER_INFO"]["TEACHERNO"]);
            $this->model->startTrans();
            $row=$this->model->sqlExecute($this->model->getSqlMap("Quality/insertEvaluation_ddpj.SQL"),$bind);
            $row2=$this->updateRatio($_POST["year"],$_POST["term"],$_SESSION["S_USER_INFO"]["TEACHERNO"]);
            if($row && $row2) $this->model->commit();
            else $this->model->rollback();
        }
        echo $row;
    }
    //更新评分
    public function updateScore(){
        $num=0;
        $this->model->startTrans();
        foreach($_POST["list"] as $val){
            $row=$this->model->sqlExecute("update evaluation_ddpj set score=:score where id=:id",
                array(":score"=>$val["SCORE"],":id"=>$val["ID"]));
            if($row) $num++;
        }
        $row2=$this->updateRatio($_POST["year"],$_POST["term"],$_SESSION["S_USER_INFO"]["TEACHERNO"]);
        if($row && $row2) $this->model->commit();
        else $this->model->rollback();
        echo $num;
    }
    //删除
    public function del(){
        $num=0;$newids="";
        foreach($_POST["ids"] as $val){
            $newids.="'".$val."',";
            $num++;
        }
        $newids=rtrim($newids,",");
        $this->model->startTrans();
        $row=$this->model->sqlExecute("delete from evaluation_ddpj where id in($newids)");
        $row2=$this->updateRatio($_POST["year"],$_POST["term"],$_SESSION["S_USER_INFO"]["TEACHERNO"]);
        if($row && $row2) $this->model->commit();
        else $this->model->rollback();
        echo $num;
    }
    //更新督导评教折算系数
    private function updateRatio($year,$term,$supervisor){
        $bind=array(":year"=>$year,":term"=>$term,":supervisor"=>$supervisor);
        $data=$this->model->sqlQuery("select id,score from evaluation_ddpj where year=:year and term=:term and supervisor=:supervisor",$bind);
        if($data!=null && count($data) > 0){
            $sql="select CAST(avg(score) as decimal(38,2)) avgScore from evaluation_ddpj where year=:year and term=:term and supervisor=:supervisor";
            $avg=$this->model->sqlFind($sql,$bind);
            foreach($data as $val){
                $score=$val["score"];
                $ratio=$score/$avg["avgScore"];
                $sql="update evaluation_ddpj set ratio=:ratio where id=:id";
                $row=$this->model->sqlExecute($sql,array(":ratio"=>$ratio,":id"=>$val["id"]));
            }
            return $row;
        }
        return true;
    }
    /***************************************教师得分*************************************/
    //教师得分-列表
    public function queryScore(){
        if($this->_hasJson){
            $bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"],":NAME"=>doWithBindStr($_POST["name"]),
                ":TEACHERNO"=>doWithBindStr($_POST["teacherno"]),":SCHOOL"=>doWithBindStr($_POST["school"]));

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Quality/Q_score_ddpj.SQL");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        //学院
        $this->assign("school",M("schools")->select());
        $this->display();
    }
    //教师得分详细
    public function queryDetail(){
        if($this->_hasJson){
            $bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"],":TEACHERNO"=>doWithBindStr($_POST["teacherno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Quality/Q_detail_ddpj.SQL");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
    }
    //教师得分导出
    public function expScore(){
        //生成工作簿
        vendor("PHPExcel.PHPExcel");
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
        //设置单元格自动换行
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
        //设置默认内容垂直居左
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置单元格加粗，居中样式
        $this->style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期督导评教教师得分汇总表";
        $this->objPHPExcel->getActiveSheet(0)->setTitle($title);
        //设置宽度
        $this->objPHPExcel->getActiveSheet(0)->getDefaultColumnDimension()->setWidth(15);
        $this->objPHPExcel->getActiveSheet(0)->getColumnDimension("D")->setWidth(25);

        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:D1');//合并A1单元格到Q1
        $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(12);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension("2")->setRowHeight(20);//设置行高
        $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","姓名");
        $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue("B2","教师号");
        $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue("C2","平均系数");
        $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue("D2","所在学院");
        //数据检索
        $bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"],":NAME"=>doWithBindStr($_POST["name"]),
            ":TEACHERNO"=>doWithBindStr($_POST["teacherno"]),":SCHOOL"=>doWithBindStr($_POST["school"]));
        $sql = $this->model->getSqlMap("Quality/Q_score_ddpj.SQL");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['NAME']);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("B$row", $val['TEACHERNO'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['RATIO']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['SCHOOLNAME']);
        }
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        //创建第二个工作表
        $title2="{$_POST["year"]}学年{$_POST["term"]}学期督导评教教师得分明细表";
        $workSheet = new PHPExcel_Worksheet($this->objPHPExcel,$title2); //创建一个工作表
        $this->objPHPExcel->addSheet($workSheet); //插入工作表
        $this->objPHPExcel->setActiveSheetIndex(1); //切换到新创建的工作表
        //设置课号居左
        $this->objPHPExcel->getActiveSheet(1)->getStyle("C")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置宽度
        $this->objPHPExcel->getActiveSheet(1)->getDefaultColumnDimension()->setWidth(15);
        $this->objPHPExcel->getActiveSheet(1)->getColumnDimension("C")->setWidth(35);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:E1');//合并A1单元格到Q1
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue('A1',$title2);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(12);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:E2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension("2")->setRowHeight(20);//设置行高
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue("A2","姓名");
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue("B2","教师号");
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue("C2","课程名称");
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue("D2","评分");
        $this->objPHPExcel->setActiveSheetIndex(1)->setCellValue("E2","折算系数");
        $row=2;
        foreach($data as $val){
             $sql = $this->model->getSqlMap("Quality/Q_detail_ddpj.SQL");
             $list=$this->model->sqlQuery($sql,array(":YEAR"=>$val["YEAR"],":TERM"=>$val["TERM"],":TEACHERNO"=>$val["TEACHERNO"]));
             foreach($list as $d){
                 $row++;
                 $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $d['NAME']);
                 $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("B$row", $d['TEACHERNO'],PHPExcel_Cell_DataType::TYPE_STRING);
                 $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $d['COURSENAME']);
                 $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $d['SCORE']);
                 $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $d['RATIO']);
             }
        }
        $this->objPHPExcel->getActiveSheet(1)->getStyle("A2:E$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
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

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /***************************************督导名单管理*************************************/
    //督导名单-列表
    public function superviserList(){
        if($this->_hasJson){
            $bind = array(":NAME"=>doWithBindStr($_POST["name"]),":TEACHERNO"=>doWithBindStr($_POST["teacherno"]),":SCHOOL"=>doWithBindStr($_POST["school"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Quality/Q_superviser_list.SQL");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        //学院
        $this->assign("school",M("schools")->select());
        //编辑所用数据
        $sexcode = M("sexcode")->query("select rtrim(code) value,rtrim(name) text from __TABLE__");
        $school = M("schools")->query("select rtrim(school) value,rtrim(name) text from __TABLE__");
        $position=M("positions")->query("select rtrim(name) value,rtrim(value) text from __TABLE__");
        $this->assign("sex",json_encode($sexcode));
        $this->assign("sch",json_encode($school));
        $this->assign("position",json_encode($position));

        $this->display();
    }
    //查询教师-列表
    public function queryTeacherList(){
        if($this->_hasJson){
            $bind = array(":NAME"=>doWithBindStr($_POST["name"]),":TEACHERNO"=>doWithBindStr($_POST["teacherno"]),":SCHOOL"=>doWithBindStr($_POST["school"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Quality/Q_teacher_list.SQL");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
    }
    //添加督导名单
    public function updateRoles(){
        $num=0;
        foreach($_POST["list"] as $val){
            $roles=$val["ROLES"]."0";
            $row=$this->model->sqlExecute("update users set roles=:roles where teacherno=:teacherno",
                array(":roles"=>$roles,":teacherno"=>$val["TEACHERNO"]));
            if($row) $num++;
        }
        echo $num;
    }
    //删除督导
    public function delSuperviser(){
        $num=0;
        foreach($_POST["list"] as $val){
            $roles=preg_replace("/0/", "", $val["ROLES"]);
            $row=$this->model->sqlExecute("update users set roles=:roles where teacherno=:teacherno",
                array(":roles"=>$roles,":teacherno"=>$val["TEACHERNO"]));
            if($row) $num++;
        }
        echo count($_POST["list"]);
    }
    //更新督导信息
    public function updateTeacher(){
        $num=0;
        $this->model->startTrans();
        foreach($_POST["list"] as $val){
            $sql="update teachers set name=:name";
            $bind= array(":name"=>$val["NAME"]);
            $school=trim($val["SCHOOLNAME"]);//学院
            if(strlen($school) < 3 && $school!=""){
                $sql=$sql.",school=:school";
                $bind[":school"]=$school;
            }
            $sex=trim($val["SEX"]);//性别
            if(strlen($sex) < 3 && $sex!=""){
                $sql=$sql.",sex=:sex";
                $bind[":sex"]=$sex;
            }
            $position=trim($val["POSITION"]);//职称
            if(strlen($position) < 3){
                $sql=$sql.",[position]=:position";
                $bind[":position"]=$position;
            }
            $sql=$sql." where teacherno =:teacherno";
            $bind[":teacherno"]=trim($val["TEACHERNO"]);
            $row=$this->model->sqlExecute($sql,$bind);
            if($row) $num++;
        }
        if($num == count($_POST["list"])){
            $this->model->commit();
            echo true;
        }else {
            $this->model->rollback();
            echo false;
        }
    }
}