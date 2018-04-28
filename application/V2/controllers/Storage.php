<?php
/**
 * 仓库管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:29
 */
class StorageController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 新加仓库
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function createAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            jsonReturn('',MSG::ERROR_PARAM,'请选择国家！');
        }
        $storage_name = $this->getPut('storage_name');
        if (empty($storage_name)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入仓库名称！');
        }
        $storageModel = new StorageModel();
        $flag = $storageModel->createData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 更新仓库
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function updateAction(){
        $id = $this->getPut('id');
        if (empty($id)) {
            jsonReturn('',MSG::ERROR_PARAM,'id不能为空！');
        }
        $storageModel = new StorageModel();
        $flag = $storageModel->updateData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 删除仓库
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function deleteAction(){
        $id = $this->getPut('id');
        if (empty($id)) {
            jsonReturn('',MSG::ERROR_PARAM,'id不能为空！');
        }
        $storageModel = new StorageModel();
        $flag = $storageModel->deleteData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

}