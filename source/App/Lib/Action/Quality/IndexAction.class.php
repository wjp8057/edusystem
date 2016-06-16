<?php
/**
 *教学质量评估
 * User: cwebs
 * Date: 14-02-10
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    private $model;
    public function __construct(){
        parent::__construct();
        $this->model = new SqlsrvModel();
    }
    /**
     * 教学质量评估首页
     */
    public function index()
    {
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='D'"));
        //当前登录用户所在学院
        $user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO=:TEACHERNO",array(":TEACHERNO"=>$_SESSION["S_USER_INFO"]["TEACHERNO"]));
        $this->assign("userSchool",$user_school["SCHOOL"]);
        $this->display();
    }

}
