<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
//定义被thinkphp删除的几个常量
define('TITLE','宁波城市学院教务管理系统');
define('COPYRIGHT','Copyright by keysoft corp. @2016 技术支持：88221932 版本号：201606017  最佳分辨率：1440*900');
// 定义应用目录
// 定义应用目录
define('APP_PATH', __DIR__ . '/../../../verison2/app/');
// 开启调试模式
define('APP_DEBUG', true);
// 加载框架引导文件
require __DIR__ . '/../../../verison2/thinkphp/start.php';