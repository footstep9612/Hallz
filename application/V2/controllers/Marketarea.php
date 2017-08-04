<?php

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   营销区域
 */
class MarketareaController extends PublicController {

    public function init() {
        // parent::init();

        $this->_model = new MarketAreaModel();
    }

    /**
     * Description of MarketAreaModel
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function listAction() {
        $data = $this->get() ?: $this->getPut();
        $data['lang'] = $this->get('lang', '') ?: $this->getPut('lang', '');
        $market_area_model = new MarketAreaModel();
        if (redisGet('Market_Area_listall_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Market_Area_listall_' . md5(json_encode($data))), true);
        } else {
            $arr = $market_area_model->getlist($data, false);
            if ($arr) {
                redisSet('Market_Area_listall_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /**
     * Description of 详情
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function infoAction() {
        $bn = $this->get('bn', '') ?: $this->getPut('bn', '');

        if (!$bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $result = $this->_model->info($bn, $lang);

            if ($result) {
                if (!$data) {
                    $data = $result;
                    $data['name'] = null;
                    unset($data['name']);
                }
                $data[$lang]['name'] = $result['name'];
            }
        }

        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif ($data === []) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('market_area_list_*');
        $redis->delete($keys);
    }

    /**
     * Description of 新增营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function createAction() {
        $data = $this->getPut();
        $result = $this->_model->create_data($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function updateAction() {
        $data = $this->getPut();
        $result = $this->_model->update_data($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 删除营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function deleteAction() {
        $data = $this->getPut();
        $id = $this->get('id') ?: $this->getPut('id');
        if ($id) {
            $ids = explode(',', $id);
            if (is_array($ids)) {
                $where['id'] = ['in', $ids];
            } else {
                $where['id'] = $id;
            }
        }
        $result = $this->_model->where($where)->save(['status' => 'DELETE']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
