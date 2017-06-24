<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 *
 * @author zhongyg
 */
class EsgoodsController extends PublicController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];

    //put your code here
    public function init() {


//        $espoductmodel = new EsgoodsModel();
//        $flag = $espoductmodel->getproductattrsbyspus(['01']);
//        echo '<pre>';
//        var_dump($flag);
//        die();
        $this->es = new ESClient();
        //  parent::init();
    }

    /*
     * goods 数据导入
     */

    public function importgoodsAction($lang = 'en') {
        try {
            $espoductmodel = new EsgoodsModel();
            $espoductmodel->importgoodss($lang);
            $this->setCode(1);
            $this->setMessage('成功!');
            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /*
     * product数据导入
     */

    public function importproductsAction($lang = 'en') {
        try {
            $espoductmodel = new EsProductModel();
            $espoductmodel->importproducts($lang);
            $this->setCode(1);
            $this->setMessage('成功!');

            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn([]);
        }
    }

    public function indexAction() {
//        $this->es->delete('index');
        // $this->es->delete($this->index);
        //$model = new EsgoodsModel();

        $body['mappings'] = [];

        foreach ($this->langs as $lang) {
            $body['mappings']['goods_' . $lang] = $this->goodsAction($lang);

            $body['mappings']['product_' . $lang] = $this->productAction($lang);
        }

        $this->es->create_index($this->index, $body);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn($data);
    }

    public function getGoodsAction() {

        $name = 'CONDOR';
        $model = new EsgoodsModel();
        $flag = $model->getGoodsbyname($name, 'S010102');

        echo '<pre>';
        var_dump($flag);
    }

    public function goodsAction($lang = 'en') {
      

        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'sku' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'qrcode' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'model' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'purchase_price1' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price2' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price_cur' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'purchase_unit' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'pricing_flag' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];


        return $body;
    }

    public function productAction($lang = 'en') {

        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'meterial_cat_no' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'skus' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'qrcode' => [
                    'type' => $type_string,
                    "index" => "no",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'keywords' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'exe_standard' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'app_scope' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'tech_paras' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'advantages' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'profile' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'supplier_id' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'supplier_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'brand' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 2
                ],
                'source' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'source_detail' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'recommend_flag' => [
                    'type' => $type_string,
                    'analyzer' => 'whitespace'
                ],
                'status' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'updated_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'updated_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'checked_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'checked_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];

        return $body;
        // $this->es->create_index($this->index,  $body);
    }

}
