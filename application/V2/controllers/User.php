<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class UserController extends PublicController {


    public function __init() {
        //   parent::__init();


    }
    /*
     * 用户列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['username'])){
            $where['username'] = $data['username'];
        }
        if(!empty($data['group_id'])){
            $where['group_id'] = $data['group_id'];
        }
        if(!empty($data['role_id'])){
            $where['role_id'] = $data['role_id'];
        }
        if(!empty($data['pageSize'])){
            $where['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $user_modle =new UserModel();
        $data =$user_modle->getlist($where);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
         * 用户详情
         * */
    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new UserModel();
        $res = $model->info($data['id']);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['password'])) {
            $arr['password_hash'] = md5($data['password']);
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "密码不可以都为空"));
        }
        if(!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if(!isMobile($arr['mobile'])){
                $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "手机不可以都为空"));
        }
        if(!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if(!isEmail($arr['email'])){
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "邮箱不可以都为空"));
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户名不能为空"));
        }
        if(!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if(!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if(!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if(!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if(!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if(!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if(!empty($data['user_no'])) {
            $arr['user_no'] = $data['user_no'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户编号不能为空"));
        }
        $arr['created_by'] = $this->user['id'];
        $model = new UserModel();
        $login_arr['user_no'] = $data['user_no'];
        $check = $model->Exist($login_arr);
        if($check){
            $this->jsonReturn(array("code" => "-101", "message" => "用户编号已存在"));
        }
        $res=$model->create_data($arr);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = [ 'id'=>$res ];
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['password'])) {
            $arr['password_hash'] = md5($data['password']);
        }
        if(!empty($data['id'])) {
            $where['id'] = $data['id'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
        if(!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if(!isEmail($arr['email'])){
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        }
        if(!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if(!isMobile($arr['mobile'])){
                $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
            }
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if(!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if(!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if(!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if(!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if(!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if(!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if(!empty($data['user_no'])) {
            $arr['user_no'] = $data['user_no'];
        }
        if(!empty($data['status'])) {
            $arr['status'] = $data['status'];
        }
        $model = new UserModel();
        $res = $model->update_data($arr,$where);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }


    public function getRoleAction(){
        if($this->user['id']){
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        }else{
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }


}