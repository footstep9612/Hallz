<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TransBoxType
 * @author  zhongyg
 * @date    2017-8-1 16:45:28
 * @version V2.0
 * @desc   运输方式对应发货箱型
 */
class TransBoxTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'trans_box_type';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 自动完成
     */

    protected $_auto = array(
        array('status', 'VALID'),
    );
    /*
     * 自动表单验证
     */
    protected $_validate = array(
        array('box_type_bn', 'require', '发货箱型简称不能为空'),
        array('trans_mode_bn', 'require', '运输方式简称不能为空'),
        array('status', 'require', '状态不能为空'),
    );

    /*
     * 获取当前时间
     */

    function getDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 搜索条件
     * @param array $condition;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = ['tbt.deleted_flag' => 'N'];
        $this->_getValue($data, $condition, 'status', 'string', 'tbt.status'); //状态
        if (!isset($data['tbt.status'])) {
            $data['tbt.status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'box_type_bn'); //名称
        $this->_getValue($data, $condition, 'trans_mode_bn'); //贸易术语简称
        if (isset($condition['keyword']) && $condition['keyword']) {
            $map = [];
            $this->_getValue($map, $condition, 'keyword', 'like', 'bt.box_type_name');
            $this->_getValue($map, $condition, 'keyword', 'like', 'tm.trans_mode');
            $map['_logic'] = 'or';
            $data['_complex'] = $map;
        }
        return $data;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'tbt.id desc') {
        try {
            $data = $this->_getCondition($condition);
            $redis_keys = md5(json_encode($data));
            if (redisExist('TransBoxType_' . $redis_keys)) {
                return json_decode(redisGet('TransBoxType_' . $redis_keys), true);
            }
            $result = $this->alias('tbt')
                    ->join('erui_dict.box_type bt on bt.bn=tbt.box_type_bn and bt.lang=\'zh\' and bt.deleted_flag=\'N\'', 'left')
                    ->join('erui_dict.trans_mode tm on tm.bn=tbt.trans_mode_bn and tm.lang=\'zh\' and tm.deleted_flag=\'N\'', 'left')
                    ->field('tbt.id,bt.box_type_name,tm.trans_mode,tbt.box_type_bn,'
                            . 'tbt.trans_mode_bn,tbt.created_by,tbt.created_at ')
                    ->order($order)
                    ->where($data)
                    ->select();
            redisSet('TransBoxType_' . $redis_keys, json_encode($result), 86400);
            return $result;
        } catch (Exception $ex) {

            return [];
        }
    }

    /**
     * 获取情
     * @param  string $bn 编码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id = '') {
        $where['id'] = $id;
        $redis_keys = $id;
        if (redisHashExist('TransBoxType', $redis_keys)) {
            return json_decode(redisHashGet('TransBoxType', $redis_keys), true);
        }
        $result = $this->where($where)
                ->field('id,box_type_bn,trans_mode_bn,created_by,created_at')
                ->find();
        redisHashSet('TransBoxType', $redis_keys, json_encode($result));
        return $result;
    }

    /**
     * 删除数据
     * @param  string $id id
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {
        if (!$id) {
            return false;
        } elseif (is_array($id)) {
            $where['id'] = ['in', $id];
        } elseif ($id) {
            $where['id'] = $id;
        }
        $data = ['status' => 'DELETED', 'deleted_flag' => 'Y',];
        $flag = $this->where($where)->save($data);

        if ($flag !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  array $update id
     * @return bool
     * @author jhw
     */
    public function update_data($update) {
        $data = $this->create($update);
        $where['id'] = $data['id'];
        $data['deleted_flag'] = 'N';

        $flag = $this->where($where)->save($data);
        if ($flag !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['id'])) {
            unset($create['id']);
        }
        $create['created_by'] = defined('UID') ? UID : 0;
        $create['created_at'] = date('Y-m-d H:i:s');
        $create['status'] = $create['status'] == 'INVALID' ? 'INVALID' : 'VALID';
        $data = $this->create($create);
        $flag = $this->add($data);
        if ($flag !== false) {
            return true;
        } else {
            return false;
        }
    }

}
