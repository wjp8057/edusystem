<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-11-27
 * Time: 上午11:09
 */
class BaseAction extends RightAction
{
    private $mdl;
    public function __construct(){
        parent::__construct();
        $this->mdl=new SqlsrvModel();
    }


    //组合where条件
    private function scwhere(){
        //判断where条件
        $data=array();
        if(trim($_POST['SCHOOL'])!=''){
            $one=trim($_POST['SCHOOL']);
            $data['SCHOOL']=array('like',array("$one"));
        }
        if(trim($_POST['NAME'])!=''){
            $two=trim($_POST['NAME']);
            $data['NAME']=array('like',array("$two"));
        }
        $pd=count($data)==0?"":$data;
        return $pd;
    }


    /*
     * 插入数据的方法
     */
    public function inserted(){
        $shuju=M('schools');
       foreach($_POST as $key=>$value){
           $ct[$key]=trim($value);
       }

        $sql=$shuju->add($ct);

        if($sql){
            echo 'true';                                          //测试用下
        }else{
            echo 'false';
        }
    }

    /*
     * 修改数据的方法
     */
    public function updated(){
        $shuju=M('schools');
        $data=array();
        $data['SCHOOL']=$_POST['SCHOOL'];
        $pd=$shuju->where($data)->save($_POST);
        if($pd)
            echo 'true';
        else
            echo 'false';
    }

    /*
     * 删除学院数据的方法
     */
    public function deleted(){
        $shuju=M('schools');
        $data=array();
        $data['SCHOOL']=array('in',$_POST['in']);
        $arr=$shuju->where($data)->delete();
        if($arr)
            echo true;
        else
            echo false;
    }


