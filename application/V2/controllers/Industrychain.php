<?php

/**
 * name: Industrychain.php
 * desc: 产业链控制器
 */
class IndustrychainController extends PublicController {
    public function __init() {
           parent::__init();
    }
    //上下游获取添加数据
    public function createChainAction(){
        $buyer_id = 123;
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['buyer_id'] = $buyer_id;
        $data['created_by'] = $created_by;
        $model = new IndustrychainModel();
        $res = $model->createChain($data);
        if($res==false){
            echo json_encode(array("code" => "-101", "message" => "输入不可以为空或字符过多"));
            exit();
        }
        echo json_encode(array("code" => "1", "message" => "提交成功"));
    }
    //上下游数据详情
    public function chainListAction(){
        $buyer_id = '123';
        $created_by = $this->user['id'];
        $model = new IndustrychainModel();
        $res = $model->chainList($buyer_id,$created_by);
        if($res){
            echo json_encode(array("code" => "1", "data" => $res, "message" => "返回数据"));
        }
    }
}