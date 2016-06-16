<?php
class UserAction extends RightAction{
    private $md;
    /*
     *   新建用户的主页
     */
    public function newuser(){
        $this->xiala('positions','position');       //职称数据
        $this->xiala('schools','schools');          //学院数据
        $methods=M('roles');
        if($this->_hasJson){                        //判断用户是否是用json的请求方式
            $arr=$methods->select();
            echo json_encode($arr);
            exit;
        }
        $this->display();
    }

    /*
     * 用户插入信息处理的方法
     */
    public function uinserted(){
        $teacher=M('teachers');          //教师表
        $users=M('users');             //用户表
        $teacherinfo=array();           //教师信息
        $userinfo=array();              //用户信息
        foreach($_POST as $key=>$val){
            if($key=='POSITION'){
                $val=rtrim($val,',');
            }
            if(count($arr=explode('_',$key))>1){
                $userinfo[$arr[1]]=$val;
            }else{
                $teacherinfo[$key]=$val;
            }
        }

        $teacherinfo['EnterYear']=date('Y',time());
        $teacher->startTrans();
        $bo=$teacher->add($teacherinfo);
        $bo2=$users->add($userinfo);

        if($bo && $bo2){
            echo 'true';
            $teacher->commit();//
        }else{
            echo 'false';

            $teacher->rollback();//不成功，回滚
        }

    }


    /*
     * 用户做ajax验证
     */
    public function useryz(){
        if(isset($_POST['NAME'])){                          //表示用户发来的是  用户名的
            if($_POST['NAME']==""){
                exit('sev');
            }
            $shuju=M('users');
            $arr['USERNAME']=$_POST['NAME'];
        }else{                                                  //用户发来的是 教师编号的
            if(strlen($_POST['TEACHERNUM'])!=6){
                exit('sev');
            }
            $shuju=M('teachers');
            $arr['TEACHERNO']=$_POST['TEACHERNUM'];
        }
        $one=$shuju->where($arr)->find();
        if($one){
            echo 'false';
        }else{
            echo 'aa';
        }
    }

