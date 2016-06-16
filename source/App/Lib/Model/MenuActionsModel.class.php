<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 2015/7/8
 * Time: 11:09
 */
class MenuActionsModel extends SqlsrvModel {
    public $STR_CHILDREN = "__children__";
    public $STR_ROW = "__row__";
    public function __construct($name='',$tablePrefix='',$connection=''){
        parent::__construct($name, $tablePrefix, $connection);
    }

    /**
     * 找到一条记录
     * @param $id
     * @return mixed
     */
    public function findByid($id){
        return $this->sqlFind("select * from MENU_ACTIONS WHERE ID=:ID", array(":ID"=>$id));
    }

    /**
     * 删除一条记录
     * @param $id
     * @return mixed
     */
    public function deleteById($id){
        return $this->sqlExecute("delete from MENU_ACTIONS WHERE ID=:ID", array(":ID"=>$id));
    }

    /**
     * 找到所有角色
     * @return mixed
     */
    public function getRolesComboBox(){
        return $this->sqlQuery("select ROLE as id,DESCRIPTION as text from ROLES");
    }

    /**
     * 获得所有指定角色的授权列表
     * @param $roleId
     * @return mixed
     */
    public function getRolesById($roleId){
        return $this->sqlQuery("select * from MENU_ACTIONS where ROLES LIKE :ROLES", array(":ROLES"=>"%".$roleId."%"));
    }

    /**
     * 保存授权
     * @param $roleId
     * @param $grantIds
     * @return bool|mixed
     */
    public function saveGrantByRoles($roleId, $grantIds){
        $this->startTrans();
        //todo 1、首先撤除此角色原些所有的授权
        $data = $this->sqlExecute("update MENU_ACTIONS set ROLES=replace(ROLES,'".$roleId."','')");
        if($data===false){
            $this->rollback();
            return $data;
        }

        //todo 2、重新授予指定的权限
        if(is_array($grantIds)){
            $data = $this->sqlQuery("select INNERID from MENU_ACTIONS where INNERID IS NOT NULL AND INNERID<>'' AND ID IN (".@implode(",",$grantIds).")");
            if($data===false){
                $this->rollback();
                return $data;
            }
            if(count($data)>0){
                foreach($data as $row) $grantIds[] = intval($row["INNERID"]);
            }

            $data = $this->sqlExecute("update MENU_ACTIONS set ROLES=(CASE WHEN ROLES IS NULL THEN '' ELSE ROLES END)+'".substr($roleId,0,1)."' where ID IN (".@implode(",",$grantIds).")");
            if($data===false){
                $this->rollback();
                return $data;
            }
        }

        $this->sqlExecute("update MENU_ACTIONS set ROLES=NULL WHERE ROLES=''");
        $this->commit();
        return true;
    }

    /**
     * 获得数据
     * @param null $isMenu  是否菜单
     * @param null $root   上级ID
     * @param null $name   模块名称
     * @param null $id     模块ID
     * @param null $act    ACTION
     * @return mixed
     */
    public function findData($isMenu=null, $root=null, $name=null, $id=null, $act=null){
        $sql = "select * from MENU_ACTIONS WHERE 1=1";

        $bind = Array();

        if($isMenu!==null){
            $sql .= " AND ISMENU=:ISMENU";
            $bind[":ISMENU"] = $isMenu ? 1 : 0;
        }
        if(!empty($root)){
            $sql .= " AND PID=:PID";
            $bind[":PID"] = $root;
        }
        if(!empty($name)){
            $sql .= " AND NAME LIKE :NAME";
            $bind[":NAME"] = '%'.$name.'%';
        }
        if(!empty($id)){
            $sql .= " AND ID=:ID";
            $bind[":ID"] = $id;
        }
        if(!empty($act) && $act!="%"){
            $sql .= " AND ACTION LIKE :ACTION";
            $bind[":ACTION"] = $act;
        }

        return $this->sqlQuery($sql, $bind);
    }

