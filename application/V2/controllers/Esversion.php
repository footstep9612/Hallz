<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 产品
 */
class EsversionController extends EsproductController {

    //put your code here
    public function init() {

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
    }

    /*
     * 获取列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function UpdateVersionAction() {
        $update_version = $this->getPut('update_version');
        $select_version = $this->getPut('select_version');
        $alias = $this->getPut('alias', 'erui_goods');
        $model = new EsVersionModel();
        $version = $model->getVersion();
        $es = new ESClient();
        if ($update_version && $update_version != $version['update_version']) {
            $this->version = $update_version;
            $flag = $model->UpdateVersion($alias, $update_version, $select_version);
            if ($flag) {
                $this->indexAction();
            } else {
                $this->setCode(1);
                $this->setMessage('失败!');
                $this->jsonReturn();
            }
        } elseif ($select_version && $select_version != $version['select_version']) {
            $flag = $model->UpdateVersion($alias, $update_version, $select_version);
            if ($flag) {
                if ($es->index_existsAlias($version['alias'] . '_' . $version['select_version'], $version['alias'])) {
                    $es->index_deleteAlias($version['alias'] . '_' . $version['select_version'], $version['alias']);
                }
                $es->index_alias($version['alias'] . '_' . $version['update_version'], $version['alias']);
                $this->setCode(1);
                $this->setMessage('成功!');
                $this->jsonReturn();
            } else {
                $this->setCode(1);
                $this->setMessage('失败!');
                $this->jsonReturn();
            }
        } else {
            $this->setCode(1);
            $this->setMessage('失败!');
            $this->jsonReturn();
        }
    }

}