<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuyerSource
 * @author  zhongyg
 * @date    2018-4-22 10:28:07
 * @version V2.0
 * @desc
 */
class BuyerQuestionnaireModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_source';
    protected $dbName = 'buyer_questionnaire'; //数据库名称
    protected $g_table = 'erui_buyer.buyer_questionnaire';

    public function __construct() {
        parent::__construct();
    }

    public function create_data($buyer_id, $questionnaire) {
        $data['buyer_id'] = $buyer_id;
        $data['questionnaire'] = $questionnaire;
        $data['created_at'] = date('Y-m-d H:i:s');


        return [$this->add($data), $data['token']];
    }

}
