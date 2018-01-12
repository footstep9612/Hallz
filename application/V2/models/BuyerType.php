<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 客户类型名称
 */
class BuyerTypeModel extends PublicModel {
    protected $dbName = 'erui_config';
    protected $tableName = 'buyer_type';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param string $lang
     * 客户类型列表-王帅
     */
    public function buyerNameList($lang='zh'){
        if($lang=='zh'){
            $info=$this->field('id type_id,name type_name')->where(array('deleted_flag'=>'N'))->order('sort asc')->select();
        }else{
            $info=$this->field('id type_id,en as type_name')->where(array('deleted_flag'=>'N'))->order('sort asc')->select();
        }
        return $info;
    }

    /**
     * @param $id 客户类型id
     * @param string $lang  语言
     * 王帅
     */
    public function buyerTypeNameById($id,$lang='zh'){
        $cond=array(
            'id'=>$id,
            'deleted_flag'=>'N'
        );
        if($lang=='zh'){
            $name=$this->field('id type_id,name type_name')->where($cond)->find();
        }else{
            $name=$this->field('id type_id,en type_name')->where($cond)->find();
        }
        return $name;
    }
}
