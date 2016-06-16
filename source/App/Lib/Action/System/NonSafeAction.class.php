<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 2015/7/24
 * Time: 8:59
 */
class NonSafeAction extends CommonAction{
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model=new MenuActionsModel();
    }

    public function getMenuComboTree(){
        if($this->_hasJson) {
            $data = $this->model->findAll(null,$_REQUEST["NAME"], $_REQUEST["ID"], $_REQUEST["URL"]);
            if($data==null) $data = Array();
            elseif(count($data)==1){
                $data = $this->model->findByMenu($data[0]);
            }

            $this->ajaxReturn($this->model->getComboTree($data));
        }
    }

    public function getRolesComboBox(){
        if($this->_hasJson) {
            $this->ajaxReturn($this->model->getRolesComboBox());
        }
    }
}