<?php
/**
 * 专题
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:43
 */
class SpecialController extends PublicController{
    public function init() {
        $this->token = false;
    }

    /**
     * 专题商品
     */
    public function goodsAction(){
        $input = $this->getPut();
        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', 'special_id不能为空');
        }

        $model = new SpecialGoodsModel();
        if(!isset($input['sku']) || empty($input['sku'])){    //列表
            $result = $model->getList($input);
        }else{    //详情
            $result = $model->getInfo($input);
        }

        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 专题详情
     */
    public function infoAction(){
        $input = $this->getPut();
        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', 'special_id不能为空');
        }

        $sModel = new SpecialModel();
        $result = $sModel->getInfo(intval($input['special_id']));
        if($result!==false){
            //获取广告信息
            if(isset($input['ad_on']) && $input['ad_on']){
                $spModel = new SpecialPositionModel();
                $fields = 'name,description,remark,sort_order,thumb,link';
                $adInfo = $spModel->getList(['special_id'=>intval($input['special_id']),'type'=>'A'],'',$fields);

                $result['adList'] = $adInfo ? $adInfo['data'] : [];
            }

            //获取楼层推荐位信息
            if(isset($input['position_on']) && $input['position_on']){

                $spModel = new SpecialPositionModel();
                $positionInfo = $spModel->getList(['special_id'=>intval($input['special_id'])],['type'=>['neq','A']]);
                if($positionInfo && isset($input['position_goods_on']) && $input['position_goods_on']){
                    foreach($positionInfo['data'] as $index => $posi){
                        $spdModel = new SpecialPositionDataModel();
                        $goodsInfo = $spdModel->getList(['special_id'=>$posi['special_id'],'position_id'=>$posi['id']]);

                        $positionInfo['data'][$index]['goodsInfo'] = $goodsInfo ? $goodsInfo['data'] : [];
                    }
                }

                $result['positionList'] = $positionInfo ? $positionInfo['data'] : [];
            }
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 推荐商品列表
     */
    public function pglistAction(){
        $input = $this->getPut();
        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', 'special_id不能为空');
        }
        if (!isset($input['position_id']) || empty($input['position_id'])) {
            jsonReturn('', 'position_id不能为空');
        }
        $spgModel = new SpecialPositionGoodsModel();
        $result = $spgModel->getList(intval($input['special_id']), intval($input['position_id']), isset($input['size']) ? intval($input['size']): 0);
        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
}