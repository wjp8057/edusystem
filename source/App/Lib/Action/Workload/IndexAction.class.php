<?php
/**
 * 系统首页
 * User: cwebs
 * Date: 13-11-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    /**
     * 班级管理首页
     */
    private $md;        //存放模型对象
    private $base;      //路径
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");
    /**
     *  班级管理
     *
     **/
    public function __construct(){
        parent::__construct();
    }

    public function index()
    {
        $userObj = D('year_term');
        $condition['type'] = 'G';
        $data = $userObj->field('year,term')->where($condition)->find();
        $this->assign('yearterm',$data);
        $Obj=D('teachers');
        $condition=null;
        $condition['users.username'] =  session("S_USER_NAME");
        $data=$Obj->join('users on users.teacherno=teachers.teacherno')->field('teachers.school')->where($condition)->find();
        $this->assign('school',$data);
        $this->display();
    }

}