<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 9:34
 */
class GoodsController extends PublicController {

    private $input;

    public function init() {
        // error_reporting(E_ERROR);
        parent::init();
        $this->put_data = $this->put_data ? $this->put_data : json_decode(file_get_contents("php://input"), true);
        // Log::write(json_encode($this->put_data), Log::INFO);
    }

    /**
     * sku管理列表
     * @pararm 适用于:关联sku列表  审核列表  上架列表
     * @return array
     * @author klp
     */
    public function listAction() {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku基本详情
     * @pararm  sku编码 lang status
     * @return array
     * @author klp
     */
    public function skuInfoAction() {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getSkuInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 获取用户创建的第一个sku信息
     * @author klp
     */
    public function getFirstSkuAction() {
        $goodsModel = new GoodsModel();
        $arr = [];
        $result = $goodsModel->getSku($this->user, $this->put_data);
        if ($result) {
            $data['sku'] = $result[0]['sku'];
            $res = $goodsModel->getSkuInfo($data);

            $goodsModel = new GoodsAttachModel();
            $attach = $goodsModel->getSkuAttachsInfo($data);

            $GoodsCostPriceModel = new GoodsCostPriceModel();
            $supplierCost = $GoodsCostPriceModel->getInfo($data);
            if ($res) {
                $res['attachs'] = $attach ? $attach : [];
                $res['supplier_cost'] = $supplierCost ? $supplierCost : [];
                $arr = $res;
            }
        }
        jsonReturn($arr);
    }

    /**
     * sku属性详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttrsInfoAction() {
        $goodsModel = new GoodsAttrModel();
        $result = $goodsModel->getSkuAttrsInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku附件详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttachsInfoAction() {
        $goodsModel = new GoodsAttachModel();
        $result = $goodsModel->getSkuAttachsInfo($this->put_data);
        if ($result === false) {
            jsonReturn('', ErrorMsg::FAILED);
        } else {
            jsonReturn($result);
        }
    }

    /**
     * 查找用户信息
     * @pararm  用户id
     * @return
     * @author klp
     */
    public function userInfoAction() {
        if (empty($this->put_data['id'])) {
            jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
        }
        $userModel = new UserModel();
        $result = $userModel->info($this->put_data['id']);
        $this->returnInfo($result);
    }

    /**
     * 商品进货价格/供应商查询
     * @param   sku
     * @author  klp  2017/8/2
     */
    public function supplierCostInfoAction() {
        $GoodsCostPriceModel = new GoodsCostPriceModel();
        $result = $GoodsCostPriceModel->getInfo($this->put_data);
        if ($result === false) {
            jsonReturn('', ErrorMsg::FAILED);
        } else {
            jsonReturn($result);
        }
    }

    /**
     * sku新增/编辑  -- 总接口
     * @param  sku: sku编码不存在为新建,反之更新
     * @param          spu(编码)  name(名称)  show_name(展示名称) lang(语言数组)
     * @param  attr:  attr_no(属性编码) attr_name(属性名称)
     *                 ex_goods_attrs(商品属性)   spec_attrs(规格型号)  other_attrs(其它属性)  ex_hs_attrs(申报要素)
     *                注:属性添加时带其中一个flag
     * @param  attach:  attach_url(文件地址)
     * @param  supplier_cost:  supplier_id(供应商ID)
     * @example [
     *           sku:'',
     *           en=>[
     *                name:'',...
     *                attrs=>[],
     *           ],
     *          zh=>[],...
     *          attachs=>[]
     *          supplier_cost=>[]
     * ]
     *
     *  @return sku编号
     * @author  klp  2017/7-13
     */
    public function editSkuAction() {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->editSku($this->put_data);
        if ($result) {
            $this->updateEsgoods($this->put_data, $result);
        }
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            if ($this->put_data[$lang]['spu']) {
                $this->updateEsproduct([$lang => $lang], $this->put_data[$lang]['spu']);
            }
        }
        if (!$result || $result === false) {
            jsonReturn('', ErrorMsg::FAILED, '失败!');
        } else {
            jsonReturn($result);
        }
    }

    /*
     * 更新ESproduct
     */

    public function updateEsproduct($input, $spu) {
        $es_product_model = new EsProductModel();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            if (isset($input[$lang]) && $input[$lang]) {
                $es_product_model->create_data($spu, $lang);
            } elseif (empty($input[$lang])) {
                $es_product_model->create_data($spu, $lang);
            }
        }
    }

