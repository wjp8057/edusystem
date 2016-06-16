<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class XqcExamAction extends RightAction {
    private $md;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }

    //todo:安排监考教师的页面
    public function invigilate(){
        $this->xiala('schools','schools');
        $this->display();
    }



    //todo:本科学位课筛选
    public function DegreeScreening(){
        if($this->_hasJson){
            if($_POST['TERM']>1){
                $_POST['TERM']=$_POST['TERM']-1;
            }else{
                $_POST['YEAR']=$_POST['YEAR']-1;
                $_POST['TERM']=$_POST['TERM']+1;
            }
            $COURSENO=substr($_POST[':COURSENO'],0,7);
            $GROUP=substr($_POST[':COURSENO'],7);
            //todo:查询要筛选的课存在不存在
            $course=$this->md->sqlFind("select * from SCORES WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND (PLANTYPE='M' or PLANTYPE='T') and COURSENO='$COURSENO' and [group]='$GROUP'");
            if(!$course){
                exit('该课未找到');
            }

                $total=$this->md->sqlExecute($this->md->getSqlMap('exam/DegreeScreening_insert75.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>$_POST[':COURSENO']));
                var_dump($total);
                exit;
        }
        $this->display();
    }


        //todo:学期初初始化
        public function chushihua(){
            if($_POST['TERM']>1){
                $_POST['TERM']=$_POST['TERM']-1;
            }else{
                $_POST['YEAR']=$_POST['YEAR']-1;
                $_POST['TERM']=$_POST['TERM']+1;
            }

            $this->md->startTrans();
               $this->md->sqlExecute("delete from TestStudent where status=0");


            //todo:60分
            $this->md->sqlExecute($this->md->getSqlmap("exam/DegreeScreening_insert_new.SQL"),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

            //todo:75分的
            $this->md->sqlExecute($this->md->getSqlmap("exam/DegreeScreening_insert75_new.SQL",array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'])));

              /*//todo;插入到student
               $this->md->sqlExecute($this->md->getSqlMap('exam/DegreeScreening_insert.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));*/
              //todo:缓考的学生
            $this->md->sqlExecute($this->md->getSqlMap('exam/DegreeScreening_insert_hk.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
            //todo:删除COURSE
            $this->md->sqlExecute("delete from TestCourse");
            //todo:插入要补考的课程
            $this->md->sqlExecute($this->md->getSqlMap('exam/DegreeScreening_insert_course.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

 
            $this->md->commit();
        }



    //todo:补考名单查询
    public function examQuery(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function kaowuquery(){
        $this->xiala('schools','schools');
        $this->display();
    }





}





?>