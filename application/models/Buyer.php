<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class BuyerModel extends PublicModel {
    //put your code here
    protected $tableName = 'buyer';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.t_buyer_account';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        $sql =  'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .=  '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM '.$this->g_table;
        if ( !empty($condition['where']) ){
            $sql .= ' WHERE '.$condition['where'];
        }
        $sql .= ' Order By '.$order;
        if ( $condition['page'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['countPerPage'];
        }
        return $this->query( $sql );
    }

//    /**
//     * 获取列表
//     * @param  string $code 编码
//     * @param  int $id id
//     * @param  string $lang 语言
//     * @return mix
//     * @author zyg
//     */
//    public function info($id = '') {
//        $where['id'] = $id;
//        return $this->where($where)
//                        ->field('id,user_id,name,email,mobile,status')
//                        ->find();
//    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where=array();
        if(!empty($data['email'])){
            $where['email'] = $data['email'];
        }
        if(!empty($data['mobile'])){
            $where['mobile'] = $data['mobile'];
        }
        if(empty($where['mobile'])&&empty($where['email'])){
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if(!empty($data['password'])){
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'NORMAL';
        $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        $row = $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
//    public function Exist($data) {
//        $sql =  'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
//        $sql .=  '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
//        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
//        $sql .= ' FROM '.$this->g_table;
//        $where = '';
//        if ( !empty($data['email']) ){
//            $where .= " where email = '" .$data['email']."'";
//        }
//        if ( !empty($data['mobile']) ){
//            if($where){
//                $where .= " or mobile = '" .$data['mobile']."'";
//            }else{
//                $where .= " where mobile = '" .$data['mobile']."'";
//            }
//
//        }
//        if ( $where){
//            $sql .= $where;
//        }
//        $row = $this->query( $sql );
//        return empty($row) ? false : (isset($row['customer_id']) ? $row['customer_id'] : true);
//    }



    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($create = [])
    {
        $data['customer_id'] = $create['customer_id'];
        $data['serial_no'] = $create['serial_no'];
        $data['lang'] = $create['lang'];
        $data['name'] = $create['name'];
        $data['bn'] = $create['bn'];
        $data['profile'] = $create['profile'];
        $data['country'] = $create['country'];
        $data['province'] = $create['province'];
        $data['reg_date'] = date('Y-m-d');
        $data['logo'] = $create['logo'];
        $data['official_website'] = $create['official_website'];
        $data['brand'] = $create['brand'];
        $data['bank_name'] = $create['bank_name'];
        $data['swift_code'] = $create['swift_code'];
        $data['bank_address'] = $create['bank_address'];
        $data['bank_account'] = $create['bank_account'];
        $data['buyer_level'] = $create['buyer_level'];
        $data['credit_level'] = $create['credit_level'];
        $data['finance_level'] = $create['finance_level'];
        $data['logi_level'] = $create['logi_level'];
        $data['qa_level'] = $create['qa_level'];
        $data['steward_level'] = $create['steward_level'];
        $data['remarks'] = $create['remarks'];
        $data['apply_at'] = date('Y-m-d H:i:s');
        $data['approved_at'] = $create['approved_at'];
        $datajson = $this->create($data);
        return $this->add($datajson);
    }
    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author klp
     */
    public function getInfo($data)
    {
        $where=array();
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        //$lang = $data['lang'] ? strtolower($data['lang']) : (browser_lang() ? browser_lang() : 'en');
        $buyerInfo = $this->where($where)
                          ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                          ->find();
        if($buyerInfo){
            //通过顾客id查询用户信息
            $buyerAccount = new BuyerAccountModel();
            $userInfo = $buyerAccount->field('email,user_name,phone,first_name,last_name,status')
                ->where(array('customer_id' => $buyerInfo['customer_id']))
                ->find();
            //通过顾客id查询用户邮编
            $buyerAddress = new BuyerAddressModel();
            $zipCode = $buyerAddress->field('zipcode')->where(array('customer_id' => $buyerInfo['customer_id']))->find();
            $info = array_merge($buyerInfo,$userInfo);
            $info['zipCode'] = $zipCode;

            return $info;
        } else{
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition){
        if ($condition['customer_id']) {
            $where['customer_id'] = $condition['customer_id'];
        }
        if ($condition['name']) {
            $data['name'] = $condition['name'];
        }
        if ($condition['bn']) {
            $data['bn'] = $condition['bn'];
        }
        if ($condition['country']) {
            $data['country'] = $condition['country'];
        }
        if ($condition['official_website']) {
            $data['official_website'] = $condition['official_website'];
        }
        if ($condition['buyer_level']) {
            $data['buyer_level'] = $condition['buyer_level'];
        }
        if ($condition['province']) {
            $data['province'] = $condition['province'];
        }
        if ($condition['city']) {
            $data['city'] = $condition['city'];
        }
        if($condition['status']){
            switch ($condition['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_DELETE:
                    $data['status'] = $condition['status'];
                    break;
            }
        }

        return $this->where($where)->save($data);

    }

}
