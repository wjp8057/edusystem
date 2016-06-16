<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/7/6
 * Time: 16:12
 *
 *
 * 导入方法：import('ORG.Util.YearTermUtil');
 *
 */
class YearTermUtil{



    /**
     * 判断学年学期是否存在且未锁定
     * @param $yt
     * @return array
     */
    public static function isYearTermAvailable($yt){
        $message = array(
            'type'=>1,
            'message'=>'未知的错误！'
        );
        if(empty($yt)){
            $message['message'] = '学年学期信息不存在！';
        }elseif($yt['LOCK']){
            $message['message'] = '开课计划初始化工作已被教务处锁定！';
        }else{
            $message['type'] = 0;
            $message['message'] = '';
        }
        return $message;
    }




}

