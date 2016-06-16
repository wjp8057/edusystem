<?php
/**
 * Email:linzhv@foxmail.com
 * User: Lin
 * Date: 2015/7/23
 * Time: 13:52
 */

/**
 * 15年07月23日重写时的通用类
 * Class CommonModel
 */
class CommonModel extends SqlsrvModel{


    /**
     * 创建数据时的表达域需要的前缀
     * @var string
     */
    const CREATE_FEILD_PREFIX = 'CTD_';
    const SEARCH_FEILD_PREFIX = 'SFP_';


    /**
     * 开关
     * @var array
     */
    protected static $_switcher = array(
        'SHOW_ERROR' => true,//决定是否向用户显示错误信息
    );

    protected $_php_excel = null;


    protected $_model = null;

//    protected function getModel(){
//        if(!isset($this->_model)){
//            $classnm = get_called_class();
//            $this->_model = $classnm();
//        }
//        return $this->_model;
//    }

    public function isShowErrorSwitchOn(){
        return static::$_switcher['SHOW_ERROR'];
    }

    /**
     * 创建记录并插入到目标表
     * @param string $tablename 在该表中插入前台传递的数据
     * @param mixed $datafields 要插入的数据
     * @return string|boolean 如果发生了错误，将返回字符串形式的错误信息
     */
    protected function insertData($tablename,$datafields=null){
        $columns    = '';
        $values     = '';
        $bind  = array();

        if(NULL === $datafields){
            foreach($_REQUEST as $key=>$val){
                $key = strtolower(trim($key));
                if(is_string($val)){
                    $val = trim($val);
                }
                $pos = stripos($key,self::CREATE_FEILD_PREFIX);
                if($pos !== false){
                    $pos += strlen(self::CREATE_FEILD_PREFIX);//获取截取的位置

                    $colnm = substr($key,$pos);//插入列的名称
                    $bindname = ":{$key}";//绑定列的名称

                    //拼接插入SQL字符串
                    $columns .= $colnm.',';
                    $values  .= "{$bindname},";

                    $bind[$bindname] = $val;
                }
            }
        }else{
            $columns    = $datafields['columns'];
            $values     = $datafields['values'];
            $bind  = $datafields['bind'];
        }

        $columns = rtrim($columns,', ');
        $values  = rtrim($values,', ');
        $sql = "insert into {$tablename} ( {$columns} ) VALUES ( {$values} );";
        $rst = $this->sqlExecute($sql,$bind);
//        var_dump($rst,$sql,$bind,$this->getErrorMessage());

        return $this->doneExecute($rst);
    }

    /**
     * 更新记录信息
     * @param string $tblName 更新的数据表的名称
     * @param array $updFields 更新的字段数组，键为string时代表字段名称，值为字段的新值,使用的字段名称不带':'
     *                                       键为array时第一个元素代表"XXX=:YYY"的字符串形式，参数二表示要设置的值
     * @param array $whrMap  更新的字段的筛选条件，键为键为字段名称，值为string(等于该字符串)，数组(首元素是值，次元素是操作符号)
     * @return string|integer 返回受影响行数或者错误信息
     */
    protected function updateData($tblName,$updFields,$whrMap){
        if(!is_array($updFields) or !count($updFields)){
            return 'No Update Fields';
        }
        $sql = "update {$tblName} set ";
        $bind = array();

        foreach($updFields as $key=>$val){
            if(is_array($val)){
                $sql .= " {$val[0]},";
                if(!isset($val[2])){
                    //如果设置了第三个参数，则说明第二个参数是特殊的绑定变量，应对的是情况是： [group]=dbo.fn_10to36(:GP)
                    $key = trim(strstr($val[0],':',true),' ]');//包含‘:’的部分
                    $value = trim($val[1]);
                }else{
                    //否则键为参数2，值为参数3
                    $key = ':'.trim($val[1],' :[]');
                    $value = trim($val[2]);
                }
                $bind[$key] = $value;
            }elseif(is_string($key)){
                $key = trim($key);
                if($key){
                    //为非空字符串
                    $bkey = ':'.strtolower(trim($key,' []'));
                    $sql .= " {$key}={$bkey},";
                    $bind[$bkey] = $val;
                }else{
                    return "Key '{$key}' invalid !";
                }
            }
        }
        $sql = rtrim($sql,' ,');
        if(is_array($whrMap) and count($whrMap)){
            $sql .= ' where ';
            foreach($whrMap as $key=>$val){
                $key = trim($key,' :');
                if(is_string($key) and $key){
                    //为非空字符串
                    $oprater = ' = ';
                    if(is_array($val)){
                        $oprater = " {$val[1]} ";
                        $val = $val[0];
                    }
                    $bkey = strtolower(":{$key}");
                    $sql .= " [{$key}]{$oprater} {$bkey} and";
                    $bind[$bkey] = $val;
                }else{
                    return "Key '{$key}' invalid !";
                }
            }
            //去掉最后一个'and'
            $sql = substr($sql,0,strlen($sql)-3);
        }
        $rst = $this->sqlExecute($sql,$bind);
//        var_dump($rst,$sql,$bind,$this->getErrorMessage());
        return $this->doneExecute($rst);
    }