    /*
     * 更新ESgoods
     */

    public function updateEsgoods($input, $sku) {
        $es_product_model = new EsGoodsModel();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            if (isset($input[$lang]) && $input[$lang]) {
                $flag = $es_product_model->create_data($sku, $lang);
            } elseif (empty($input[$lang])) {
                $flag = $es_product_model->create_data($sku, $lang);
            }
        }
    }

    /**
     * sku状态更改  -- 总接口
     * @param    status_type(状态flag ) 存在为修改状态
     *           标志: check(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码  spu编码   lang语言
     * @example    $this->put_data = [
     *                 'status_type'=> 'check',
     *                     'skus'=>[
     *                         0 => [
     *                         'sku'=> '14979553',
     *                         'spu'=> '8832211',
     *                         'lang'=> 'zh',
     *                         'remarks' =>  ''
     *                         ],
     *                    ]
     *                 ]
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function modifySkuAction() {
        /*  $this->put_data = [
          'status_type'=> 'check',
          'skus'=>[
          0 => [
          'sku'=> '14979553',
          'spu'=> '8832211',
          'lang'=> 'zh',
          'remarks' =>  ''
          ],
          ]
          ];
          return $this->put_data; */
        if (empty($this->put_data)) {
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->modifySkuStatus($this->put_data);

        if ($result) {
            $esgoods_model = new EsGoodsModel();
            //新状态可以补充
            switch ($this->put_data['status_type']) {
                case 'check':    //报审
                    $status = GoodsModel::STATUS_CHECKING;
                    break;
                case 'valid':    //审核通过
                    $status = GoodsModel::STATUS_VALID;
                    break;
                case 'invalid':    //驳回
                    $status = GoodsModel::STATUS_INVALID;
                    break;
            }
            if ($this->put_data['lang']) {
                $lang = $this->put_data['lang'];
                $esgoods_model->changestatus($this->put_data['sku'], $status, $lang, defined('UID') ? UID : 0);
            } else {
                $langs = ['en', 'zh', 'es', 'ru'];
                foreach ($langs as $lang) {
                    $esgoods_model->changestatus($this->put_data['sku'], $status, $lang, defined('UID') ? UID : 0);
                }
            }
        }

        $this->returnInfo($result);
    }

    /**
     * sku删除  -- 总接口
     * @param     sku编码  spu编码   lang语言
     * @example   $this->put_data=[
     *                  0  => [
     *                       'sku'=> '3303060000010001',
     *                       'lang'=> 'zh'
     *                       ],
     *                      1  => [],...
     *                  ];
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function deleteRealSkuAction() {
        if (empty($this->put_data)) {
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->deleteSkuReal($this->put_data);

        if ($result === true) {
            $esgoods_model = new EsGoodsModel();

            if ($this->put_data['lang']) {
                $lang = $this->put_data['lang'];
                $esgoods_model->delete_data($this->put_data['sku'], $lang);
            } else {
                $langs = ['en', 'zh', 'es', 'ru'];
                foreach ($langs as $lang) {
                    $esgoods_model->delete_data($this->put_data['sku'], $lang);
                }
            }
        }
        $this->returnInfo($result);
    }

    /**
     * sku附件新增
     * @author  klp  2017/7-6
     */
    public function addSkuAttachAction() {
        /*  $this->put_data = [
          'sku'=>'666123',
          "attachs"=>[
          0=>[
          'supplier_id'    =>'333',
          'attach_type'	 =>'',
          'attach_name'	 =>'',
          'attach_url'     =>'a/b/c.png',
          'sort_order'     =>'0',
          ],
          ],
          ]; */
        $userInfo = getLoinInfo();
        $this->put_data['user_id'] = $userInfo['id'];
        $gattach = new GoodsAttachModel();
        $resAttach = $gattach->editSkuAttach($this->put_data);
        if ($resAttach) {

            if ($resAttach['code'] == 1) {

                $this->updateEsgoods(null, $this->put_data['sku']);
            }
            $this->jsonReturn($resAttach);
        } else {
            jsonReturn('', -1, '失败!');
        }
    }

    /**
     * sku附件删除
     * @param  "sku":['000001'，'000002',...]
     * @author  klp  2017/7-6
     */
    public function delSkuAttachAction() {
//        $this->put_data = ['123'];
        $gattach = new GoodsAttachModel();
//        $this->put_data = $this->getPut('sku');
        $resAttach = $gattach->deleteSkuAttach($this->put_data);
        if ($resAttach) {

            if ($resAttach['code'] == 1) {

                $this->updateEsgoods(null, $this->put_data['sku']);
            }
            $this->jsonReturn($resAttach);
        } else {
            jsonReturn('', -1, '失败!');
        }
    }

    /**
     * sku供应商  -- 通过生产商ID或名称获取供应商信息
     * @author  klp  2017/7-6
     */
    public function listSupplierAction() {
        $SupplierModel = new SupplierModel();
        $result = $SupplierModel->getlist($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku审核记录查询
     * @param sku
     * @author  klp  2017/8/2
     */
    public function checkInfoAction() {
        /*   $this->put_data =[
          'sku' =>'14979553',
          'lang' => 'zh'

          ]; */
        $ProductChecklogModel = new ProductCheckLogModel();
        $result = $ProductChecklogModel->getRecord($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 审核记录
     * @author link 2017-08-05
     */
    public function checklogAction() {
        $sku = ($this->getMethod() == 'GET') ? $this->getQuery('sku', '') : (isset($this->put_data['sku']) ? $this->put_data['sku'] : '');
        $lang = ($this->getMethod() == 'GET') ? $this->getQuery('lang', '') : (isset($this->put_data['lang']) ? $this->put_data['lang'] : '');

        if (empty($sku)) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if (empty($lang)) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $pchecklog = new ProductCheckLogModel();

        $logs = $pchecklog->getRecord(array('sku' => $sku, 'lang' => $lang), 'sku,lang,status,remarks,approved_by,approved_at');
        if ($logs !== false) {
            jsonReturn($logs);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     *   通过spu查询四种语言name
     * @author  klp  2017/7-22
     */
    public function getNameAction() {
        $productModel = new ProductModel();
        $result = $productModel->getName($this->put_data);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    //统一回复调用方法
    function returnInfo($result) {
        if ($result && !isset($result['code'])) {
            jsonReturn($result);
        } else {
            jsonReturn('', MSG::MSG_FAILED, '失败');
        }
        exit;
    }

    public function testInput() {
        $data = [
            "sku" => '',
            "en" => [
                'lang' => '',
                'spu' => '',
                'sku' => '',
                'qrcode' => '',
                'name' => '',
                'show_name' => '',
                'model' => '',
                'description' => '',
                'status' => '',
                'created_by' => '',
                'created_at' => '',
                'updated_by' => '',
                'updated_at' => '',
                'checked_by' => '',
                'checked_at' => '',
                //固定商品属性
                'exw_days' => '',
                'min_pack_naked_qty' => '',
                'nude_cargo_unit' => '',
                'min_pack_unit' => '',
                'min_order_qty' => '',
                'purchase_price' => '',
                'purchase_price_cur_bn' => '',
                'nude_cargo_l_mm' => '',
                //固定物流属性
                'nude_cargo_w_mm' => '',
                'nude_cargo_h_mm' => '',
                'min_pack_l_mm' => '',
                'min_pack_w_mm' => '',
                'min_pack_h_mm' => '',
                'net_weight_kg' => '',
                'gross_weight_kg' => '',
                'compose_require_pack' => '',
                'pack_type' => '',
                //固定申报要素属性
                'name_customs' => '',
                'hs_code' => '',
                'tx_unit' => '',
                'tax_rebates_pct' => '',
                'regulatory_conds' => '',
                'commodity_ori_place' => '',
                'attrs' => [
                    'spec_attrs' => [
                        0 => [
                            'attr_name' => '',
                            'attr_value' => '',
                            'value_unit' => '',
                            'spec_flag' => 'Y',
                            'attr_group' => '',
                            'attr_no' => '',
                            'attr_value_type' => '',
                            'goods_flag' => 'N',
                            'logi_flag' => 'N',
                            'hs_flag' => 'N',
                            'required_flag' => '',
                            'search_flag' => '',
                            'sort_order' => '',
                            'status' => '',
                        ],
                        1 => [
                        ],
                    ],
                    'ex_goods_attrs' => [
                        0 => [
                            'attr_name' => '',
                            'attr_value' => '',
                            'value_unit' => '',
                            'goods_flag' => 'Y',
                        ],
                    ],
                    'ex_hs_attrs' => [
                        0 => [
                            'attr_name' => '',
                            'attr_value' => '',
                            'value_unit' => '',
                            'hs_flag' => 'Y',
                        ]
                    ],
                    'other_attrs' => [
                        0 => [
                            'attr_name' => '',
                            'attr_value' => '',
                            'value_unit' => '',
                        ]
                    ],
                ]
            ],
            "zh" => [
            ],
            "es" => [
            ],
            "ru" => [
            ],
            "attachs" => [
                0 => [
                    'supplier_id' => '',
                    'attach_type' => '',
                    'attach_name' => '',
                    'attach_url' => '',
                    'sort_order' => '',
                    'status' => '',
                    'created_by' => '',
                    'created_at' => '',
                    'updated_by' => '',
                    'updated_at' => '',
                    'checked_by' => '',
                    'checked_at' => '',
                ],
                1 => [
                ],
            ],
            'supplier_cost' => [
                'supplier_id' => '',
                'price' => '',
                'price_unit' => '',
                'price_cur_bn' => '',
                'min_purchase_qty' => '',
                'pricing_date' => '',
                'price_validity' => '',
            ]
        ];
    }

    /**
     * 导出模板
     */
    public function exportTempAction() {
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->exportTemp();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品导出
     */
    public function exportAction() {
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->export($this->put_data);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品导出csv
     */
    public function exportCsvAction() {
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->exportCsv($this->put_data);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 导入
     */
    public function importAction() {
        if (empty($this->put_data['spu']) || empty($this->put_data['xls']) || !in_array($this->put_data['lang'], array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }
        $process = isset($this->put_data['process']) ? 1 : '';
        $filename = $this->getPut('name');
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->import($this->put_data['spu'], $this->put_data['xls'], $this->put_data['lang'], $process, $filename);
        if ($localDir) {
            if (is_array($localDir) && isset($localDir['success']) && $localDir['success'] == 0) {
                jsonReturn($localDir, ErrorMsg::SUCCESS, '导入失败');
            }
            $message = '成功' . $localDir['success'] . '条';
            if (isset($localDir['faild']) && $localDir['faild'] > 0) {
                $message = $message . ',失败' . $localDir['faild'] . '条。';
            }
            jsonReturn($localDir, ErrorMsg::SUCCESS, $message);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * zip导入
     * @param xls zip文件fastdfs地址
     */
    public function zipImportAction() {
        if (empty($this->put_data['xls'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $goodsModel = new GoodsModel();
        $result = $goodsModel->zipImport2($this->put_data['xls']);
        if ($result !== false) {
            $error = '';
            if (!empty($result['failds'])) {
                foreach ($result['failds'] as $e) {
                    $error .= '[' . $e['item'] . ']失败：' . $e['hint'] . ';';
                }
            }
            $result['failds'] = $error;
            //$str = '成功导入'.$result['succes_lang'].'条，spu'.$result['sucess'].'个；'.$error;
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }


    /**
     * 到期提醒模板导出
     */
    public function expireTempAction(){
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->expireTemp();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }


    /**
     * 到期提醒导出
     */
    public function expireExportAction(){
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->expireExport($this->put_data);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 到期提醒导入
     */
    public function expireImportAction(){
        if (empty($this->put_data['xls'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $goodsModel = new GoodsModel();
        $result = $goodsModel->expireImport($this->put_data['xls']);
        if($result){
            jsonReturn($result, ErrorMsg::SUCCESS, '成功操作'.(isset($result['success']) ? $result['success'] : 0).'条，失败'.(isset($result['faild']) ? $result['faild'] : 0).'条');
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }


    /********************************************
     * 临时导出
     */
    public function exportallAction() {
        $goodsModel = new GoodsModel();
        $localDir = $goodsModel->exportAll($this->put_data);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
}
