<?php
/**
 * 考评结果查询
 * User: cwebs
 * Date: 14-2-12
 * Time: 下午3:43
 */
class ItemAction extends RightAction
{
    private $md;        //存放模型对象
    /**
     *  考评结果
     *
     **/
    public function __construct()
    {
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /*
     * 考评结果首页
     */
    public function index()
    {
        $this->display();
    }


    /*
     * 显示主页FORM的方法
     */
    public function additem()
    {
        $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $this->assign("yearTerm",$data);

        $sql="SELECT rtrim(TEACHERNO) AS CODE,rtrim(NAME) AS NAME FROM TEACHERS ORDER BY NAME";
        $teachername=$this->model->sqlquery($sql);
        $sjson4=array();
        foreach($teachername as $val)
        {
            $sjson2['text']=trim($val['NAME']);
            $sjson2['value']=$val['CODE'];                    // 把教师数据转成json格式给前台的combobox使用
            array_push($sjson4,$sjson2);
        }
        $sjson4=json_encode($sjson4);
        $this->assign('teachername',$teachername);
        $this->display();
    }


}