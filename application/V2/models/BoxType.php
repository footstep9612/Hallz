<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BoxType
 * @author  zhongyg
 * @date    2017-8-1 16:49:20
 * @version V2.0
 * @desc   发货箱型
 */
class BoxTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'box_type';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }
  /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = [];
        $this->_getValue($data, $condition, 'lang');
        $this->_getValue($data, $condition, 'bn');
        $this->_getValue($data, $condition, 'box_type_name');
        $this->_getValue($data, $condition, 'status');
        if (!isset($data['status'])) {
            $data['status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'deleted_flag', 'bool');

        return $data;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $data = $this->_getCondition($condition);
            $this->field('id,bn,box_type_name')
                    ->where($data);
            return $this->order($order)
                            ->select();
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }
}