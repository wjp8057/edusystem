<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'url_route_on' => true,

    'template'               => [
        // 模板文件名分隔符，默认为文件夹分隔符
        /*  'view_depr'    => DS,*/
        'view_depr'    => '_',
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '<',
        // 标签库标签结束标记
        'taglib_end'   => '>',
    ],
    'session'                => [
        'prefix'         => '', //默认为think
        'type'           => '',
        'auto_start'     => true,
    ],
// 默认模块名
    'default_module'         => 'home',
// 禁止访问模块

// 默认控制器名
    'default_controller'     => 'index',
// 默认操作名
    'default_action'         => 'index',

    'log'                    => [
        'type' => 'File',  // 日志记录方式，支持 file socket trace sae
        'path' => LOG_PATH,  // 日志保存目录
    ],
    'logdb'=>[
        // 数据库类型
        'type'        => 'sqlsrv',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => '127.0.0.1',
      /*  'hostname'    => '172.18.0.41',*/
        // 数据库名
        'database'    => 'logdb',
        // 数据库用户名
        'username'    => 'sa',
        // 数据库密码
        'password'    => 'comefirstfangrenfu@0',
        // 数据库连接端口
        'hostport'    => '1433',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
];
