<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class FinalQueryAction extends RightAction {
        private $md;
        protected $message = array("type"=>"info","message"=>"","dbError"=>"");

        public function __construct(){
            parent::__construct();
            $this->md = M("SqlsrvModel:");
        }

        //todo:期末考试查询
        public function FinalExamQuery(){
            if(isset($_GET['tag']) && trim($_GET['tag']) == 'print'){
                $this->assign('post',json_encode($_POST));
                $this->display('printFinalQuery');
                exit;
            }
            $this->xiala('schools','schools');
            $this->display();
        }


        //todo:座位安排的页面
        public function zuoweianpai(){
            if($this->_hasJson){
                $count=$this->md->sqlFind($this->md->getSqlMap($_POST['Sqlpath']['count']),$_POST['bind']);
                if($data['total']=$count['ROWS']){
                    $data['rows']=$this->md->sqlQuery($this->md->getSqlMap($_POST['Sqlpath']['select']),$_POST['bind']);
                }else{
                    $data['rows']=array();
                }
                $this->ajaxReturn($data,'JSON');
                exit;
                
            }
            //todo:查询出标题信息：
          $title=$this->md->sqlfind($this->md->getSqlmap('exam/zuoweianpai_title.SQL'),array(':courseno'=>$_GET['COURSENO'],':year'=>$_GET['YEAR'],':term'=>$_GET['TERM']));

            //todo:判断考场几
            switch($_GET['KC']){
                case '1';               //todo:考场1
                    $this->assign('start',1);                   //todo:start
                    $this->assign('end',$title['renshu1']);     //todo:end
                    $this->assign('jiankao',array($title['tOne1'],$title['tOne2'],$title['tOne3']));        //todo:监考老师
                    $this->assign('endzz',$title['renshu1']);
                    break;
                case '2';               //todo:考场2
                    $this->assign('start',$title['renshu1']+1);
                    $this->assign('end',$title['renshu1']+$title['renshu2']);
                    $this->assign('endzz',$title['renshu2']);
                    $this->assign('jiankao',array($title['tTwo1'],$title['tTwo2'],$title['tTwo3']));        //todo:监考老师
                    break;
                case '3';               //todo:考场3
                    $this->assign('start',$title['renshu2']+1);
                    $this->assign('end',$title['renshu3']);
                    $this->assign('jiankao',array($title['tThree1'],$title['tThree2'],$title['tThree3']));        //todo:监考老师
                    $this->assign('endzz',$title['renshu3']);
                    break;

            }
            $this->assign('info',$title);
            $this->assign('get',$_GET);
            $this->display();
        }

        //todo:检索考位安排
        public function zuoweianpai2(){

            //todo:查询出标题信息：
            $title=$this->md->sqlfind($this->md->getSqlmap('exam/zuoweianpai_title.SQL'),array(':courseno'=>$_GET['COURSENO'],':year'=>$_GET['YEAR'],':term'=>$_GET['TERM']));
            //todo:标题信息2
            $title2=$this->md->sqlfind($this->md->getSqlmap('exam/zuoweianpai2_title.SQL'),array(':cone'=>$_GET['COURSENO'],':ctwo'=>$_GET['COURSENO'],':year'=>$_GET['YEAR'],':term'=>$_GET['TERM']));


            $this->assign('info',$title2);
            $this->assign('start',1);
            $this->assign('end',$title['rs']);
            $this->assign('jiankao',array($title['tThree1'],$title['tThree2'],$title['tThree3']));        //todo:监考老师
            $this->assign('endzz',$title['rs']);
            $this->assign('get',$_GET);
            $this->display();

        }



    //todo:检索期末考试冲突
    public function ExamConflictQuery(){
        if(isset($_GET['tag']) && trim($_GET['tag']) == 'getdata'){
            $bind = array(':YONE'=>$_POST['bind'][':YONE'],':TONE'=>$_POST['bind'][':TONE'],
                ':YTEN'=>$_POST['bind'][':YTEN'],':TTEN'=>$_POST['bind'][':TTEN'],
                ':school'=>$_POST['bind'][':school']);
            $json = $this->getListJson($bind,'exam/ExamConfictQuery_count.SQL','exam/ExamConfictQuery_select.SQL');
//            varsPrint($bind,$json,$this->md->getDbError());exit;
            $this->ajaxReturn($json,"JSON");
            exit;
        }
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



}


