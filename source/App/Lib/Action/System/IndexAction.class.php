<?php
/**
 * 系统首页
 * User: cwebs
 * Date: 13-11-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    /**
     * 系统维护首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 新建用户
     */
    public function newUser()
    {
        $this->display();
    }
}