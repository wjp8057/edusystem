<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/12/3 21:13
// +----------------------------------------------------------------------

namespace app\common\vendor;

require ROOT_PATH . '/vendor/Captcha/src/Captcha.php';
use think\Config;

class Captcha {
    public static function validimg()
    {
        $id=1;
        $captcha = new \Captcha((array)Config::get('captcha'));
        return $captcha->entry($id);
    }
    public static function check($code){
        $captcha = new \Captcha((array)Config::get('captcha'));
        return $captcha->check($code,1);
    }
}