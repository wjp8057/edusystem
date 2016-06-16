<?php
/**
 * 对sql server的扩展库，提高原生sql查询速度
 * User: educk
 * Date: 13-11-21
 * Time: 上午10:18
 */
class SqlsrvModel extends Model{
    // gourp对象
    protected $cwebsGroup               =   null;
    //查找栏目
    protected $cwebsFields              =   null;

    public function __construct($name='',$tablePrefix='',$connection=''){
        parent::__construct($name, $tablePrefix, $connection);
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName(){
        return $this->name = "";
    }

    /**
     * 执行原生sql并返回一条对象数据
     * @param $sql
     * @param array $bind
     * @return mixed
     */
    public function sqlFind($sql, $bind=array()){
        $data = $this->sqlQuery($sql, $bind);
        if($data === false || count($data)==0) return false;
        return $data[0];
    }

    /**
     * 查询原生sql
     * @param $sql
     * @param array $bind
     * @return mixed
     */
    public function sqlQuery($sql, $bind=array()){
        $sql  =   $this->parseSql($sql,false);
        //$this->db->setModel("");
        return $this->db->query($sql,$bind);
    }

    /**
     * 统计条数
     * @param $sql
     * @param array $bind
     * @param bool $parse 是否把sql解析成count(*)语法，复杂sql语法请慎用
     * @return int|string
     */
    public function  sqlCount($sql, $bind=array(), $parse=false){
        if($parse){
            /**
            $fromIndex = stripos($sql,"from");
            $selectIndex = strripos($sql,"select",-(strlen($sql)-$fromIndex));
            if($fromIndex==-1 || $selectIndex==-1) return 0;
            $sql = substr($sql,0,$selectIndex+6)." count(*) AS [ROWS] ".substr($sql,$fromIndex);
             **/
            $orderIndex = strripos($sql,"order ");
            if($orderIndex===false) $orderIndex = strlen($sql);
            $sql = "select count(*) as ROWS from (".substr($sql, 0, $orderIndex).") AS _CTB";
        }
        $data = $this->sqlFind($sql, $bind);
        if($data===false) return false;
        foreach($data as $val){
            return $val;
        }
    }
    /**
     * 获得分页sql
     * @param $sql sql语句
     * @param $file 从sqlMap中获得sql，此项必须建立在$sql项为空时才生效
     * @param $start 开始记录
     * @param $pageSize 偏移量(每页大小)
     * @return null|string
     */
    public function getPageSql($sql, $file,  $start, $pageSize){
        if(empty($sql) && !empty($file)) $sql = $this->getSqlMap($file);

        //处理order
        $order = "ORDER BY rand()";
        $index = strripos($sql,"order");
        if($index!==false){
            $order = substr($sql, $index);
            $list = @explode(".",$order);
            if(count($list)>1){
                foreach($list as $key=>$f){
                    if($key%2==0) $list[$key] = substr($f,0,strripos($f," ")+1);
                }
                $order = implode("",$list);
            }
            $sql = substr($sql, 0, $index);
        }

        //处理griuo
        if($this->cwebsGroup !== null){
            $sql = "SELECT ".($this->cwebsFields===null?$this->cwebsGroup:$this->cwebsFields)." FROM (".$sql.") AS G1 GROUP BY ".$this->cwebsGroup;
        }

        $sql  = "SELECT T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER (".$order.") AS ROW_NUMBER FROM (".$sql.") AS thinkphp)";
        $sql .= " AS T1 WHERE (T1.ROW_NUMBER BETWEEN ".$start." + 1 AND ".$start." + ".$pageSize.")";

        return $sql;
    }

    /**
     * 执行原生sql
     * @param $sql
     * @param array $bind
     * @return mixed
     */
    public function sqlExecute($sql, $bind=array()){
        return $this->db->execute($sql,$bind);
    }

    /**
     * 从sqlMap中获得sql
     * @param $file
     * @return null|string
     */
    public function getSqlMap($file){
        //如果sql和file都为空，则返回空
        if(empty($file)) return null;

        $file = str_replace("..",".",$file);
        return file_get_contents(SQL_MAP_PATH.$file);
    }

    /**
     * 获得bind
     * @param $options 需要绑定的变量
     * @param string $form 从哪里获得这些变量默认为_REQUEST，也可以设定与options对应的值
     * @param string $default 如果为空时的默认值
     * @return bind|array
     */
    public function getBind($options, $form="REQUEST", $default=""){
        $bind = array();
        $indexModel = false;
        if(empty($options)) return $bind;
        elseif(is_array($options)) $need = $options;
        else {
            $need = @explode(",",$options);
            $indexModel = true;
        }

        if(empty($form)) $value = null;
        elseif(is_array($form)) {
            $value = $form;
            $indexModel = false;
        }
        elseif(in_array(strtoupper($form),array("_REQUEST","_COOKIE", "_ENV", "_FILES", "_GET", "_POST", "_SERVER", "_SESSION"))){
            $value = $$form;
            $indexModel = false;
        }else{
            $value = @explode(",",$form);
            $indexModel = true;
        }

        if(count($need)>0){
            foreach($need as $key => $item) {
                if(is_int($key) && array_key_exists(trim($key), $value)){
                    $bind[":".trim($item)] = isset($value[trim($key)])?$value[trim($key)]:$default;
                }else{
                    $bind[":".trim($item)] = isset($value[trim($item)])?$value[trim($item)]:$default;
                }
                //$bind[":".trim($item)] = $indexModel ? (array_key_exists(trim($key), $value)?$value[trim($key)]:$default) : (array_key_exists(trim($item), $value)?$value[trim($item)]:$default);
            }
        }
        return $bind;
    }

    /**
     * 给sql加上gourp选项
     * @param $group
     * @return $this
     */
    public function sqlGroup($group){
        if(empty($group) || count($group)==0) $this->cwebsGroup = null;

        if(is_array($group)) $this->cwebsGroup = @implode(",", $group);
        else $this->cwebsGroup = $group;
        return $this;
    }

    /**
     * 设置sql取得列名，主要是配合group用的
     * @param $fields
     * @return $this
     */
    public function  sqlFields($fields){
        if(empty($fields) || count($fields)==0) $this->cwebsGroup = null;

        if(is_array($fields)) $this->cwebsFields = @implode(",", $fields);
        else $this->cwebsFields = $fields;
        return $this;
    }
}
