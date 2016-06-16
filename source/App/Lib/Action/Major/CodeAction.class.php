<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class CodeAction extends RightAction{
    private $md;        //存放模型对象
    private $base;      //路径
    /*
     * 专业代码管理首页
     * [CODE] => 510202
    [NAME] => 园林技术1
    [ROW_NUMBER] => 2
     */
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
       // $this->base='CourseManager/';
    }

    public function index(){
        $this->display();
    }

    /**
     * 专业代码管理
     *
     **/
    public function codeq()
    {
        if($this->_hasJson)
        {
            $xy=M('majorcode');
            $arr['total']=$xy->where($this->cowhere())->count();
            if($arr['total']>0)
                $arr['rows']=$xy->where($this->cowhere())->limit($this->_pageDataIndex,$this->_pageSize)->select(); //$this->_pageSize   请求多少行(该语句返回查询到的数组)
            else
                $arr['rows']=array();
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->display();

    }
    // 组合where条件
    public function cowhere()
    {
        // 判断where条件
        $data=array();
        if(trim($_POST['CODE'])!='')
        {
            $data['CODE']=array('like',array("{$_POST['CODE']}"));
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
    public function insertco()
    {
        $shuju=M('majorcode');
        $sql=$shuju->add($_POST);
        //$arr=$_POST['data'];
        if($sql) echo true;
        else echo false;
    }

    /*
     * 修改数据的方法
     */
    public function updateco()
    {
        //$shuju=M('majorcode');
     /*   [CODE] => 510202
    [NAME] => 园林技术1
    [ROW_NUMBER] => 2*/
        //$data=array();
        //$data['CODE']=$_POST['CODE'];
        $pd=$this->md->sqlExecute("update majorcode set code=:code,name=:name where code=:ctwo",
        array(':code'=>$_POST['CODE'],':name'=>$_POST['NAME'],':ctwo'=>$_POST['CODE']));


        //$pd=$shuju->where($data)->save($_POST);
        if($pd)
            echo true;
        else
            echo false;
    }

    public function deleteco()
    {
        //$shuju=M('majorcode');
        //$data=array();
        $str='';
        foreach($_POST['in'] as $val){
            $str.=$val.',';
        }
        $str=rtrim($str,',');
        /*
        $data['CODE']=array('in',$_POST['in']);
        echo '<pre>';
        print_r($_POST);*/


        $arr=$this->md->sqlExecute('delete from majorcode where code in(:code)',
        array(':code'=>$str));

     //   $arr=$shuju->where($data)->delete();
        if($arr)
            echo true;
        else
            echo false;
    }

}
?>