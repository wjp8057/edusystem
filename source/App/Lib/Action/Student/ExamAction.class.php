<?php
/**
 * 考试模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class ExamAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    public function qlist(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
          //  var_dump($_SESSION);
          //  var_dump(session("studnet_studentno"));
            $bind = $this->model->getBind("STUDENTNO,YEAR,TERM",array(session("S_USER_NAME"),intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"])));
            $sql = $this->model->getSqlMap("exam/studentExam.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("qlist");
    }

    public function seat(){
        $json = array("total"=>0, "rows"=>array(),"error"=>null,"info"=>array());
        $bind = $this->model->getBind("COURSENO,YEAR,TERM",$_REQUEST);
        $row = $this->model->sqlFind($this->model->getSqlMap("exam/examAll.sql"), $bind);
        if($row==false){
            $json["error"] = "没有找到指定课程[".$_REQUEST['COURSENO']."]的考场信息！";
        }else{
            if($_REQUEST["INDEX"]==1) {
                $this->_pageDataIndex = 0;
                $this->_pageSize = intval($row["RS1"]);
            }elseif($_REQUEST["INDEX"]==2) {
                $this->_pageDataIndex =intval($row["RS1"]);
                $this->_pageSize = intval($row["RS2"]);
            }elseif($_REQUEST["INDEX"]==3) {
                $this->_pageDataIndex = intval($row["RS1"])+intval($row["RS2"]);
                $this->_pageSize = intval($row["RS3"]);
            }

            $sql = $this->model->getPageSql(null,"exam/studentExamSeat.sql",$this->_pageDataIndex, $this->_pageSize);
            $data = $this->model->sqlQuery($sql,$bind);

            $rows = array();
            $j = 0;
            for($i=0; $i<count($data);$i+=3){
                for($ii =0; $ii<3; $ii++){
                    if(trim($data[$i+$ii]["STUDENTNO"])==session("studnet_studentno")){
                        $rows[$j]["S".($ii+1)] = "<font color='#0000FF'>".$data[$i+$ii]["STUDENTNO"]."</font>";
                        $rows[$j]["N".($ii+1)] = "<font color='#0000FF'>".$data[$i+$ii]["NAME"]."</font>";
                    }else{
                        $rows[$j]["S".($ii+1)] = $data[$i+$ii]["STUDENTNO"];
                        $rows[$j]["N".($ii+1)] = $data[$i+$ii]["NAME"];
                    }
                    $rows[$j]["Z".($ii+1)] = $i+$ii+1;
                    $rows[$j]["K".($ii+1)] = "";
               }
                /**
                $rows[$j] = array(
                    "S1"=>$data[$j]["STUDENTNO"],"N1"=>$data[$j]["NAME"],"Z1"=>$i+1,"K1"=>"",
                    "S2"=>$data[$j+1]["STUDENTNO"],"N2"=>$data[$j+1]["NAME"],"Z2"=>$i+2,"K2"=>"",
                    "S3"=>$data[$j+2]["STUDENTNO"],"N3"=>$data[$j+2]["NAME"],"Z3"=>$i+3,"K3"=>"",);**/
                $j++;
            }

            $json['rows'] = $rows;
            $json['total'] = count($rows);
            $json['info'] = $row;
        }
        $this->ajaxReturn($json,"JSON");
        exit;
    }


}