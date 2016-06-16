<?php
/**
 * 检测权限是否符合
 * @param string $actionPath 模块Action地址，格式为 GROUP_NAME/MODEL_NAME/ACTION_NAME
 * @param string $ownerRoles 用户所拥有的权限
 * @param int $userType 用户类型 1=教师, 2=学生
 * @return bool 没找到action返回false，没有权限返回MID值，成功返回true
 */
function checkRight($actionPath, $ownerRoles, $userType){
    //if(stripos($ownerRoles,'A') !== false){
        //管理员直接跳过权限检验
        //return true;
    //}
    $model = new MenuActionsModel();// M("MenuActionsModel:");
    $data = $model->sqlQuery("SELECT ID,ROLES FROM MENU_ACTIONS where ACTION+',' LIKE :ACTION",array(":ACTION"=>"%".$actionPath.",%"));
    if(count($data)!=1) return false;
    if(checkRoles($data[0], $ownerRoles, $userType)) return true;
    else return $data[0]["ID"];
}

/**
 * 检测Action需要的权限与用户所拥有的权限是否有交集
 * @param string $needRoles Action所需要的权限
 * @param string $ownerRoles 用户所拥有的权限
 * @param int $userType 用户类型 1=教师, 2=学生
 * @return boolean
 */
function checkRoles($needRoles, $ownerRoles, $userType=1){
    //如果是学生用户，没有找到MID则通过
    //if($userType==2 && !$needRoles) return true;
    //elseif(!$needRoles["ROLES"] || !$ownerRoles) return false;
    if(!$needRoles["ROLES"] || !$ownerRoles) return false;
    /**
     如果下面一段效率有问题就请使用这段程序
    $count = strlen($needRoles["ROLES"]);
    for($i=0; $i<$count; $i++){
        if(false !== strpos($ownerRoles, substr($needRoles["ROLES"],$i,1))){
            return true;
        }
    }
    return false;
     * */
    return count(array_intersect(str_split($needRoles["ROLES"]),str_split($ownerRoles))) > 0 ? true : false;
}

/**
 * 获得Action访问路径
 */
function getActionPath(){
    return (GROUP_NAME ? GROUP_NAME."/" : "").MODULE_NAME."/".ACTION_NAME;
}

/**
 * 得到唯一的GUID
 * @param $sessionId
 * @return string
 */
function getGUID($sessionId){
    $guid = md5(time().$sessionId);
    $guid = "{".substr($guid,0,4).trim(chunk_split(substr($guid,4,-4),4, "-"),"-").substr($guid,-4)."}";
    return strtoupper($guid);
}

function getGUIDStr($sessionId){
	$guid = md5(time().$sessionId);
	$guid = substr($guid,0,4).trim(chunk_split(substr($guid,4,-4),4, "-"),"-").substr($guid,-4);
	return strtoupper($guid);
}

function ctrim($str){
    return trim($str);
}

/**
 * 检测参数是否设置过,一般就于字符串检测
 * @param $str
 * @param null $form
 * @return bool
 */
function VarIsSet($str, $form=null){
    if($form==null) $form=$_REQUEST;
    $arr = is_array($str) ? $str : @explode(",",$str);
    if(count($arr)){
        foreach($arr as $v){
            if(trim($v) && !isset($form[trim($v)])) return false;
        }
        return true;
    }
    return false;
}

/**
 * 检测参数是否有值,一般就于字符串检测
 * @param $str
 * @param null $form
 * @return bool
 */
function VarIsNotEmpty($str, $form=null){
    if($form==null) $form=$_REQUEST;
    $arr = is_array($str) ? $str : @explode(",",$str);
    if(count($arr)){
        foreach($arr as $v){
            if(trim($v) && empty($form[trim($v)])) return false;
        }
        return true;
    }
    return false;
}

function file_upload($src_file,$dest_file){
	$pdir=dirname($dest_file);
	if(!is_dir($pdir)) @mkdir($pdir,0777);
	return copy($src_file,$dest_file);
}

/**
 * 检测参数是否为整形
 * @param $str
 * @param null $form
 * @return bool
 */
function VarIsIntval($str, $form=null){
    if($form==null) $form=$_REQUEST;
    $arr = is_array($str) ? $str : @explode(",",$str);
    if(count($arr)){
        foreach($arr as $v){
            if(trim($v) && $form[trim($v)]!=intval($form[trim($v)])) return false;
        }
        return true;
    }
    return false;
}

/**
 * 处理精确查找的问题
 * @param $str
 * @return array|string
 */
function doWithBindStr($str){
    if($str == ''){
        return '';
    }
    return array(trim($str), SQLSRV_PARAM_IN ,null ,SQLSRV_SQLTYPE_VARCHAR(strlen($str)));
}

function  varDebug($param){
	echo "<pre>";
	var_dump($param);
	echo "</pre>";
	exit();
}

/**
 * 检查教师号是否属于该学院
 * @param $teacherno $_SESSION['S_USER_INFO']['TEACHERNO'] 教师号
 * @param $schoolno $_POST['SCHOOL'] 学院号
 * @return int  0 非本学院
 *              -1 A1部门
 *              1 本学院
 * @throws Exception
 */