    /**
     * 从$_REQUEST中获取绑定参数
     * <code>
     *  例如前台请求的参数为
     *      SFP_PROGRAMNO , SFP_PROGNAME ， SFP_SCHOOL
     *  则绑定好哦的参数是
     *      array(
     *          ':programno' => XXX,
     *          ':progname' => XXX,
     *          ':school' => XXX,
     *      );
     *
     * </code>
     * @param string $prefix
     * @param string $src 数据来源
     * @return array|null 和绑定列的名称绑定参数数组
     */
    protected function bindParameter($prefix,$src='request'){
        $bind = array();
        $dataSource = $_REQUEST;
        switch($src){
            case 'post':$dataSource = $_POST;break;
            case 'get':$dataSource = $_GET;break;
            case 'request'://默认项
            default:;
        }
        foreach($dataSource as $key=>$val){
            $key = strtolower(trim($key));
            if(is_string($val)){
                $val = trim($val);
            }
            $pos = stripos($key,$prefix);
            if($pos !== false){
                $pos += strlen($prefix);//获取截取的位置
                $colnm = substr($key,$pos);//插入列的名称
                $bind[":{$colnm}"] = $val;
            }
        }
        return $bind;
    }

    /**
     * ****可以用makesegment替换****
     * 添加筛选过滤字段
     * @param string $fieldname 过滤字段名称,[]防止冲突设置需要用户手动设置
     * @param string|array $types 过滤字段类型
     * @param boolean $flag 表明过滤字段是圈内的还是圈外的
     * @param bool $withAnd 是否自动带上and，当为第一个时设置为false
     * @return string
     */
    protected function makeFilter($fieldname,$types,$flag=true,$withAnd=true){
        $fieldname = trim($fieldname);
        $filter = $withAnd?" AND  {$fieldname} ":" {$fieldname} ";
        $types = is_string($types)?trim($types):$types;
        if(empty($types)){
            return '';
        }else{
            if($flag){
                if(is_array($types)){
                    $filter .= " IN ('".implode("','",$types)."')";
                }else{
                    $filter .= " = '".trim($types)."'" ;
                }
            }else{
                if(is_array($types)){
                    $filter .= " NOT IN ('".implode("','",$types)."')";
                }else{
                    $filter .= " != '".trim($types)."'" ;
                }
            }
        }
        return $filter;
    }

















/***************** 信赖且通用的方法 **************************************************************/

    /**
     * 获取前端表使用的SQL
     * <code>
     * 返回的数据格式如:
     *  array(
     *      'total'=>XX,
     *      'rows'=>array(....),
     *  );
     * </code>
     * @param string $csql 查询数量的SQL
     * @param string $ssql 查询数据的SQL
     * @param array $bind 查询使用的绑定参数
     * @return string|array 发生错误时返回字符串形式的错误信息，否则返回查询记过数组
     */
    protected function getTableList($csql,$ssql,$bind){
        $json = array('total'=>0,'rows'=>array());
        $rtn = $this->sqlQuery($csql,$bind);
//        mist($rtn,$csql,$bind,$this->getErrorMessage());
        if(false ===$rtn){
//            vardump($rtn,$csql,$bind,$this->getErrorMessage());
            return __FILE__.__LINE__.'##'.$this->getErrorMessage();
        }
//        var_dump($rtn,$csql,$bind,$this->getErrorMessage());
        $json['total'] = $rtn[0]['ROWS'];
        if($json['total'] > 0){
            $rtn = $this->sqlQuery($ssql,$bind);
//                vardump($rtn,$ssql,$bind,$this->getErrorMessage());
            if(false === $rtn){//$json['total'] > 0 就一定不是空数组
                return __FILE__.__LINE__.'##'.$this->getErrorMessage();
            }
            $json['rows'] = $rtn;
        }
//        var_dump($rtn,$ssql,$bind,$this->getErrorMessage());
        return $json;
    }
    /**
     * 改造自父类方法getTableList
     * @param $csql
     * @param $ssql
     * @param $bind
     * @return array|string
     */
    protected function getTableList2($csql,$ssql,$bind){
        $json = array('total'=>0,'rows'=>array());
        $rtn = $this->sqlQuery($csql,$bind);
//        mist($rtn,$csql,$bind);
        if(false ===$rtn){
            return __FILE__.__LINE__.'##'.$this->getErrorMessage();
        }
        $json['total'] = count($rtn);// 为什么写成记录数呢？？ 参照文件QueryScheduleplanCount.SQL中第一句 SELECT count(*) FROM (        SELECT count(*) as NCOUNT
        if($json['total'] > 0){
            $rtn = $this->sqlQuery($ssql,$bind);
            if(false === $rtn){
                return __FILE__.__LINE__.'##'.$this->getErrorMessage();
            }
            $json['rows'] = $rtn;
        }
        return $json;
    }
    /**
     * 获取计算数量的SQL
     * 设计目标：可以省略计算数量的字段
     * @param $fromtable
     * @param array $compos
     * @param string $defaultName
     * @return string
     */
    protected function makeCountSql($fromtable,$compos=array(),$defaultName='ROWS'){
        $compos['fields'] = "count(*) as {$defaultName}";
        return $this->makeSql($fromtable,$compos);
    }

