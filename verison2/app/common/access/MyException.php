<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com>
// +----------------------------------------------------------------------
namespace app\common\access;
/**异常代码
 * Class MyException
 * @package app\common\access
 */
class MyException
{
    const UN_DEFINED = '700'; //未定义错误
    const NOT_LOGIN = '701'; //尚未登录
    const LOGIN_BY_OHTER = '702'; //在其他地方登陆了。
    const WITH_OUT_PERMISSION = '703'; //无权访问
    const USER_NOT_EXISTS = '704'; //用户不存在
    const PARAM_NOT_CORRECT = '705'; //参数错误
}