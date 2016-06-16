<?php
/**
 * 新的授权方法，老系统使用老方法，新系统使用新方法
 *
 * User: educk
 * Date: 2015/7/8
 * Time: 11:00
 */
class GrantAction extends RightAction{
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"","data"=>"");

    public function __construct(){
        parent::__construct();
        $this->model=new MenuActionsModel();
    }

    /**
     * 授权列表
     */
    public function qlist(){
        if($this->_hasJson) {
            $data = $this->model->findAll(null,$_REQUEST["NAME"], $_REQUEST["ID"], $_REQUEST["URL"]);
            if($data==null) $data = Array();
            elseif(count($data)==1){
                $data = $this->model->findByMenu($data[0]);
            }

            if($_REQUEST["TREE_TYPE"]=="ComboTree") $this->ajaxReturn($this->model->getComboTree($data));
            else $this->ajaxReturn($this->model->getTreeGrid($data));

        }
        $this->assign("rolesList", $this->model->getRolesComboBox());
        $this->display("qlist");
    }

    /**
     * 新增或修改权限
     */
    public function save(){
        if(!$this->_hasJson) return;

        if($_REQUEST["actionType"]==1){ //添加
            //todo 参数检测
            if(VarIsIntval("ID,ISMENU")==false || VarIsNotEmpty("NAME,ACTION")==false){
                $this->message["type"] = "error";
                $this->message["message"] = "输入的参数有错误，非法提交数据！";
                $this->__done();
            }
            //todo 检测ID是否重复
            if($this->model->isRepeat($_REQUEST["ID"])){
                $this->message["type"] = "error";
                $this->message["message"] = "模块号".$_REQUEST["ID"]."已被使用！";
                $this->model->rollback();
                $this->__done();
            }

            //todo 处理PATH
            if($_REQUEST["PID"]) {
                $pData = $this->model->findByid($_REQUEST["PID"]);
                $PATH = $pData["PATH"].$_REQUEST["ID"]."|";
            }else $PATH = "|".$_REQUEST["ID"]."|";

            $bind = $this->model->getBind("ID,PID,NAME,ACTION,INNERID,ROLES,REM,PATH,ISMENU",$_REQUEST);
            $bind[":PATH"] = $PATH;
            if(intval($bind[":PID"])==0) $bind[":PID"]=null;
            $data = $this->model->sqlExecute("insert into MENU_ACTIONS (ID,PID,[NAME],ACTION,INNERID,ROLES,REM,PATH,ISMENU) values (:ID,:PID,:NAME,:ACTION,:INNERID,:ROLES,:REM,:PATH,:ISMENU)", $bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "插入数据时发生错误！";
                $this->__done();
            }

            $this->message["data"] = $this->model->findByid($_REQUEST["ID"]);
            $this->message["message"] = "模块信息已成功添加！";
            $this->__done();
            exit;

        }elseif($_REQUEST["actionType"]==2){ //修改
            if(VarIsIntval("ID,OLD_ID,ISMENU")==false || VarIsNotEmpty("NAME,ACTION")==false){
                $this->message["type"] = "error";
                $this->message["message"] = "输入的参数有错误，非法提交数据！";
                $this->__done();
            }

            //todo 开启事务模式
            $this->model->startTrans();
            //todo 取得老数据
            $oData = $this->model->findByid($_REQUEST["OLD_ID"]);
            //todo 检测ID是否重复
            if($_REQUEST["OLD_ID"]!=$_REQUEST["ID"]){
                if($this->model->isRepeat($_REQUEST["ID"])){
                    $this->message["type"] = "error";
                    $this->message["message"] = "模块号".$_REQUEST["ID"]."已被使用！";
                    $this->model->rollback();
                    $this->__done();
                }
            }

            //处理PATH
            if($oData["PID"]==$_REQUEST["PID"]) $PATH = $oData["PATH"];
            else{
                if($_REQUEST["PID"]) {
                    $pData = $this->model->findByid($_REQUEST["PID"]);
                    $PATH = $pData["PATH"].$_REQUEST["ID"]."|";
                }else $PATH = "|".$_REQUEST["ID"]."|";
            }

            //todo 修改权限表
            $bind = $this->model->getBind("ID,PID,NAME,ACTION,INNERID,ROLES,REM,PATH,ISMENU,OLD_ID",$_REQUEST);
            $bind[":PATH"] = $PATH;
            if(intval($bind[":PID"])==0) $bind[":PID"]=null;
            $this->model->sqlExecute("update MENU_ACTIONS set ID=:ID, PID=:PID,[NAME]=:NAME,ACTION=:ACTION,INNERID=:INNERID,ROLES=:ROLES,REM=:REM,PATH=:PATH,ISMENU=:ISMENU where ID=:OLD_ID", $bind);

            $this->model->sqlExecute("update MENU_ACTIONS set PATH=replace(PATH,'".$oData["PATH"]."','".$PATH."') WHERE PATH LIKE :LPATH", array(":LPATH"=>$oData["PATH"]."%"));
            $this->model->commit();

            $this->message["data"] = $this->model->findByid($_REQUEST["ID"]);
            $this->message["message"] = "模块信息已成功修改！";
            $this->__done();
            exit;
        }
    }

    /**
     * 删除权限
     */
    public function delete(){
        if(!$this->_hasJson) return;

        //todo 检测参数
        if(VarIsIntval("ID")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        //todo 检测是否有子模块
        if($this->model->isChildren($_REQUEST["ID"])){
            $this->message["type"] = "error";
            $this->message["message"] = "指定模块号[".$_REQUEST["ID"]."]，还有子模块不能删除！";
            $this->__done();
        }

        $data = $this->model->deleteById($_REQUEST["ID"]);
        if($data===false || $data==0){
            $this->message["type"] = "error";
            $this->message["message"] = "删除模块时发生错误！";
        }else $this->message["message"] = "模块已成功删除！";

        $this->__done();
    }

    /**
     * 获得指定角色的授权ID
     * @param $roleId
     */
    public function getRolesById(){
        if(!$this->_hasJson) return;

        $data = $this->model->getRolesById($_REQUEST["ROLESID"]);
        $ids = array();
        if($data){
            foreach($data as $val) $ids[] = $val["ID"];
        }
        $this->ajaxReturn($ids);
    }

    /**
     * 保存授权修改
     */
    public function saveGrantByRoles(){
        if(!$this->_hasJson) return;

        if(VarIsNotEmpty("ROLESID")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $data = $this->model->saveGrantByRoles($_REQUEST["ROLESID"], $_REQUEST["GRANTIDS"]);

        if($data==false){
            $this->message["type"] = "error";
            $this->message["message"] = $_REQUEST["ROLESID"]."角色授权更新时发生错误！";
        }else $this->message["message"] = $_REQUEST["ROLESID"]."角色授权更新成功！";
        $this->__done();
    }
}