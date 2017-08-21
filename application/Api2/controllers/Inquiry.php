<?php

/**
 * User: zhangyuliang
 * desc: 询价单控制器
 * Date: 2017/6/27
 * Time: 15:30
 */
class InquiryController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    //返回询价单流水号
    public function getInquiryNoAction() {
        $data['serial_no'] = $this->getInquirySerialNo();
        if (!empty($data)) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }
    }

    //获取询单总数
    public function getInquiryCountAction() {
        $inquiry = new InquiryModel();
        $where['buy_id'] = $this->user['id'];
        $data['count'] = $inquiry->getcount($where);

        if ($data['count'] > 0) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn($data);
        }
    }

    //添加询价单
    public function addAction() {
        $inquiry = new InquiryModel();
        $data = $this->getPut();

        $inquiryNo = $inquiry->checkInquiryNo($data['inquiry_no']);

        if ($inquiryNo['code'] == 1) {
            $data['customer_id'] = $this->user['customer_id'];
            $data['inquirer'] = $this->user['user_name'];
            $data['inquirer_email'] = $this->user['email'];

            $results = $inquiry->addData($data);
        } else {
            $results = $inquiryNo;
        }
        $this->jsonReturn($results);
    }

    //询价单列表
    public function getListAction() {
        $inquiry = new InquiryModel();
        $item = new InquiryItemModel();
        $where = $this->getPut();

        $results = $inquiry->getlist($where);

        foreach ($results['data'] as $key => $val) {
            $test['serial_no'] = $val['serial_no'];
            $results['data'][$key]['quantity'] = $item->getcount($test);
        }

        $this->jsonReturn($results);
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = $this->getPut();

        $results = $inquiry->getinfo($where);

        $this->jsonReturn($results);
    }

    //修改询价单
    public function updateAction() {
        $inquiry = new InquiryModel();
        $data = $this->getPut();

        $results = $inquiry->update_data($data);
        $this->jsonReturn($results);
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where = $this->getPut();
        //$where['inquiry_no'] = '10001';
        $results = $inquiry->delete_data($where);
        $this->jsonReturn($results);
    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = $this->getPut();

        $results = $attach->getlist($where);
        //var_dump($data);die;
        $this->jsonReturn($results);
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->getPut();

        $results = $attach->add_data($data);

        $this->jsonReturn($results);
    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->getPut();

        $results = $attach->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();

        $results = $Item->getlist($where);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getInfoItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();

        $results = $Item->getinfo($where);
        $this->jsonReturn($results);
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->getPut();

        $results = $Item->add_data($data);
        $this->jsonReturn($results);
    }

    //删除明细
    public function delItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->getPut();

        $results = $Item->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细附件列表
    public function getListItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();

        $where = $this->getPut();

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    //添加明细附件
    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->getPut();

        $results = $ItemAttach->add_data($data);
        $this->jsonReturn($results);
    }

    //删除明细附件
    public function delItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->getPut();

        $results = $ItemAttach->delete_data($data);
        $this->jsonReturn($results);
    }

}