<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class SupplierAccountModel extends PublicModel
{

    protected $tableName = 'supplier_account';
    protected $dbName = 'erui_supplier'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }

    /**
     * 通过产品获取供应商信息
     * @return
     * @author
     */
    public function getInfo($data)
    {
        $where = array();
        if(!empty($data['spu'])){
            $where['spu'] = $data['spu'];
        } else{
            jsonReturn('','-1001','spu不可以为空');
        }
        //通过spu查询供应商编号
        $supplier_id = $this->where($where)
                            ->field('supplier_id')
                            ->select();

        $field = 'id,customer_id,email,user_name,mobile,role,first_name,last_name,phone,status,login_count,last_login_time,login_failure_count,created_by,created_at';
        $group = array();
        $SupplierBrandModel = new SupplierBrandModel();
        foreach ($supplier_id as $v) {
            $info = $SupplierBrandModel->field($field)->where(array('supplier_id' => $v['supplier_id']))->select();
            $group[] = $info;
        }
        if(!empty($group)){
            return $group;
        } else {
            return array();
        }
    }


    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string $lang 语言
     * @return mix
     * @author
     */
//    public function Exist($data)
//    {
//        if (!empty($data['email']) && !empty($data['user_name'])) {
//            $map1['email'] = $data['email'];
//            $map1['user_name'] = $data['user_name'];
//            $map1['_logic'] = 'or';
//            $map['_complex'] = $map1;
//            $row = $this->table('erui_buyer.t_buyer_account')->where($map)->select();
//        } else {
//            $row = $this->table('erui_buyer.t_buyer_account')->where($data)->select();
//        }
//        return empty($row) ? false : $row;
//    }

//    /**
//     * 获取列表
//     * @param data $data;
//     * @return array
//     * @author
//     */
//    public function getlist($data,$limit,$order='ug.id desc') {
//        $sql  = 'SELECT ug.id,ug.group_id,g.name as group_name,ug.user_id,u.name as user_name ';
//        $sql .= ' FROM '.$this->tableName.'as ug';
//        $sql .= ' LEFT JOIN t_group AS g ON t_group.`id` = ug.`group_id`';
//        $sql .= ' LEFT JOIN t_user AS u ON u.`id` = ug.`user_id`';
//        if(!empty($data['group_id'])){
//            $sql .= ' WHERE g.`group_id` = '.$data['group_id'];
//        }
//        if(!empty($limit)){
//            $sql .= ' LIMIT '.$limit['page'].','.$limit['num'];
//        }
//        $sql .= ' ORDER BY '.$order;
//        $res = $this->query( $sql );
//        return $res;
//    }

//    /**
//     * 获取列表
//     * @param  int  $id
//     * @return array
//     * @author jhw
//     */
//    public function detail($id = '') {
//        $where['id'] = $id;
//        if(!empty($where['id'])){
//            $row = $this->where($where)
//                ->field('id,parent_id,name,description,status')
//                ->find();
//            return $row;
//        }else{
//            return false;
//        }
//    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string $enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author
     */
//    public function login($data)
//    {
//        $where = array();
//        if (!empty($data['email'])) {
//            $where['email'] = $data['email'];
//        }
//        if (!empty($data['user_name'])) {
//            $where['user_name'] = $data['user_name'];
//        }
//        if (empty($where['user_name']) && empty($where['email'])) {
//            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
//            exit();
//        }
//        if (!empty($data['password'])) {
//            $where['password_hash'] = md5($data['password']);
//        }
//        $where['status'] = 'VALID';
//        $row = $this->where($where)
//            ->field('id,customer_id,email,user_name,mobile,role,first_name,last_name,phone,status,login_count,last_login_time,login_failure_count')
//            ->find();
//        return $row;
//    }

    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author
     */
    public function update_data($data, $where)
    {

        if (isset($data['email'])) {
            $arr['email'] = $data['email'];
        }
        if (isset($data['user_name'])) {
            $arr['user_name'] = $data['user_name'];
        }
        if (isset($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
        }
        if (isset($data['password_hash'])) {
            $arr['password_hash'] = md5($data['password_hash']);
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
        if (isset($data['phone'])) {
            $arr['phone'] = $data['phone'];
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
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author
     */
    public function create_data($create = [])
    {
        if (isset($create['customer_id'])) {
            $arr['customer_id'] = $create['customer_id'];
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
        $arr['created_at'] = Date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
}