    /**
     * 获得菜单
     * @param null $root   上级ID
     * @param null $name   模块名称
     * @param null $id     模块ID
     * @param null $act    ACTION
     * @return mixed
     */
    public function findMenu($root=null,$name=null, $id=null, $act=null){
        return $this->findData(1, $root, $name, $id, $act);
    }

    /**
     * 获得所有
     * @param null $root   上级ID
     * @param null $name   模块名称
     * @param null $id     模块ID
     * @param null $act    ACTION
     * @return mixed
     */
    public function findAll($root=null,$name=null, $id=null, $act=null){
        return $this->findData(null, $root, $name, $id, $act);
    }

    /**
     * 检测ID是重复
     * @param $id
     */
    public function isRepeat($id){
        $data = $this->sqlCount("select count(*) from MENU_ACTIONS where ID=:ID",array(":ID"=>$id));
        if($data===false) return true;
        elseif($data>0) return true;
        else return false;
    }

    /**
     * 是否有子模块
     * @return bool
     */
    public function isChildren($id){
        $data = $this->findByid($id);
        if($data && $data["PATH"]){
            $data = $this->sqlCount("select count(*) from MENU_ACTIONS where ID<>:ID AND PATH like :PATH",array(":ID"=>$id, ":PATH"=>$data["PATH"]."%"));
            if($data!== false && $data==0) return false;
        }
        return true;
    }

    /**
     * 根据一条数据所取上下文数组
     * @param $menu
     * @param $hashSub 是否取子集
     */
    public function findByMenu($menu, $hashSub=true){
        $sql = "select * from MENU_ACTIONS WHERE id IN (".str_replace('|',',',trim($menu["PATH"],'|')).")";
        if($hashSub) $sql .= " OR PATH LIKE '".$menu["PATH"]."%'";
        $sql .= " ORDER BY ID";
        return $this->sqlQuery($sql);
    }

    /**
     * 把数组变为树型数组
     * @param $arr
     * @return array
     */
    public function makeDataToTree($arr, $fieldArr=null){
        $data = array();

        //设置是否需要映射字段
        $mapField = false;
        if(is_array($fieldArr)) $mapField = true;

        foreach($arr as $row){
            $path = $this->getArrayString($row["PATH"]);
            if($mapField) $row = $this->makeMapField($row, $fieldArr);
            eval("\$data$path = \$row;");
        }
        return $data;
    }

    /**
     * 获得树型dataGrid
     * @param $arr
     * @return array
     */
    public function getTreeGrid($arr){
        $arr = $this->makeDataToTree($arr);
        return $this->makeArray($arr, "children");
    }

    /**
     * 获得树型ComboTree
     * @param $arr
     */
    public function getComboTree($arr){
        $arr = $this->makeDataToTree($arr, Array("ID"=>"id","NAME"=>"text"));
        return $this->makeArray($arr, "children");
    }

    private function getArrayString($path, $prefix="|"){
        $paths = @explode($prefix, trim($path, $prefix));
        $len = count($paths);
        if($len==1) return '['.$paths[0].']['.$this->STR_ROW.']';
        else{
            return '['.str_replace($prefix, ']["'.$this->STR_CHILDREN.'"][', trim($path, $prefix)).']['.$this->STR_ROW.']';
        }
        //return '["'.str_replace($prefix, '"]["', trim($path, $prefix)).'"][row]';
    }

    private function makeArray($arr, $childrenKey, $returnArr=Array()){
        $_tmpArr = Array();
        foreach($arr as $key=>$val){
            $_tmpArr = isset($val[$this->STR_ROW]) ? $val[$this->STR_ROW] : $this->findByid($key);
            if($val[$this->STR_CHILDREN]) $_tmpArr[$childrenKey] = $this->makeArray($val[$this->STR_CHILDREN], $childrenKey);
            $returnArr[] = $_tmpArr;
        }
        return $returnArr;
    }

    private function makeMapField($row, $fieldArr){
        $data = Array();
        foreach($fieldArr as $key=>$val){
            $data[$val] = $row[$key];
        }
        return $data;
    }
}