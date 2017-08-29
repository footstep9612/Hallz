<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class BuyerAccountModel extends PublicModel {

    protected $tableName = 'buyer_account';
    protected $dbName = 'erui2_buyer'; //数据库名称

    public function __construct($str = '') {

        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 判断用户是否存在
     * @param  string $data 用户名
     * @param  string $sql 语言
     * @return mix
     * @author jhw
     */

    public function Exist($data, $sql = 'or') {
        if (!empty($data['email']) && !empty($data['user_name'])) {
            if ($sql == 'or') {
                $map1['email'] = $data['email'];
                $map1['user_name'] = $data['user_name'];
                $map1['_logic'] = 'or';
                $map['_complex'] = $map1;
            } else {
                $map['email'] = $data['email'];
                $map['user_name'] = $data['user_name'];
            }
            $row = $this->table('erui2_buyer.buyer_account')->where($map)->select();
        } else {
            $row = $this->table('erui2_buyer.buyer_account')->where($data)->select();
        }
        return empty($row) ? false : $row;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'ug.id desc') {
        $sql = 'SELECT ug.id,ug.group_id,g.name as group_name,ug.user_id,u.name as user_name ';
        $sql .= ' FROM ' . $this->tableName . 'as ug';
        $sql .= ' LEFT JOIN t_group AS g ON t_group.`id` = ug.`group_id`';
        $sql .= ' LEFT JOIN t_user AS u ON u.`id` = ug.`user_id`';
        if (!empty($data['group_id'])) {
            $sql .= ' WHERE g.`group_id` = ' . $data['group_id'];
        }
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
        $sql .= ' ORDER BY ' . $order;
        $res = $this->query($sql);
        return $res;
    }

    /**
     * 获取用户信息
     * @param  array  $data
     * @return array
     * @author jhw
     */
    public function info($data) {
        if (!empty($data['buyer_id'])) {
            $row = $this->where(['buyer_id' => $data['buyer_id'], 'deleted_flag' => 'N'])
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 获取用户信息
     * @param  array  $data
     * @return array
     * @author jhw
     */
    public function getinfo($data) {
        $model = new BuyerModel();
        $table = $model->getTableName();
        $buyeraddress_model = new BuyerAddressModel();

        $buyeraddress_table = $buyeraddress_model->getTableName();
        if (!empty($data['buyer_id'])) {
            $row = $this->alias('b')
                    ->join($table . ' as ba on b.buyer_id=ba.id', 'left')
                    ->join($buyeraddress_table . ' as bad on b.buyer_id=bad.buyer_id', 'left')
                    ->where(['b.buyer_id' => $data['buyer_id'], 'b.deleted_flag' => 'N'])
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where = array();
        if (!empty($data['email'])) {
            $where['email'] = $data['email'];
        }
        if (!empty($data['user_name'])) {
            $where['user_name'] = $data['user_name'];
        }
        if (empty($where['user_name']) && empty($where['email'])) {
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if (!empty($data['password'])) {
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'VALID';
        $row = $this->where($where)
                ->field('id,buyer_id,email,mobile,user_name,password_hash,role,first_name,last_name,login_count,last_login_time,login_failure_count')
                ->find();
        return $row;
    }

    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (isset($data['email'])) {
            $arr['email'] = $data['email'];
        }
        if (isset($data['user_name'])) {
            $arr['user_name'] = $data['user_name'];
        }
        if (isset($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
        }
        if (isset($data['role'])) {
            $arr['role'] = $data['role'];
        }
        if (isset($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
        }
        if ($data['status']) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DELETE:
                    $arr['status'] = $data['status'];
                    break;
            }
        }
        if (!empty($where)) {
            $res =  $this->where($where)->save($arr);
        } else {
            return false;
        }
        if($res!==false){
            return true;
        }
        return false;
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['buyer_id'])) {
            $arr['buyer_id'] = $create['buyer_id'];
        }
        if (isset($create['email'])) {
            $arr['email'] = $create['email'];
        }
        if (isset($create['user_name'])) {
            $arr['user_name'] = $create['user_name'];
        }
        if (isset($create['mobile'])) {
            $arr['mobile'] = $create['mobile'];
        }
        if (isset($create['password_hash'])) {
            $arr['password_hash'] = $create['password_hash'];
        }
        if (isset($create['role'])) {
            $arr['role'] = $create['role'];
        }
        if (isset($create['first_name'])) {
            $arr['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $arr['last_name'] = $create['last_name'];
        }
        if (isset($create['phone'])) {
            $arr['phone'] = $create['phone'];
        }
        if (isset($create['status'])) {
            $arr['status'] = $create['status'];
        }
        $arr['created_at'] = Date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

    /**
     * 密码校验
     * @author klp
     */
    public function checkPassword($data,$userId) {
        if (!empty($userId['buyer_id'])) {
            $where['buyer_id'] = $userId['buyer_id'];
        } else {
            jsonReturn('', '-1001', '用户buyer_id不可以为空');
        }
        if (!empty($data['oldpassword'])) {
            $password = $data['oldpassword'];
        }
        $pwd = $this->where(['buyer_id'=>$where['buyer_id']])->field('password_hash')->find();

        if ($pwd['password_hash'] == $password) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 密码修改
     * @author klp
     * return bool
     */
    public function update_pwd($data, $token) {

        if (!empty($token['buyer_id'])) {
            $where['buyer_id'] = $token['buyer_id'];
        } else {
            jsonReturn('', '-1001', '用户buyer_id不可以为空');
        }
        if (!empty($data['password'])) {
            $new['password_hash'] = $data['password'];
        } else {
            jsonReturn('', '-1001', '新密码不可以为空');
        }
        return $this->where(['buyer_id'=>$where['buyer_id']])->save($new);
    }

}
