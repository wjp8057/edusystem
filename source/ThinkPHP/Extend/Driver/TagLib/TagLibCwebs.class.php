<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-5
 * Time: 上午10:20
 */
defined('THINK_PATH') or exit();

class TagLibCwebs extends TagLib{
    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'sqllist'    => array('attr'=>'id,sql,file,bind,delimiter','level' => 3),
        'sqlselect'    => array('attr'=>'id,sql,file,value,title,headerKey,headerTitle,style,attr,onchange,selected','close'=>0)
    );

    public function _sqllist($attr,$content){
        $tag   =    $this->parseXmlAttr($attr,'sqllist');
        $parseStr = "";

        $delimiter = (empty($tag["delimiter"]) ? '@' : $tag["delimiter"]);
        $data = $this->getData($tag["sql"], $tag["file"], $this->getBind($tag["bind"], $delimiter));
        if($data===null) return $parseStr;

        $cacheIterateId = md5($attr . $content);
        $this->tpl->set($cacheIterateId, $data);

        $parseStr = '<?php $'.$tag["id"].' = $this->get("'.$cacheIterateId.'"); ?>';
        $parseStr .= $this->tpl->parse($content);
        return $parseStr;
    }

    public function _sqlselect($attr){
        trace($attr);
        $tag   =    $this->parseXmlAttr($attr,'sqlselect');
        $parseStr = "";
        $data = $this->getData($tag["sql"], $tag["file"]);
        if($data===null) return $parseStr;

        $parseStr .= '<select name="'.$tag["name"].'" id="'.$tag["id"].'"';
        $parseStr .= empty($tag["style"]) ? "" : 'style="'.$tag["style"].'"';
        $parseStr .= empty($tag["onchange"]) ? "" : 'onchange="'.$tag["onchange"].'"';
        $parseStr .= empty($tag["attr"]) ? "" : $tag["attr"];
        $parseStr .= '>';

        if(!empty($tag["headerkey"]) || !empty($tag["headertitle"])){
            $parseStr .= '<option value="'.$tag["headerkey"].'">'.$tag["headertitle"].'</option>';
        }

        foreach($data as $key=>$Entity){
            $parseStr .= '<option value="'.$Entity[$tag["value"]].'"'.(isset($tag["selected"]) && $Entity[$tag["value"]]==$tag["selected"]?"selected":"").'>'.$Entity[$tag["title"]].'</option>';
        }
        $parseStr .= '</select>';
        return $parseStr;
    }

    /**
     * 根据sql或者file取得数据
     * @param $sql
     * @param $file
     * @return null
     */
    private function getData($sql, $file, $bind=array()){
        //如果sql和file都为空，则返回空
        if(empty($sql) && empty($file)) return null;

        $model = M("SqlsrvModel:");
        //如果file不为空，则读取file，并把内容赋给sql
        if(!empty($file)){
            $sql = $model->getSqlMap($file);
        }
        return $model->sqlQuery($sql, $bind);
    }

    /**
     * 格式化bind
     * @param $str
     * @return array
     */
    private function getBind($str,$delimiter="@"){
        $bind = array();
        $str = str_replace($delimiter, '=>', $str);
        @eval('$bind = array('.$str.');');
        return $bind;
    }
}