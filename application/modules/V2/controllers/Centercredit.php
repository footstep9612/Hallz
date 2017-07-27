<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 14:46
 */

class CentercreditController extends PublicController
//class CentercreditController extends Yaf_Controller_Abstract
{
    private $input;
    public function __init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 会员授信列表
     * @author klp
     */
    public function listAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getListCredit($this->input);
        $this->returnInfo($result);
    }

    /**
     * 采购商企业银行信息
     * @pararm  customer_id(采购商编号)  lang(语言)(默认英文)
     * @return array
     * @author klp
     */
    public function getBuyerInfoAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getBuyerInfo($this->input);
        $this->returnInfo($result);
    }

    /**
     * 查看审核信息   --t_buyer_evaluation
     * @author klp
     */
    public function getCheckInfoAction(){
        $BuyerappapprovalModel = new BuyerappapprovalModel();
        $result = $BuyerappapprovalModel->getCheckInfo($this->input);
        $this->returnInfo($result);
    }

    /**
     * 审核会员授信(待完善,触发中信保审核)
     * @author klp
     */
    public function checkAction(){
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['approved_by'] = $userInfo['name'];
        $BuyerappapprovalModel = new BuyerappapprovalModel();
        $result = $BuyerappapprovalModel->checkCredit($this->input);
        if($BuyerappapprovalModel::STATUS_APPROVED == $result){
            //触发信保审核
        }
        $this->returnInfo($result);
//        require_once('Edi.php');
//        $ediController = new EdiController();
//        $res = $ediController->testAction();
//        var_dump($res);die;
    }

    /**
     * 区域等级会员维护列表(未写)
     * @author klp
     */
    public function gradeListAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getGradeList($this->put_data);
        $this->returnInfo($result);
    }


    //统一回复调用方法
    function returnInfo($result){
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }
}