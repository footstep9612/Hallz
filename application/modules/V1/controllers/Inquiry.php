<?php
/**
 * name: Inquiry.php
 * desc: 询价单控制器
 * User: zhangyuliang
 * Date: 2017/6/16
 * Time: 14:51
 */
class InquiryController extends PublicController {

    public function __init() {
        parent::__init();
    }

    //返回询价单流水号
    public function getInquiryNoAction() {
        $data['serial_no'] = $this->getInquirySerialNo();
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }
    }

    //查询询单号（项目代码）是否存在
    public function checkInquiryNoAction() {
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $results = $inquiry->checkInquiryNo($where);

        $this->jsonReturn($results);
        /*if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }*/
    }

    //询价单列表
    public function getListAction(){
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $results = $inquiry->getlist($where);
var_dump($results);die;
        $this->jsonReturn($results);
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $results = $inquiry->getinfo($where);

        $this->jsonReturn($results);
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $results = $inquiry->add_data($data);
        $this->jsonReturn($results);
    }

    //修改询价单
    public function updateAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $results = $inquiry->update_data($data);
        $this->jsonReturn($results);
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);
        //$where['inquiry_no'] = '10001';
        $results = $inquiry->delete_data($where);
        $this->jsonReturn($results);
    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $data = $attach->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->add_data($data);
        //var_dump($id);die;
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('保存失败!');
            $this->jsonReturn();
        }

    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->delete_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = json_decode(file_get_contents("php://input"), true);

        $data = $Item->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $Item->add_data($data);

        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('保存失败!');
            $this->jsonReturn();
        }

    }

    //删除明细
    public function delItemAction() {
        $Item = new InquiryItemModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $Item->delete_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }

    //明细附件列表
    public function getListItemAttachAction()
    {
        $ItemAttach = new InquiryItemAttachModel();

        $where = json_decode(file_get_contents("php://input"), true);

        $data = $ItemAttach->getlist($where);

        if (!empty($data)) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加明细附件
    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $ItemAttach->add_data($data);

        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('保存失败!');
            $this->jsonReturn();
        }

    }

    //删除明细附件
    public function delItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $ItemAttach->delete_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }
}