function checkSelfSchool($teacherno = null,$schoolno){
    $model = new SqlsrvModel();
    $sqlone="select SCHOOL from TEACHERS where TEACHERNO=:TEACHERNO";
    $school=$model->sqlFind($sqlone,array(':TEACHERNO'=>isset($teacherno)?trim($teacherno):$_SESSION['S_USER_INFO']['TEACHERNO']));
    if($school){
        if(($school['SCHOOL']!='A1') && ($school['SCHOOL']!=$schoolno)) {
            return 0;
        }else if($school['SCHOOL']=='A1'){
            return -1;
        }else{
            return 1;
        }
    }
    throw new Exception('查询教师所在学院失败了！');
}
//别名
function checkModifyAuth($teacherno=null,$schoolno){
    return checkSelfSchool($teacherno,$schoolno);
}

function getStudentSchool($studentno){
    $model = new CommonModel();
    $sqlone="SELECT SCHOOL from STUDENTS WHERE STUDENTNO = :studentno";
    $school=$model->sqlFind($sqlone,array(':studentno'=>trim($studentno)));
    return $school?$school['SCHOOL']:'';
}


function varsPrint(){
    $params = func_get_args();
    echo '<pre>';
    foreach ($params as $key=>$val){
        echo '<hr /><b>Param '.$key.' is:</b><br />';
        var_dump($val);
    }
    echo '<hr /></pre>';
}




/**
 * 查询用户是否具有管理员权限
 * @param string $username 用户名
 * @return mixed < integer>
 * 				integer(0) 查询出错、查无此人、非教务处人员
 */
function isDeanByUsername($username){
    $model = new CommonModel();// M('SqlsrvModel:');
    $sql = 'SELECT USERS.isdean from USERS where USERNAME = :username;';
    $bind = array(':username'=>$username);
    $res = $model->sqlFind($sql,$bind);
    return ($res===false||count($res)!==1)?0:$res['isdean'];
}

/**
 * 从session中获取登录用户名
 * @return mixed
 */
function getUsername(){
    $val = session('S_USER_NAME');
    return is_string($val)?$val:'';
}

/**
 * 获取学部列表
 * @param bool $tag
 * @return array
 */
function getSchoolList($tag = true){
    $model = new CommonModel();// M('SqlsrvModel:');
    $schools = array();

// 	$data = $model->sqlQuery('SELECT * from SCHOOLS WHERE  (PARENT is NULL or ltrim(rtrim(PARENT))='') order by ORDERBY ;');
// 	//得到根
// 	foreach ($data as $key=>$val){
// 		$schools[$val['SCHOOL']] = $val;
// 		$bind = array(':parent'=>'%'.trim($val['SCHOOL']).'%');
// 		$data2 = $model->sqlQuery('SELECT * from SCHOOLS WHERE PARENT like :parent order by  ORDERBY ;',$bind);
// 		foreach ($data2 as $key2=>$val2){
// 			$val2['NAME']  = '&nbsp;&nbsp;'.$val2['NAME'];
// 			$schools[$val2['SCHOOL']] = $val2;
// 		}
// 	}
    $data = $model->sqlQuery('SELECT * from SCHOOLS order by ORDERBY ;');
    foreach($data as $key=>$val){
        if(!$val['PARENT']){
            //是根节点下的
            $val['NAME']  = trim($val['NAME']);
            $schools[$val['SCHOOL']] = $val;
            $children = array();
            foreach($data as $val2){
                //遍历顺序也按照orderby进行
                if(trim($val2['PARENT']) == trim($val['SCHOOL'])){
                    //是该节点的子节点
                    if($tag){
                        $val2['NAME']  = '&nbsp;'.trim($val2['NAME']);
                    }else{
                        $val2['NAME']  = trim($val2['NAME']);
                    }
                    $children[$val2['SCHOOL']] = $val2;
                }
            }
            $schools = array_merge($schools,$children);
        }
    }

    return $schools;
}



function getUserRoles(){
    $role = session("S_ROLES");
    return $role?$role:null;
}

function create_tree($arr,$parentId=0) {
    $ret = array();
    foreach($arr as $k => $v) {
        if($v['parentId'] === $parentId) {
            $tmp = $arr[$k];unset($arr[$k]);
            $tmp['children'] = create_tree($arr,$v['id']);
            $ret[] = $tmp;
        }
    }
    return $ret;
}


function create_schooltree($arr,$parentRowId,$parentId=0) {
    $ret = array();
    foreach($arr as $k => $v) {
        if ($parentId === "0")
        {
            $PARENT = trim($v['PARENT']);
            if(empty($PARENT)) {

                $tmp = $arr[$k];unset($arr[$k]);
                $tmp['parentRowId'] = $parentRowId;
                $tmp['children'] = create_schooltree($arr,$v['rowId'],$v['SCHOOL']);
                $ret[] = $tmp;
            }
        }
        else
        {
            if(trim($v['PARENT']) === $parentId) {

                $tmp = $arr[$k];unset($arr[$k]);
                $tmp['parentRowId'] = $parentRowId;
                $tmp['children'] = create_schooltree($arr,$v['rowId'],$v['SCHOOL']);
                $ret[] = $tmp;
            }
        }

    }
    return $ret;
}
