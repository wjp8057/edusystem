<?php
/**
 * 我的学业
 * User: educk
 * Date: 13-12-25
 * Time: 下午3:36
 */
class LearningAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * 我的教材
     */
    public function mybooks(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("STUDENTNO",array(session("studnet_studentno")));
            $sql = $this->model->getSqlMap("Learning/myBooks.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("mybooks");
    }

    /**
     * {ACT]培养方案
     */
    public function  program(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            if(isset($_REQUEST["SCHOOLCODE"])){

                $bind = $this->model->getBind("SCHOOL",array($_REQUEST["SCHOOLCODE"]));
                $sql = $this->model->getSqlMap("Learning/studentQueryProgram.sql");
            }else{

                $bind = $this->model->getBind("STUDENTNO",array(session("studnet_studentno")));
                $sql = $this->model->getSqlMap("Learning/studentr28.sql");
            }
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("program");
    }

    /**
     * {ACT]培养方案详细列表
     */
    public function programDetail(){
        $json = array("total"=>0, "rows"=>array());
        $bind = $this->model->getBind("PROGRAMNO",array($_REQUEST['programNo']));
        $json["rows"] = $this->model->sqlQuery($this->model->getSqlMap("Learning/programCourse.sql"), $bind);
        $json["total"] = count($json["rows"]);

        for($i=0; $i<$json["total"]; $i++){
            $json["rows"][$i]["WEEKS"] = decbin($json["rows"][$i]["WEEKS"]);
        }
        $this->ajaxReturn($json,"JSON");
        exit;
    }

    /**
     * 学籍信息
     */
    public function student(){
        $data = $this->model->sqlFind($this->model->getSqlMap("Learning/statusQueryStudent.sql"),array(":STUDENTNO"=>session("studnet_studentno")));
        $list = $this->model->sqlFind($this->model->getSqlMap("Learning/queryStudentRegistry.sql"),array(":STUDENTNO"=>session("studnet_studentno")));
        $this->assign("info",$data);
        $this->assign("list",$list);
        $this->display("student");
    }

    /**
     * [ACT]等级考试
     */
    public function level(){

        $this->display("level");
    }



    /*
     * 看了别生气
     */
    public function delete(){
        $this->display();
    }


    /*
     * 空教室
     */
    public function NULL_Rooms(){
        $this->assign('year_term',$this->model->sqlFind("select * from year_term where TYPE='C'"));

        $this->xiala('timesectors','timesectors');
        $this->xiala('areas','areas');
        $this->display();
    }


    public function selectshiyong(){
        $shuju=new SqlsrvModel();
        $mo=array(':MON'=>'0',':TUE'=>'0',':WES'=>'0',':THU'=>'0',':FRI'=>'0',':SAT'=>'0',':SUN'=>'0',':ROOMNO'=>doWithBindStr($_POST['arr']['ROOMNO']),':JSN'=>doWithBindStr($_POST['arr']['JSN']),':SCHOOL'=>doWithBindStr($_POST['arr']['SCHOOL']),':EQUIPMENT'=>doWithBindStr($_POST['arr']['EQUIPMENT']),':AREA'=>doWithBindStr($_POST['arr']['AREA']),':SEATSDOWN'=>0,':SEATSUP'=>1000,':YEAR'=>$_POST['arr']['YEAR'],':TERM'=>$_POST['arr']['TERM'],':TYPE'=>'R');
        if($_POST['arr']['DAY']!=-1){
            $mo[$_POST['arr']['DAY']]=-1;

        }
        $arr2=$shuju->sqlFind($shuju->getSqlMap('CourseManager/Four_select_Room_count.SQL'),$mo);


        if($arr2['ROWS']==0){

            exit;
        }
        $ar['total']=$arr2['ROWS'];
        $ar['page']=ceil($arr2['ROWS']/$this->_pageSize);
        if($_POST['page']>=$ar['page']){
            $ar['nowpage']=$ar['page'];
        }else if($_POST['page']<1){
            $ar['nowpage']=1;
        }else{
            $ar['nowpage']=$_POST['page'];
        }



        $mo2=array(':MON'=>'0',':TUE'=>'0',':WES'=>'0',':THU'=>'0',':FRI'=>'0',':SAT'=>'0',':SUN'=>'0',':ROOMNO'=>doWithBindStr($_POST['arr']['ROOMNO']),':JSN'=>doWithBindStr($_POST['arr']['JSN']),':SCHOOL'=>doWithBindStr($_POST['arr']['SCHOOL']),':EQUIPMENT'=>doWithBindStr($_POST['arr']['EQUIPMENT']),':AREA'=>doWithBindStr($_POST['arr']['AREA']),':SEATSDOWN'=>0,':SEATSUP'=>1000,':YEAR'=>$_POST['arr']['YEAR'],':TERM'=>$_POST['arr']['TERM'],':start'=>($ar['nowpage']-1)*$this->_pageSize,':end'=>$ar['nowpage']*$this->_pageSize);
        if($_POST['arr']['DAY']!=-1){

            $mo2[$_POST['arr']['DAY']]=-1;
        }
        $arr=$shuju->sqlQuery($shuju->getSqlMap('CourseManager/Four_select_Room.SQL'),$mo2);

        $str='';

        foreach($arr as $key=>$val){
            $str.='<tr>';
            $str.='<td><a href="javascript:void(0)" onclick="tanchu(this)">'.$val['ROOMNO'].'</a></td>';        //教室号
            $str.='<td>'.$val['JSN'].'</td>';           //简称
            $str.=$this->zhou($val['MON']);        //星期1
            $str.=$this->zhou($val['TUE']);        //星期2
            $str.=$this->zhou($val['WES']);        //星期3
            $str.=$this->zhou($val['THU']);        //星期4
            $str.=$this->zhou($val['FRI']);        //星期5
            $str.=$this->zhou($val['SAT']);        //星期6
            $str.=$this->zhou($val['SUN']);        //星期7
            $str.='</tr>';
        }
        $ar['str']=$str;


        echo json_encode($ar);
    }

    public function zhou($num){
        $str2='';
        $str=str_pad(strrev(decbin($num)),24,0);
        $arr=explode('.',trim(chunk_split($str,2,'.'),'.'));
        $num=count($arr);
        if($num>12){
            $arr=array('11','11','11','11','11','11','11','11','11','11','11','11');
        }
        foreach($arr as $val){
            switch($val){
                case '00':
                    $str2.='<td>&nbsp</td>';
                    break;
                case '01':
                    $str2.='<td>E</td>';
                    break;
                case '10':
                    $str2.='<td>D</td>';
                    break;
                case '11':
                    $str2.='<td>B</td>';
                    break;
            }
        }
        return $str2;
    }
}