    /*
     * 查找用户的方法
     */
    public function selectu(){
        if($this->_hasJson){
            $shuju=new SqlsrvModel();
            $arr=array();
            $sql=$shuju->getSqlMap('user/teacher/userselect.SQL');                //组合rows的sql语句
            $count=$shuju->getSqlMap('user/teacher/usercount.SQL');            //组合total的sql语句
            $bind=array(':USER'=>doWithBindStr($_POST['USER']),':DANWEI'=>doWithBindStr($_POST['DANWEI']),':ROLE'=>doWithBindStr($_POST['ROLE']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize);
            $bind2=array(':USER'=>doWithBindStr($_POST['USER']),':DANWEI'=>doWithBindStr($_POST['DANWEI']),':ROLE'=>doWithBindStr($_POST['ROLE']));
            $two=$shuju->sqlQuery($count,$bind2);
            if($arr['total']=$two[0]['']){
                $arr['rows']=$shuju->sqlQuery($sql,$bind);
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->xiala('positions','position');       //职称数据
        $this->xiala('schools','schools');          //学院数据

        $js=M('roles');
        $roles=$js->select();

        foreach($roles as $k=>$val){
            foreach($val as $key=>$v ){
                $roles[$k][$key]=trim($v);
            }
        }
        $this->assign('roles',$roles);
        $this->display();
    }
    /*
     * 编辑用户时候的方法
     */
    public function edituser(){
        $shuju = new SqlsrvModel();
        $sql=$shuju->getSqlMap('user/teacher/editUser.SQL');
        $bind=array(':TEACHERNO'=>$_POST['id']);
        $one=$shuju->sqlFind($sql,$bind);
        foreach($one as $key=>$val){
            $one[$key]=trim($val);
        }
        $role = str_split(trim($one["ROLES"]));
        $one['ROLES']=$role;

        echo json_encode($one);

    }


    /*
   *        修改用户信息的方法
   */
    public function updatedu(){

        $shuju=new SqlsrvModel();

        // $sql=$shuju->getSqlMap('./updateTeacher.SQL');           //teacherSQL修改
        //   $sql2=$shuju->getSqlMap('./updateUser.SQL');            //用户sql修改
        $ROLES=implode('',$_POST['ROLE']);          //对权限的拼接
        $teacherno2=trim($_POST['TEACHERNO2']);
        $bind=array(':TEACHERNO2'=>"$teacherno2",':NAME'=>"{$_POST['NAME']}",':TEACHERNO'=>"{$_POST['TEACHERNO']}",':POSITION'=>"{$_POST['POSITION']}",':SCHOOL'=>"{$_POST['SCHOOL']}",':SEX'=>"{$_POST['SEX']}");
        $bind2=array(':TEACHERNO2'=>"$teacherno2",':USERNAME'=>"{$_POST['USERNAME']}",':PASSWORD'=>"{$_POST['PASSWORD']}",':DAYSTOLIVE'=>"{$_POST['DAYSTOLIVE']}",':ROLES'=>"$ROLES",':TEACHERNO'=>"{$_POST['TEACHERNO']}");
        $sql="UPDATE TEACHERS SET NAME='{$_POST['NAME']}',TEACHERNO='{$_POST['TEACHERNO']}',POSITION='{$_POST['POSITION']}',SCHOOL='{$_POST['SCHOOL']}',SEX='{$_POST['SEX']}' WHERE TEACHERNO='$teacherno2'";
        $sql2="update USERS set USERNAME='{$_POST['USERNAME']}',PASSWORD='{$_POST['PASSWORD']}',DAYSTOLIVE='{$_POST['DAYSTOLIVE']}',ROLES='$ROLES',TEACHERNO='{$_POST['TEACHERNO']}' WHERE TEACHERNO='$teacherno2'";
        $shuju->startTrans();
        $bo=$shuju->sqlExecute($sql);
        // $bo=$shuju->sqlQuery($sql,$bind);
        // $bo2=$shuju->sqlQuery($sql2,$bind2);
        $bo2=$shuju->sqlExecute($sql2);


        if($bo!==false && $bo2!==false){
            echo 'true';
            $shuju->commit();//
        }else{
            echo 'false';
            echo $sql;
            echo $sql2;
            $shuju->rollback();//不成功，回滚
        }

    }
    /*
     *      删除用户的方法
     */
    public function deletedu(){
        $shuju=M('teachers');
        $shuju2=M('users');

        $where['TEACHERNO']=array('in',rtrim($_POST['teacherno'],','));
        if($_POST['teacherno']=="")
            exit('<font color="red">非法操作，必须选择要删除的选项</font>');

        //:todo:开启事物处理
        $shuju->startTrans();

        $bool=$shuju->where($where)->delete();
        $bool2=$shuju2->where($where)->delete();
        if($bool&&$bool2){
            $shuju->commit();
            echo '删除成功';
        }else{
            var_dump($bool);
            var_dump($bool2);
            $shuju->rollback();
            echo '非法操作，必须选择要删除的选项';
        }
    }


    /*
     * 冻结用户的主页
     */
    public function frozenu(){
        if($this->_hasJson){
            $shuju=new SqlsrvModel();
            $bind=array(':zz'=>trim($_POST['STUDENTNO']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize);
            if($_POST['STUDENTNO']){
                $sql=$shuju->getSqlMap('user/teacher/frozenU.SQL');            //有where的
            }else{
                $sql=$shuju->getSqlMap('user/teacher/frozenU2.SQL');         //没where的
                unset($bind[':zz']);
            }
            $count=$shuju->getSqlMap('user/teacher/frozenUcount.SQL');
            $one=$shuju->sqlQuery($count);
            if($arr['total']=$one[0]['']){
                $sarr=$shuju->sqlQuery($sql,$bind);
                $arr['rows']=$this->rows($sarr);
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,"JSON");

            exit;
        }
        $this->display();

    }

        //对1和0进行中文化
        private function rows($rows){
            $zshuzu=array();
            $shuzu=array();
            foreach($rows as $v){
                foreach($v as $key=>$val){
                    if($key[0]=="_"){
                        if($val){
                            $shuzu[$key]='已交';
                        }else{
                            $shuzu[$key]='未交';
                        }
                        continue;
                    }
                    $shuzu[$key]=$val;
                }
                $zshuzu[]=$shuzu;
            }
            return $zshuzu;
        }

    /*
     * 修改学生缴费信息的方法
     */
    public function frozenup(){
        $this->md= M("SqlsrvModel:");
        $shuju=M('fee');
        if(is_numeric($_POST['_s']))
            $arr['Study']=$_POST['_s'];
        if(is_numeric($_POST['_l']))
            $arr['Live']=$_POST['_l'];
        if(is_numeric($_POST['_b']))
            $arr['Book']=$_POST['_b'];

        $arr1['StudentNo']=$_POST['STUDENTNO'];
        $bo=$shuju->where($arr1)->save($arr);

        $a=$this->md->sqlFind("select Study,Live,Book from Fee where StudentNo=:studentno",
            array(':studentno'=>$_POST['STUDENTNO']));

        if($a['Study']&&$a['Live']&&$a['Book']){
            $demo=$this->md->sqlExecute("update students set free=0 where studentno=:studentno",
                array(':studentno'=>$_POST['STUDENTNO']));
        }else{
            $demo=$this->md->sqlExecute("update students set free=1 where studentno=:studentno",
                array(':studentno'=>$_POST['STUDENTNO']));
        }
/*        var_dump($bo);
        var_dump($a);
        var_dump($demo);*/
        if($bo&&$demo)
            echo 'true';
        else
            echo 'false';
    }


}

?>