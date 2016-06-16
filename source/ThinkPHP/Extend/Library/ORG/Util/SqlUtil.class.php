<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/7/7
 * Time: 10:02
 *
 *
 * 导入方法：import('ORG.Util.SqlUtil');
 *
 */
class SqlUtil {

    /**
     * 绑定参数
     * @param $to
     * @param $from
     * @param string $dft
     * @return array
     */
    public static function bind($to,$from,$dft=''){
        $rst = array();
        $temp = '';
        if(is_string($to)){
            $to = @explode(',',$to);
        }
        foreach($from as &$val){
            $val = strtolower($val);
        }
        foreach($to as &$val){
            $val = strtolower($val);
            if(0 !== strpos(':',$val)){
                $temp = ':'.$val;
            }
            if(isset($from[$val])){
                $to[$temp] = $from[$val];
            }else{
                $to[$temp] = $dft;
            }
        }
        return $rst;
    }


    /**
     * 将一段select查询的sql转化成计算数量的形式
     * @param $select
     * @return null|string
     */
    public static function getCountFormat($select){
        if(0 === stripos(trim($select),'select') ){
            return 'select count(*) as ROWS from ('. $select .') AS _temp';
        }
        return NULL;
    }

    /**
     * 批量绑定
     * @param $dst
     * @param $src
     * @param string $dft
     * @return NULL|Array null不合法的输入
     */
    public static function bindParamInBatch($dst,$src,$dft=''){
        $tobind = array();
        $temp = null;
        if(is_string($dst)){
            $temp = explode(',',$dst);
        }
        foreach($temp as $val){
            $val = trim($val);
            $pos = strpos($val,':');
            if($pos !== false && $pos === 0){
                //在第一个位置
                $tobind[$val] = $dft;
            }elseif($pos === false){
                //未出现冒号
                $tobind[':'.$val] = $dft;
            }elseif($pos > 0){
                //出错，非法字符
                return NULL;
            }
        }

        if(is_array($src)){
            //判断是关联数组还是数字键数组,由第一个参数的键决定(暂定)
            $key = key($src);
            $i = 0;
            if(is_numeric($key)){
                //数字键以顺序绑定
                foreach($tobind as $key=>$val){
                    $tobind[$key] = $src[$i];
                    ++$i;
                }
            }else{
                foreach($tobind as $key=>$val){
                    $tobind[$key] = $src[str_replace(':','',$key)];
                    ++$i;
                }
            }

        }elseif(is_string($src)){
            //待定
        }else{
            return NULL;
        }
        return $tobind;
    }

}