    /**
     *  学院模块
     */
    public function school(){
        if($this->_hasJson){
            $xy=M('schools');
            $arr['total']=$xy->where($this->scwhere())->count();
            if($arr['total']>0){
                $arr['rows']=$this->mdl->sqlQuery('select * from(select row_number() over(order by SCHOOL) as row,RTRIM(SCHOOL) AS SCHOOL,RTRIM(NAME) AS NAME FROM schools) as b where b.row between :start and :end',array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $arr['rows'] = array();
            };
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->display();
    }
    /**
     *  职称管理
     *
     **/
    public function positions()
    {
        if($this->_hasJson)
        {
            $xy=M('positions');
            $arr['total']=$xy->where($this->powhere())->count();
            if($arr['total']>0)
                $arr['rows']=$xy->where($this->powhere())->limit($this->_pageDataIndex,$this->_pageSize)->select(); //$this->_pageSize   请求多少行(该语句返回查询到的数组)
            else
                $arr['rows']=array();
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
          $this->display();
    }

    // 组合where条件
    public function powhere()
    {
        // 判断where条件
        $data=array();
        $_POST['VALUE']=trim($_POST['VALUE']);
        $_POST['NAME']=trim($_POST['NAME']);
        if(trim($_POST['VALUE'])!='')
        {
            $data['VALUE']=array('like',array("{$_POST['VALUE']}"));
        }
        if(trim($_POST['NAME'])!='')
        {
            $data['NAME']=array('like',array("{$_POST['NAME']}"));
        }
        $pd=count($data)==0?"":$data;
        return $pd;
    }
    /*
     * 插入数据的方法
     */
    public function insertpo()
    {
        $shuju=M('positions');
        $sql=$shuju->add($_POST);
        //$arr=$_POST['data'];
        if($sql) echo true;
        else echo false;
    }

    /*
    * 修改数据的方法
    */
    public function updatepo()
    {
        $shuju=M('positions');
        $data=array();
        $data['NAME']=$_POST['NAME'];
        $pd=$shuju->where($data)->save($_POST);
        if($pd)
            echo true;
        else
            echo false;
    }

    public function deletepo()
    {
        $shuju=M('positions');
        $data=array();
        $data['NAME']=array('in',$_POST['in']);

        // $str=implode(',',$_POST['in']);
        // echo $str;

        $arr=$shuju->where($data)->delete();
        if($arr)
            echo true;
        else
            echo false;
    }

    /**
     * 角色管理
     *
     **/
    public function roles()
    {
        if($this->_hasJson)
        {
            $xy=M('roles');
            $arr['total']=$xy->where($this->rowhere())->count();
            if($arr['total']>0)
                $arr['rows']=$xy->where($this->rowhere())->limit($this->_pageDataIndex,$this->_pageSize)->select(); //$this->_pageSize   请求多少行(该语句返回查询到的数组)
            else
                $arr['rows']=array();
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->display();

    }
    // 组合where条件
    public function rowhere()
    {
        // 判断where条件
        $data=array();
        $_POST['ROLE']=trim($_POST['ROLE']);
        $_POST['DESCRIPTION']=trim($_POST['DESCRIPTION']);

        if(trim($_POST['ROLE'])!='')
        {
            $data['ROLE']=array('like',array("{$_POST['ROLE']}"));
        }
        if(trim($_POST['DESCRIPTION'])!='')
        {
            $data['DESCRIPTION']=array('like',array("{$_POST['DESCRIPTION']}"));
        }
        $pd=count($data)==0?"":$data;
        return $pd;
    }

    /* public function roles2()
     {

         $xy=M('roles');
         $str=$this->rowhere();                                                                                         // where条件组合

         $start=($this->_pageNo-1)*$this->_pageSize;                                                                    // limit起始位置
         $row=$this->_pageSize;                                                                                         // 请求多少行

         $roles=$xy->where($str)->limit($start,$row)->select();                                                         // 学院信息数组
         $total=$xy->where($str)->count();                                                                              // 总条数
         if(!$roles)
         {
             $roles=array('ROLE'=>"","DESCRIPTION"=>"");                                                                //  如果没查到数据 给个空数组 防止前台报错
             $total=0;
         }

         $arr=array();
         $arr['total']=$total;
         $arr['rows']=$roles;
         echo json_encode($arr);
     }*/

    /*
     * 插入数据的方法
     */
    public function insertro()
    {
        $shuju=M('roles');
        $sql=$shuju->add($_POST);
        //$arr=$_POST['data'];
        if($sql) echo true;
        else echo false;
    }

    /*
     * 修改数据的方法
     */
    public function updatero()
    {
        $shuju=M('roles');
        $data=array();
        //print($_POST);
        $data['ROLE']=$_POST['ROLE'];
        $pd=$shuju->where($data)->save($_POST);
        if($pd)
            echo true;
        else
            echo false;
    }

    public function deletero()
    {
        $shuju=M('roles');
        $data=array();
        $data['ROLE']=array('in',$_POST['in']);

        $arr=$shuju->where($data)->delete();
        if($arr)
            echo true;
        else
            echo false;
    }
	
	public function yearterm(){
        if($this->_hasJson){
            $count=$this->mdl->sqlFind("select count(*) as ROWS from (select row_number() over(order by type) as row,rem,year,term,type from year_term where rem like :rem) as b",
                array(':rem'=>$_POST['rem']));

            if($data['total']=$count['ROWS']){
                $data['rows']=$this->mdl->sqlQuery("select * from (select row_number() over(order by type) as row,rem,year,
                term,type from year_term where rem like :rem) as b where b.row between :start and :end",array_merge(array(':rem'=>$_POST['rem']),array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));
            }else{
                $data['total']=0;
                $data['rows']=array();
            }

            $this->ajaxReturn($data,'JSON');
            exit;
        }
        $this->display();
    }

    //todo:修改学年学期
    public function updateyearterm(){
        $this->mdl->starttrans;
        foreach($_POST['bind'] as $val){
            if(trim($val['term'])>2){
                exit('学期不能大于2');
            }
            $int=$this->mdl->sqlExecute("update year_term set year=:year,term=:term where type=:type",
            array(':year'=>$val['year'],':term'=>$val['term'],':type'=>$val['type']));
            if(!$int){
                exit($val['rem'].'修改的时候出错了！');
            }
        }
        $this->mdl->commit();
        exit('修改成功');

    }
}