<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/6/4
 * Time: 16:31
 *
 * 工具类
 */
class Utils{

    /**
     * 如果传入参数存在null的情况
     * @return bool
     */
    public static function allNotNull(){
        $params = func_get_args();
        foreach ($params as $key=>$val){
            if(!isset($val)) return false;
        }
        return true;
    }

    /**
     * 如果传入参数存在空的情况
     * @return bool
     */
    public static function allNotEmpty(){
        $params = func_get_args();
        foreach ($params as $key=>$val){
            if(empty($val)) return false;
        }
        return true;
    }

    /**
     * 获取格式化后的变量信息
     * @return string
     */
    public static function varsMsg(){
        $params = func_get_args();
        $str = '<pre>';
        foreach ($params as $key=>$val){
            $str .= '<hr /><b>Param '.$key.' is:</b><br />';
        }
        $str .= '</pre>';
        exit($str);
    }

    /**
     * 字符串中间不能出现空格，否则被视为非法,导致程序结束
     * @param $reqname
     * @return string
     */
    public static function getSafeParam($reqname){
        $val = trim(urldecode($reqname));
        return strpos($val,'') === false?addslashes($val):null;
    }

    /**
     * 获取下拉框的数据
     * @param $tablename 表的名称
     * @param $valfield 值列的名称
     * @param $txtfield 文本列的名称
     * @param string $defaultname 默认显示的文本，其键值对将作为数组的首个元素
     * @param boolean $dfttxt 默认值是显示值，false为真实值
     * @return array|string
     */
    public static  function getComboData($tablename,$valfield,$txtfield,$defaultname = '',$dfttxt=true){
        $tablename = Utils::getSafeParam($tablename);
        $valfield  = Utils::getSafeParam($valfield);
        $txtfield  = Utils::getSafeParam($txtfield);
        $sjson=array();
        if(Utils::allNotNull($tablename,$valfield,$txtfield)){
            $model = M("SqlsrvModel:");
            $arr=$model->sqlQuery("select $valfield as value,$txtfield as text from $tablename");
            foreach($arr as $val){
                $sjson2 = array();
                $sjson2['text']=trim($val['text']);
                $sjson2['value']=trim($val['value']);                  // 把学院数据转成json格式给前台的combobox使用
                $cpval = $dfttxt?trim($sjson2['text']):trim($sjson2['value']);
                if($cpval == trim($defaultname)){
                    //交换第一个元素和本元素的位置
                    $temp = $sjson[0];
                    $sjson[0] = $sjson2;
                    $sjson2 = $temp;
                }
                array_push($sjson,$sjson2);
            }
        }
        $sjson=json_encode($sjson);

        return $sjson;
    }


    public static function fillChar($str,$len=0){
        if($len<1 || $len<strlen($str)) return $str;
        return sprintf("%0".$len."s", $str);
    }


}