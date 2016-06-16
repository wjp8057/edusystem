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

use think\Collection;
use think\Db;
use think\db\Builder;
use think\db\Query;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\Model;
use think\Paginator;

class MyQuery extends  Query
{

    /**
     * 批处理执行SQL语句
     * 批处理的指令都认为是execute操作
     * @access public
     * @param array $sql SQL批处理指令
     * @return boolean
     */
    public function batchQuery($sql = [])
    {
        MyAccess::checkAccess('E');
        return parent::batchQuery();
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param boolean $replace 是否replace
     * @param boolean $getLastInsID 是否获取自增ID
     * @param string $sequence 自增序列名
     * @return integer
     */
    public function insert(array $data, $replace = false, $getLastInsID = false, $sequence = null)
    {
        MyAccess::checkAccess('A');
        return parent::insert($data,$replace,$getLastInsID,$sequence);
    }

    /**
     * 批量插入记录
     * @access public
     * @param mixed $dataSet 数据集
     * @return integer
     */
    public function insertAll(array $dataSet)
    {
        MyAccess::checkAccess('A');
        return parent::insertAll($dataSet);
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @return int
     * @throws PDOException
     */
    public function selectInsert($fields, $table)
    {
        MyAccess::checkAccess('A');
        return parent::selectInsert($fields,$table);
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @return int
     * @throws Exception
     * @throws PDOException
     */
    public function update(array $data)
    {
        MyAccess::checkAccess('M');
        return parent::update($data);
    }

    /**
     * 查找记录
     * @access public
     * @param array|string|Query|\Closure $data
     * @return Collection|false|\PDOStatement|string
     * @throws DbException
     * @throws Exception
     * @throws PDOException
     */
    public function select($data = [])
    {
        MyAccess::checkAccess('R');
        return parent::select($data);
    }
    /**
     * 查找单条记录
     * @access public
     * @param array|string|Query|\Closure $data
     * @return array|false|\PDOStatement|string|Model
     * @throws DbException
     * @throws Exception
     * @throws PDOException
     */
    public function find($data = [])
    {
        MyAccess::checkAccess('R');
        return parent::find($data);
    }

    /**
     * 删除记录
     * @access public
     * @param array $data 表达式
     * @return int
     * @throws Exception
     * @throws PDOException
     */
    public function delete($data = [])
    {

        MyAccess::checkAccess('D');
        return parent::delete($data);
    }
}