    /**
     * 根据条件获得查询的SQL，SQL执行的正确与否需要实际查询才能得到验证
     * @param string $fromtable 查找的表名称,不需要带上from部分
     * @param array $compos  复杂SQL的组成部分
     * @param null|integer $offset 偏移
     * @param null|integer $limit  选择的最大的数据量
     * @return string
     */
    protected function makeSql($fromtable,$compos=array(),$offset=NULL,$limit=NULL){
        $components = array(
            'distinct'=>'',
            'top' => '',
            'fields'=>' * ', //查询的表域情况
            'join'=>'',     //join部分，需要带上join关键字
            'where'=>'', //where部分
            'group'=>'', //分组 需要带上group by
            'having'=>'',//having子句，依赖$group存在，需要带上having部分
            'order'=>'',//排序，不需要带上order by
        );
        $components = array_merge($components,$compos);
        if($components['distinct']){//为true或者1时转化为distinct关键字
            $components['distinct'] = 'distinct';
        }
        $sql = " select {$components['distinct']} {$components['top']} {$components['fields']}  from  {$fromtable} ";

        //group by，having 加上关键字(对于如group by的组合关键字，只要判断第一个是否存在)如果不是以该关键字开头  则自动添加
        if($components['where'] && 0 !== stripos(trim($components['where']),'where')){
            $components['where'] = ' where '.$components['where'];
        }
        if($components['group'] && 0 !== stripos(trim($components['group']),'group')){
            $components['group'] = ' group by '.$components['group'];
        }
        if( $components['having'] && 0 !== stripos(trim($components['having']),'having')){
            $components['having'] = ' having '.$components['having'];
        }
        //去除order by
        $components['order'] = preg_replace_callback('|order\s*by|i',function(){return '';},$components['order']);

        //按照顺序连接，过滤掉一些特别的参数
        foreach($components as $key=>&$val){
            if(in_array($key,array('fields','order','top','distinct'))) continue;
            $sql .= " {$val} ";
        }

        $flag = true;//标记是否需要再次设置order by

        //是否湖区偏移
        if(NULL !== $offset && NULL !== $limit){
            $outerOrder = ' order by ';
            if(!empty($components['order'])){
                //去掉其中的order by
                $orders = @explode(',',$components['order']);//分隔多个order项目

                foreach($orders as &$val){
                    $segs = @explode('.',$val);
                    $outerOrder .= array_pop($segs).',';
                }
                $outerOrder  = rtrim($outerOrder,',');
            }else{
                $outerOrder .= ' rand() ';
            }
            $endIndex = $offset+$limit;
            $sql = "SELECT T1.* FROM (
            SELECT  ROW_NUMBER() OVER ( {$outerOrder} ) AS ROW_NUMBER,thinkphp.* FROM ( {$sql} ) AS thinkphp
            ) AS T1 WHERE (T1.ROW_NUMBER BETWEEN 1+{$offset} AND {$endIndex} )";
            $flag = false;
        }
        if($flag && !empty($components['order'])){
            $sql .= ' order by '.$components['order'];
        }
        return $sql;
    }

    /**
     * 获取下拉框的数据,针对键值对类型的表
     *  表的名称和值列、文本列的名称将由程序员手动调入，无需绑定等防止注入的操作
     * @param string $tablename 表的名称
     * @param string $valfield 值列的名称，列的名称将显示为value
     * @param string $txtfield 文本列的名称，列的名称将显示为text
     * @param string $defaultname 默认显示的文本，其键值对将作为数组的首个元素
     * @param boolean $defaultShowText 默认值是显示值，false为真实值
     * @return array|string 返回结果为NULL时表示参数不合法 返回string表示查询过程出现错误
     */
    protected function getComboData($tablename,$valfield,$txtfield,$defaultname = NULL,$defaultShowText=true){
        if(!self::checkInvalidExist(array(NULL,''),$tablename,$valfield,$txtfield)){
            //搜有的参数都是
            $arr= $this->sqlQuery("select RTRIM({$valfield}) as value,RTRIM({$txtfield}) as text from {$tablename}");
            if(false === $arr){
                return $this->getErrorMessage();
            }
            $sjson = array();
            foreach($arr as $val){
                $curVal = array();
                $curVal['text']=trim($val['text']);
                $curVal['value']=trim($val['value']);                  // 把学院数据转成json格式给前台的combobox使用
                if(NULL !== $defaultname){
                    $cpval = $defaultShowText?$curVal['text']:$curVal['value'];
                    if(trim($cpval) == trim($defaultname)){
                        //交换第一个元素和本元素的位置
                        $temp = $sjson[0];
                        $sjson[0] = $curVal;
                        $curVal = $temp;
                    }
                }
                $sjson[] = $curVal;
            }
            return $sjson;
        }else{
            return 'Invalid Parameters！';
        }
    }


    /**
     * 执行更新操作
     * @param $tblName
     * @param $updFields
     * @param $whrMap
     * @return int|string
     */
    protected function updateRecords($tblName,$updFields,$whrMap){
        $sql = "update {$tblName} set ";
        $cols = $this->makeSegments($updFields,false);
        $whr = $this->makeWhere($whrMap);
        //可能出现更新字段同筛选字段，需要在where中自己构建参数绑定
        $sql .= " {$cols[0]} {$whr[0]} ";
        $bind = array_merge($cols[1],$whr[1]);
        $rst = $this->sqlExecute($sql,$bind);
        return $this->doneExecute($rst);
    }

    /**
     * 插入记录
     * <note>
     *      'key' => $val
     *      'key' => array($val,true);//true表示对列进行转义
     * </note>
     * @param $tableName
     * @param $fieldsMap
     * @return int|string
     */
    protected function createRecord($tableName,$fieldsMap){
        $columns    = '';
        $values     = '';
        $bind  = array();
        if(is_array($fieldsMap)){
            foreach($fieldsMap as $key=>$val){
                if(!isset($val)) continue;//预备一些不允许为null，且有默认值的字段
                $key = trim($key,'[] :');
                $colnm = $key;
                if(is_array($val)){
                    $colnm = $val[1]?"[$key]":$key;
                    $val = $val[0];
                }

                $bindname = ":{$key}";//绑定列的名称

                //拼接插入SQL字符串
                $columns .= "{$colnm},";
                $values  .= "{$bindname},";

                $bind[$bindname] = $val;
            }
            $columns = rtrim($columns,', ');
            $values  = rtrim($values,', ');
            $sql = "insert into {$tableName} ( {$columns} ) VALUES ( {$values} );";
            $rst = $this->sqlExecute($sql,$bind);
            return $this->doneExecute($rst);
        }else{
            return 'Parameter 2 invalid';
        }
    }

    /**
     * 执行删除数据的操作
     * @param $tableName
     * @param $whrMap
     * @return int|string
     */
    protected function deleteRecords($tableName,$whrMap=array()){
        $sql = "delete from {$tableName} ";
        $bind = array();
        if(!empty($whrMap) and is_array($whrMap)){
            $rst = $this->makeWhere($whrMap);
            $sql .= $rst[0];
            $bind = $rst[1];
        }
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }

    /**
     * 获取表格中的数据
     * 参数二遵循标准分段规则,推荐用于代替getFrom方法
     *
     * <bug>
     *  当遇到中文字段时，对之进行json化会变成null
     *  {"ROOMNO":"00D000001","AREA":"4","BUILDING":"\u6559\u5b66\u697c5                       ","NO":"000            ","JSN":"\u5357\u533a-\u4f53\u80b2\u9986\u5f62\u4f53\u5ba42  ","SEATS":60,"TESTERS":0,"EQUIPMENT":"Q","STATUS":0,"PRIORITY":"02","USAGE":"C","REM":"                    ","RESERVED":0,null:null,null:null}
     * </bug>
     * @param $tblName
     * @param null|array $whr 与getFrom有点区别,遵循的规则不同
     * @param bool $count
     * @return array|integer|string
     */
    protected function getTable($tblName,$whr=null,$count=false){
//        vardump($tblName,$whr,$count);
        $sql = $count?"select count(*) as ROWS from {$tblName} ":"select * from {$tblName} ";
        $whr = $this->makeWhere($whr);
        $sql .= $whr[0];
        $rst = $this->sqlQuery($sql,$whr[1]);
//        mist($rst,$sql,$whr[1],$this->getDbError());
        if(false === $rst){
            return $this->getErrorMessage();
        }else{
            return $count?$rst[0]['ROWS']:$rst;
        }
    }


    /**
     * 获取一张表中的的数据
     * @param string $tableName 表名称
     * @param array $map  查询条件，map如果需要使用[]进行转意的需要用户手动进行
     * @param boolean $count 是否只获取数量,默认为false表示获取数据
     * @return string|integer|array 返回错误信息(string) 或者 数量(仅仅查询数目的时候，参数三为true时) 或者 表中的数据(二维数组的形式)
     */
    protected function getFrom($tableName,$map=null,$count=false){
        $where = ' where ';
        $sql = $count?"select count(*) as ROWS from {$tableName} ":"select * from {$tableName} ";
        $bind = array();
        if(NULL !== $map and is_array($map)){
            foreach($map as $key=>$val){
                //默认使用等于符号
                $operator = ' = ';
                $value = $val;

                if(is_array($val)){
                    $operator = isset($val[1])?" {$val[1]} ":' = ';
                    $value = trim($operator) === '%'?doWithBindStr($val[0]):$val[0];
                }

                $keyname = strtolower(trim($key,' :')); //保留[]
                $bindFieldName = trim($keyname,'[]'); //绑定名称去除[]
                $where .= " {$keyname} $operator :{$bindFieldName} and";
                $bind[":{$bindFieldName}"] = $value;
            }
            //去掉最后一个end
            $where = substr($where,0,strlen($where)-3);
            $sql .= count($map)?$where:'';
        }

        $rst = $this->sqlQuery($sql,$bind);
//        vardump($rst,$sql,$bind,$this->getErrorMessage());
        return !$rst?$this->getErrorMessage():($count?$rst[0]['ROWS']:$rst);
    }



    /**
     * 综合字段绑定的方法
     * <code>
     *      $operator = '='
     *          $fieldName = :$fieldName
     *          :$fieldName => trim($fieldValue)
     *
     *      $operator = 'like'
     *          $fieldName = :$fieldName
     *          :$fieldName => dowithbinstr($fieldValue)
     *
     *      $operator = 'in|not_in'
     *          $fieldName in|not_in array(...explode(...,$fieldValue)...)
     * </code>
     * @param string $fieldName 字段名称
     * @param string|array $fieldValue 字段值
     * @param string $operator 操作符
     * @param bool $translate 是否对字段名称进行转义,MSSQL中使用[]
     * @return array
     */
    protected function makeFieldBind($fieldName,$fieldValue,$operator='=',$translate=false){
//        vardump('-->',$fieldName,$fieldValue,$operator,$translate,'<--');
        $fieldName = trim($fieldName,' :[]');
        $bindFieldName = null;
        if(false !== strpos($fieldName,'.')){
            $arr = explode('.',$fieldName);
            $bindFieldName = ':'.array_pop($arr);
        }elseif(mb_strlen($fieldName,'utf-8') < strlen($fieldName)){//其他编码
            $bindFieldName = ':'.md5($fieldName);
        }else{
            $bindFieldName = ":{$fieldName}";
        }
        $operator = strtolower(trim($operator));
        $sql = $translate?" [{$fieldName}] ":" {$fieldName} ";
        $bind = array();

        switch($operator){
            case '=':
                $sql .= " = {$bindFieldName} ";
                $bind[$bindFieldName] = $fieldValue;
                break;
            case 'like':
                $sql .= " like {$bindFieldName} ";
                $bind[$bindFieldName] = doWithBindStr($fieldValue);
                break;
            case 'in':
            case 'not in':
                if(is_string($fieldValue)){
                    $sql .= " {$operator} ({$fieldValue}) ";
                }elseif(is_array($fieldValue)){
                    $sql .= " {$operator} ('".implode("','",$fieldValue)."')";
                }else{
                    return 'The parameter 1 "$fieldValue" is invalid!';
                }
                break;
            default:
                return 'The parameter 2 "$operator" is invalid!';
        }
        return array(
            $sql,
            $bind,
        );
    }


    /**
     * <note>
     *      片段准则
     *      $map == array(
     *           //第一种情况,连接符号一定是'='//
     *          'key' => $val,
     *          'key' => array($val,true,$operator),//布尔值情况如下,遗留问题，参数二和三应该倒置
     *          //第二种情况，数组键，数组值//
     *          array('key','val','like|=',true),//参数4的值为true时表示对key进行[]转义
     *          //第三种情况，字符键，数组值//
     *          'assignSql' => array(':bindSQLSegment',value)//与第一种情况第二子目相区分的是参数一以':' 开头
     *      );
     * </note>
     * @param $map
     * @param bool $and 表示是否使用and作为连接符，false时为,
     * @return array
     */
    protected function makeSegments($map,$and=true){
        //初始值与参数检测
        $bind = array();
        $sql = '';
        if(empty($map)){
            return array($sql,$bind);
        }
        $connect = $and?'and':',';


        //元素连接
        foreach($map as $key=>$val){
            if(is_numeric($key)){
                //第二种情况
                $rst = $this->makeFieldBind(
                    $val[0],
                    $val[1],
                    isset($val[2])?$val[2]:' = ',
                    !empty($val[3])
                );
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $connect";
                    $bind = array_merge($bind, $rst[1]);
                }
            }elseif(is_array($val) and strpos($val[0],':') === 0){
                //第三种情况,复杂类型，由用户自定义
                $sql .= " {$key} $connect";
                $bind[$val[0]] = $val[1];
            }else{
                //第一种情况
                $translate = false;
                $operator = '=';
                if(is_array($val)){
                    $translate = isset($val[1])?$val[1]:false;
                    $operator = isset($val[2])?$val[2]:'=';
                    $val = $val[0];
                }
                $rst = $this->makeFieldBind($key,trim($val),$operator,$translate);//第一种情况一定是'='的情况
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $connect";
                    $bind = array_merge($bind, $rst[1]);
                }
            }
        }
        $result = array(
            substr($sql,0,strlen($sql)-strlen($connect)),//去除最后一个and
            $bind,
        );
        return $result;
    }


    /**
     * 构建where子句
     * 区别于makeSegments的是在sql中加入了where
     * @param $map
     * @return array
     */
    protected function makeWhere($map){
        $rst = $this->makeSegments($map);
        if(!empty($map)){
            $rst[0] = " where {$rst[0]} ";
        }
        return $rst;
    }


    /**
     * 处理执行类查询的受到影响行数结果
     * @param mixed $rst 查询结果
     * @return int|string ** string表示出错信息，integer表示受影响行数目 **
     */
    protected function doneExecute($rst){
        if(false === $rst){
            return $this->getErrorMessage();
        }
        return $rst;
    }
    /**
     * 处理查询结果
     * @param mixed $rst
     * @param boolean $getAllResult 决定返回全部结果还是第一条结果,默认是true即全部返回
     * @return string|array ** string表示出错信息，array表示受影响行数目(一维还是二维由参数二决定) **
     */
    protected function doneQuery($rst,$getAllResult=true){
        //查询出错返回错误信息
        if(false === $rst) return $this->getErrorMessage();
        //判断是否是主键条件查询
        if($getAllResult){
            return $rst;
        }else{
            return empty($rst)?array():$rst[0];
        }
    }




    /**
     * 判断是否有不合法的参数存在，不合法的参数参照参数一
     * 第一个参数将会被认为是不合法的值，参数一可以是单个字符串或者数组
     * 第二个参数开始是要比较的参数列表，如果任何一个参数"匹配"了参数一，将返回true表示存在不合法的参数
     * @return bool
     */
    public static function checkInvalidExist(){
        $params = func_get_args();
        $invalidVal = array_shift($params);
        foreach ($params as $key=>&$val){
            if(is_array($invalidVal)){
                if(in_array(trim($val),$invalidVal,true)){
                    //在严格模式下查找
                    return true;
                }
            }else{
                if($invalidVal === $val){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * 获取数据库发送的错误信息
     * 实际部署模式下建议将这个键置为false
     * @return string
     */
    public function getErrorMessage(){
        return self::$_switcher['SHOW_ERROR']?
            '<br /><span style="color: #6293BB"><b>调试信息：</b>'.$this->getDbError().'</span>'//错误信息
            :'';
    }



    public function initExcel(){
        if(null === $this->_php_excel){
            vendor("PHPExcel.PHPExcel");
            $this->_php_excel = new PHPExcel();
        }
        $this->_php_excel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
    }
    public function exportExcel($title){
        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename = $title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($this->_php_excel, 'Excel5');
        $objWriter->save('php://output');
    }


    /**
     * 初始化连接
     * @return array [worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns)//第几页
     *                  ,array  //表格数据
     *              ]
     * @var PHPExcel_Reader_Excel2007
     */
    protected $_phpexcel_reader = null;
    protected function initImport($info){
        if(!is_file($info['path'])){
            throw new Exception("File '{$info['path']}' do not exists!");
        }
        //导入phpExcel
        vendor("PHPExcel.PHPExcel");
        //设置php服务器可用内存，上传较大文件时可能会用到
        ini_set('memory_limit', '1024M');
        if ( $info['type'] == "application/vnd.ms-excel" ){
            $info['type'] = 'Excel5';
        }
        elseif ( $info['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ){
            $info['type'] = 'Excel2007';
        }else{
            throw new Exception('上传的文件MIME为'.$info['type']);
        }
        $this->_phpexcel_reader = PHPExcel_IOFactory::createReader($info['type']);
        $WorksheetInfo = $this->_phpexcel_reader->listWorksheetInfo($info['path']);
        $this->_phpexcel_reader->setReadDataOnly(true);
        $objPHPExcel = $this->_phpexcel_reader->load($info['path']);
        $sheetData = $objPHPExcel->getSheet(0)->toArray(null,true,true,true);
        return array($WorksheetInfo,$sheetData);
    }



















    //********************************** EXCEL IO ****************************************************--//

    protected static $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

    //单元格对齐模式
    const ALI_LEFT = 0;
    const ALI_CENTER = 1;
    const ALI_RIGHT = 2;
    //单元格值的类型
    const TYPE_STR = 1;
    const TYPE_NUM = 2;

    const DEFALUT_CELL_WIDTH = 14;

    //样式定义
    protected $titleStyle = null;
    protected $littleTitleStyle = null;
    protected $defaultBodyStyle = null;
    /**
     * @var PHPExcel
     */
    protected $objPHPExcel = null;

    public function initPHPExcel(){
        //由于vender基于的import使用了内部缓存，可以多次导入一个类,当使用了PHPExcel相关类时必须先调用该函数
        vendor("PHPExcel.PHPExcel");
        $this->titleStyle = array('font' => array('bold' => true, 'color' => array('argb' => '00000000'), 'size' => 18),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER));
        $this->littleTitleStyle = array('font' => array('bold' => false,'color'=>array('argb' => '00000000'),'size'=>10),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER));
        $this->defaultBodyStyle = array('font' => array('bold' => false,'color'=>array('argb' => '00000000'),'size'=>10),
            'alignment' => array('vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER));
    }

    /**
     * 导出
     * @param array $data
     *            array 'expCellName'  名称列配置
     *          array 'expTableData'  数据列配置
     * @param string $opfilename 输出文件名
     * @return null 直接输出excel文件内容
     */
    public function fullyExportExcelFile($data, $opfilename = null) {
        $this->objPHPExcel = new PHPExcel();
        $date = date("Ymd", time());
        $xlsTitle = iconv('utf-8', 'gb2312', $data['title']);
        $fileName = isset($opfilename) ? $opfilename : $xlsTitle . "-" . $date;

        $cellTitle = $data['head'];
        $cellValue = $data['body'];
        $cellNum = count($cellTitle);
        $dataNum = count($cellValue);
        $keys = array_keys($cellTitle);

        /*-- 设置大标题 --*/
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . self::$cellName[$cellNum - 1] . '1');
        //设置样式
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(isset($data['titledstyle'])?$data['titledstyle']:$this->titleStyle);
        //设置单元格值(设置值和样式的区别就是getActiveSheet和setActiveSheetIndex)
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1', $data['title']);

        //记录数据域单元格对齐信息
        $titlealign = array();

        /*-- 设置标题列 --*/
        $activeSheet = $this->objPHPExcel->getActiveSheet();
        $i = 0;
        foreach($cellTitle as $key=>$val){
            $cellname = self::$cellName[$i] . '2';
            $cellval = isset($cellTitle[$key]['title'])?$cellTitle[$key]['title']:$cellTitle[$key][0];//用0作角标可以简化配置
            //居中设置
            switch($cellTitle[$key]['type']){
                case self::TYPE_NUM:
                    $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($cellname,$cellval , PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    break;
                case self::TYPE_STR:
                case null://null的情况下设为字符模式
                default:
                $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($cellname, $cellval,PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $titlealign[$i] = $cellTitle[$key]['align'];
            //宽度设置
            $activeSheet->getColumnDimension(self::$cellName[$i])->setWidth(isset($cellTitle[$key]['width'])?$cellTitle[$key]['width']:self::DEFALUT_CELL_WIDTH);
            //标题栏样式设计
            $activeSheet->getStyle($cellname)->applyFromArray(isset($data['headstyle'])?$data['headstyle']:$this->littleTitleStyle);
            $i++;
        }

        /*-- 设置数据列 --*/
        for ($i = 0; $i < $dataNum; $i++) {
            $row = $cellValue[$i];
            for ($j = 0; $j < $cellNum; $j++) {
                $curcellname = self::$cellName[$j] . ($i + 3);
                //应用对齐设置
                $alignObj =$activeSheet->getStyle($curcellname)->getAlignment();
                switch($titlealign[$j]){
                    case self::ALI_RIGHT:
                        $alignObj->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        break;
                    case self::ALI_LEFT:
                        $alignObj->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        break;
                    case self::ALI_CENTER://默认居中
                    default:
                        $alignObj->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
                //值与风格设置
                $activeSheet->setCellValue($curcellname, trim($row[$keys[$j]]));
                $activeSheet->getStyle($curcellname)->applyFromArray(isset($data['bodystyle'])?$data['bodystyle']:$this->defaultBodyStyle);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    /**
     * @param Array $map 数据域映射关系对象 兼 表头域验证
     * @param callable $callback 回调函数对象，传递引用
     * @param int $datastartline 默认数据开始的行，默认为2
     * @return array 数据域的二维数组
     * @throws PHPExcel_Reader_Exception
     */
    public function importExcel($map,$callback = null,$datastartline = 2){
        $data = array();
        $data['status'] = 'error';
        $data['data'] = array();
        $headlines = null;
        $file = $this->getUploadFile();
        if(!$file || is_string($file)){
            $data['msg'] = is_string($file)?$file:'$_FILE is empty,maybe the file is not found or invalid!';
            return $data;
        }
        $filename = $file[0]['savepath'] . $file[0]['savename'];
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $this->objPHPExcel = $objReader->load($filename, 'utf-8');
        //获取第0张sheet对象
        $sheet = $this->objPHPExcel->getSheet(0);
        //总行数和总列数
        $highestRow = $sheet->getHighestRow();
        $activeSheet = $this->objPHPExcel->getActiveSheet();
        $sheetData = $sheet->toArray(null,true,true,true);
        //获取表头数组 表头所在行默认为数据域开始行的前一行,且无法修改
        $headlineno = $datastartline-1;
        if($headlineno < 0){
            $data['msg'] = 'The cause due to lacking of headline! ';
            return $data;
        }
        $headlines = $sheetData[$headlineno];
        for ($i = $datastartline; $i <= $highestRow; $i++) {
            //仅第一次验证headline
            if ($i == $datastartline) {
                foreach ($map as $key => $val) {
                    $colnm = isset($val['colnm']) ? $val['colnm'] : $val[0];
                    $coltitlenm = isset($val['text']) ? $val['text'] : $val[1];
                    //不想要验证这一行，可能是无关紧要的数据
                    if (isset($coltitlenm)) {
                        if (trim($headlines[$colnm]) != trim($coltitlenm)) {
                            $data['msg'] = 'The headline format of Excel on '.$colnm.' is invalid! ';
                            return $data;
                        }
                    }
                }
            }
            //获取数据域
            $row = array();
            foreach ($map as $key => $val) {
                $colnm = isset($val['colnm']) ? $val['colnm'] : $val[0];
                $cellval = $activeSheet->getCell($colnm . '' . $i)->getValue();
                if (isset($callback) && is_callable($callback) ) {
                    $cellval = $callback($colnm, $cellval, $i);
                }
                $row[$key] = $cellval;
            }
            $data['data'][] = $row;
        }
        $data['status'] = 'success';
        return $data;
    }


    /**
     * @return array 文件信息数组
     *         bool  未检测到文件上传
     *         string   文件上传失败信息
     */
    private function getUploadFile(){
        if(!empty($_FILES)){
            import('ORG.Net.UploadFile');
            $config = array(
                'allowExts' => array('xlsx', 'xls'),
                'savePath' => './Public/uploads/',
                'saveRule' => 'time',
            );
            $upload = new UploadFile($config);
            if ($upload->upload()) {
                $info = $upload->getUploadFileInfo();
                return empty($info)?$upload->getErrorMsg():$info;
            }
        }
        return false;
    }


































/***************  通用模块方法  ******************************************************/
    /**
     * 获取指定类型的学年学期数据
     * @param string $type 类型代码
     * @return array|string 返回查询错误信息或者结果集(一维数组)
     */
    public function getYearTerm($type){
        return $this->doneQuery($this->getFrom('YEAR_TERM',array(
            ':TYPE'=>$type,
        )),false);
    }
    /**
     * 获取学院的代号名称映射
     * SCHOOL字段代表代号，NAME代表名称字段
     * @param bool $keyIsName
     * @return array|string
     */
    public function getSchoolMap($keyIsName=false){
        $schools = $this->getTable('SCHOOLS');
        if(is_string($schools)){
            return $schools;
        }
        $rst = array();
        foreach($schools as $row){
            if($keyIsName){
                $rst[trim($row['NAME'])] = $row['SCHOOL'];
            }else{
                $rst[$row['SCHOOL']] = trim($row['NAME']);
            }
        }
//        vardump($rst);
        return $rst;
    }

    /**
     * 获取教师信息
     * @param $teacherno
     * @return array|int|string
     */
    public function getTeacher($teacherno){
        return $this->getFrom('TEACHERS',array(
            'TEACHERNO' => trim($teacherno),
        ));
    }

    /**
     * 获取 课程类型一 的下拉框数据
     * @return array|string
     */
    public function getComboCourseTypeOptions(){
        return $this->getComboData('coursetypeoptions','NAME','VALUE');
    }

    /**
     * 获取修课方式的下拉框数据
     * @return array|string
     */
    public function getComboApproach(){
        return $this->getComboData('COURSEAPPROCHES','NAME','VALUE');
    }

    public function getComboRegCode(){
        return $this->getComboData('REGCODEOPTIONS','NAME','VALUE');
    }


    /**
     * 从timelist通过主键的组合获取数据
     * @param $year
     * @param $term
     * @param $who
     * @param $type
     * @param $para
     * @return NULL|string|array
     */
    public function getTimelist($year,$term,$who,$type,$para=NULL){
        $sql = 'SELECT * FROM TIMELIST WHERE [YEAR]=:year AND TERM=:term AND WHO=:who AND [TYPE]=:type';
        $bind = array(
            ':year'=>$year,
            ':term'=>$term,
            ':who'=>$who,
            ':type'=>$type,
        );

        if(NULL !== $para){
            $sql .= ' AND PARA=:para';
            $bind[':para'] = $para;
        }
        $rst = $this->doneQuery($this->sqlQuery($sql,$bind),true);
//        vardump($rst,$sql,$bind,$this->getErrorMessage());
        return $rst;
    }

    /**
     * 获取学生信息
     * @param $studentno
     * @param bool $eq 判断条件是否是等于，false时为like
     * @return array|int|string
     */
    public function getStudents($studentno,$eq=true){
        $rst = $this->getTable('STUDENTS',array(
            'STUDENTNO'=>array($studentno,false,$eq?' = ':' like '),
        ));
//        var_dump($rst,$studentno,$this->getDbError());
        return $rst;
    }
    /**
     * 按照班级号码获取学生信息
     * @param $classno
     * @param bool $eq
     * @return array|int|string
     */
    public function getStudentsByClassno($classno,$eq=true){
        return $this->getFrom('STUDENTS',array(
            'CLASSNO'=>array($classno,$eq?' = ':' like '),
        ));
    }
    /**
     * @param array $map
     * @return int|string
     */
    public function clearTestPlan($map=array()){
        return $this->deleteRecords('TESTPLAN',$map);
    }

    /**
     * @param array $map
     * @return int|string
     */
    public function clearTeacherPlan($map=array()){
        return $this->deleteRecords('TEACHERPLAN',$map);
    }

    /**
     * @param array $map
     * @return int|string
     */
    public function clearSchedule($map=array()){
        return $this->deleteRecords('SCHEDULE',$map);
    }

    /**
     * @param array $map
     * @return int|string
     */
    public function clearSchedulePlan($map=array()){
        return $this->deleteRecords('SCHEDULEPLAN',$map);
    }


    /**
     * @param $roleid
     * @return array|string
     */
    public function getMethodRoles($roleid){
        $sql = 'select rtrim(ROLES) as ROLES from METHODS where METHODID=:ID';
        $bind = array(
            ':ID' => $roleid
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind),false);
    }

    /**
     * 获取教师信息
     * @param null|string $teacherno 教师号
     * @return array|string
     */
    public function getTeacherInfo($teacherno=NULL){
        $sql = '
SELECT users.USERNAME,
users.PASSWORD,
users.DAYSTOLIVE,
users.ROLES,
CAST(users.PROMPT AS INT) AS PROMPT,
teachers.TEACHERNO,
teachers.NAME AS TEACHERNAME,
teachers.POSITION,
teachers.SCHOOL,
teachers.SEX
FROM USERS JOIN TEACHERS ON USERS.TEACHERNO=TEACHERS.TEACHERNO
WHERE users.TEACHERNO = :TEACHERNO';
        $user = session('S_USER_INFO');
        $bind = array(':TEACHERNO'=>isset($teacherno)?$teacherno:$user["TEACHERNO"]);
        $rst = $this->sqlQuery($sql, $bind);
        return $this->doneQuery($rst,false);
    }



    /**
     * 获取班级信息
     * @param $classno
     * @param boolean $count 是否只获取其数量
     * @return array|int|string
     */
    public function getClass($classno,$count=false){
        return $this->getTable('CLASSES',array(
            'CLASSNO' => $classno,
        ),$count);
    }

    /**
     * 1 ---> MON
     * 整数到字符串的转换
     * @param $day
     * @return null|string
     * @throws Exception
     */
    public function dayInt2String($day){
        $fieldname = null;
        switch($day){
            case 1:
                $fieldname = 'MON';
                break;
            case 2:
                $fieldname = 'TUE';
                break;
            case 3:
                $fieldname = 'WES';
                break;
            case 4:
                $fieldname = 'THU';
                break;
            case 5:
                $fieldname = 'FRI';
                break;
            case 6:
                $fieldname = 'SAT';
                break;
            case 7:
                $fieldname = 'SUN';
                break;
            default:
                throw new Exception('错误的参数！');
        }
        return $fieldname;
    }


}