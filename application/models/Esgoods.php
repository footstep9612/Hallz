<?php/* * To change this license header, choose License Headers in Project Properties. * To change this template file, choose Tools | Templates * and open the template in the editor. *//** * Description of Esgoods * * @author zhongyg */class EsgoodsModel extends PublicModel {  //put your code here  protected $tableName = 'goods';  protected $dbName = 'erui_goods'; //数据库名称  public function __construct($str = '') {    parent::__construct($str = '');  }  /* 条件组合   * @param mix $condition // 搜索条件   */  private function getCondition($condition) {    $body = [];    $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';    if (isset($condition['sku']) && $condition['sku']) {      $sku = $condition['sku'];      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['sku' => $sku]];    }    if (isset($condition['spu']) && $condition['spu']) {      $spu = $condition['spu'];      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['spu' => $spu]];    }    if (isset($condition['skus']) && $condition['skus']) {      $skus = $condition['skus'];      $skus_arr = [];      foreach ($skus as $sku) {        $skus_arr[] = [ESClient::MATCH_PHRASE => ['sku' => $sku]];      }      $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $skus_arr]];    }    if (isset($condition['show_cat_no']) && $condition['show_cat_no']) {      $show_cat_no = $condition['show_cat_no'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['show_cats' => $show_cat_no]];    }    if (isset($condition['name']) && $condition['name']) {      $name = $condition['name'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['name' => ['query' => $name,                  "minimum_should_match" => "75%"]]];    }    if (isset($condition['real_name']) && $condition['real_name']) {      $real_name = $condition['real_name'];      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['name' => $real_name]];    }    if (isset($condition['supplier_name']) && $condition['supplier_name']) {      $supplier_name = $condition['supplier_name'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['supplier_name' => $supplier_name]];    }    if (isset($condition['brand']) && $condition['brand']) {      $brand = $condition['brand'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['brand' => $brand]];    }    if (isset($condition['source']) && $condition['source']) {      $source = $condition['source'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['source' => $source]];    }    if (isset($condition['cat_name']) && $condition['cat_name']) {      $cat_name = $condition['cat_name'];      $body['query']['bool']['must'][] = [ESClient::MATCH => ['show_cats' => $cat_name]];    }    if (isset($condition['created_at_start']) && isset($condition['created_at_end']) && $condition['created_at_start'] && $condition['created_at_end']) {      $created_at_start = $condition['created_at_start'];      $created_at_end = $condition['created_at_end'];      $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>              ['gte' => $created_at_start,                  'gle' => $created_at_end,              ]          ]      ];    } elseif (isset($condition['created_at_start']) && $condition['created_at_start']) {      $created_at_start = $condition['created_at_start'];      $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>              ['gte' => $created_at_start,              ]          ]      ];    } elseif (isset($condition['created_at_end']) && $condition['created_at_end']) {      $created_at_end = $condition['created_at_end'];      $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>              ['gle' => $created_at_end,              ]          ]      ];    }    if (isset($condition['status'])) {      $status = $condition['status'];      if (!in_array($status, ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {        $status = 'VALID';      }      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => $status]];    } else {      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => 'VALID']];    }    if (isset($condition['model']) && $condition['model']) {      $model = $condition['model'];      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['model' => $model]];    }    if (isset($condition['created_by']) && $condition['created_by']) {      $created_by = $condition['created_by'];      $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['created_by' => $created_by]];    }    if (isset($condition['show_name']) && $condition['show_name']) {      $show_name = $condition['show_name'];      $body['query'] = ['multi_match' => [              "query" => $show_name,              "type" => "most_fields",              "fields" => ["show_name", "attrs", 'specs']      ]];    }    return $body;  }  /* 通过搜索条件获取数据列表   * @param mix $condition // 搜索条件   * @param string $lang // 语言   * @return mix     */  public function getgoods($condition, $_source = null, $lang = 'en') {    try {      if (!$_source) {        $_source = ['sku', 'spu', 'name', 'show_name', 'model'            , 'purchase_price1', 'purchase_price2', 'attachs', 'package_quantity', 'exw_day',            'purchase_price_cur', 'purchase_unit', 'pricing_flag', 'show_cats',            'meterial_cat', 'brand', 'supplier_name', 'warranty'];      }      $body = $this->getCondition($condition);      $pagesize = 10;      $current_no = 1;      if (isset($condition['current_no'])) {        $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;      }      if (isset($condition['pagesize'])) {        $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;      }      $from = ($current_no - 1) * $pagesize;      $es = new ESClient();      return [$es->setbody($body)                  ->setfields($_source)                  ->setsort('sort_order', 'desc')                  ->setsort('_score', 'desc')                  ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $from, $pagesize];    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过搜索条件获取数据列表   * @param mix $condition // 搜索条件   * @param string $lang // 语言   * @return mix     */  public function getshow_catlist($condition, $lang = 'en') {    try {      $body = $this->getCondition($condition);      $from = ($current_no - 1) * $pagesize;      $es = new ESClient();      return $es->setbody($body)                      ->setaggs('show_cats', 'chowcat', 'terms')                      ->search($this->dbName, $this->tableName . '_' . $lang, $from);    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过ES 获取数据列表   * @param string $name // 商品名称 属性名称或属性值   * @param string $show_cat_no // 展示分类编码   * @return mix     */  public function getGoodsbysku($sku, $lang = 'en') {    try {      $es = new ESClient();      $es->setmust(['sku' => $sku], ESClient::TERM);      return $es->search($this->dbName, $this->tableName . '_' . $lang);    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过ES 获取数据列表   * @param string $name // 商品名称 属性名称或属性值   * @param string $show_cat_no // 展示分类编码   * @return mix     */  public function getGoodsbyspu($sku, $lang = 'en') {    try {      $es = new ESClient();      $es->setmust(['sku' => $sku], ESClient::TERM);      return $es->search($this->dbName, $this->tableName . '_' . $lang);    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过SKU获取数据商品属性列表   * @param mix $skus // 商品SKU编码数组   * @param string $lang // 语言   * @return mix     */  public function getgoods_attrbyskus($skus, $lang = 'en') {    try {      $product_attrs = $this->table('erui_goods.t_goods_attr')              ->field('*')              ->where(['sku' => ['in', $skus], 'spec_flag' => 'N', 'lang' => $lang, 'status' => 'VALID'])              ->select();      foreach ($product_attrs as $item) {        $ret[$item['sku']][] = $item;      }      return $ret;    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过SKU获取数据商品文件列表   * @param mix $skus // 商品SKU编码数组   * @param string $lang // 语言   * @return mix     */  public function getgoods_attachsbyskus($skus, $lang = 'en') {    try {      $goods_attachs = $this->table('erui_goods.t_goods_attach')              ->field('id,attach_type,attach_url,attach_name,attach_url,sku')              ->where(['sku' => ['in', $skus],                  'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],                  'status' => 'VALID'])              ->select();      $ret = [];      if ($goods_attachs) {        foreach ($goods_attachs as $item) {          $data['attach_name'] = $item['attach_name'];          $data['attach_url'] = $item['attach_url'];          $ret[$item['sku']][$item['attach_type']][] = $data;        }      }      return $ret;    } catch (Exception $ex) {      print_r($ex->getMessage());      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过SKU获取数据商品规格列表   * @param mix $skus // 商品SKU编码数组   * @param string $lang // 语言   * @return mix     */  public function getgoods_specsbyskus($skus, $lang = 'en') {    try {      $product_attrs = $this->table('erui_goods.t_goods_attr')              ->field('sku,attr_name,attr_value,attr_no')              ->where(['sku' => ['in', $skus],                  'lang' => $lang,                  'spec_flag' => 'Y',                  'status' => 'VALID'              ])              ->select();      $ret = [];      foreach ($product_attrs as $item) {        $sku = $item['sku'];        unset($item['sku']);        $ret[$sku][] = $item;      }      return $ret;    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过SKU获取数据商品产品属性分类等信息列表   * @param mix $skus // 商品SKU编码数组   * @param string $lang // 语言   * @return mix     */  public function getproductattrsbyspus($skus, $lang = 'en') {    try {      $goodss = $this->where(['sku' => ['in', $skus], 'lang' => $lang])              ->select();      $spus = $skus = [];      foreach ($goodss as $item) {        $skus[] = $item['sku'];        $spus[] = $item['spu'];      }      $spus = array_unique($spus);      $skus = array_unique($skus);      $espoducmodel = new EsproductModel();      $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);      $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);      $specs = $this->getgoods_specsbyskus($skus, $lang);      $ret = [];      foreach ($goodss as $item) {        $id = $item['id'];        $body = $item;        $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];        $body['show_cat'] = $productattrs[$item['spu']]['show_cats'];        $body['specs'] = $specs[$item['sku']];        $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);        foreach ($goods_attrs[$item['sku']] as $attr) {          array_push($product_attrs, $attr);        }        $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);        // $body['specs'] = json_encode($specs, JSON_UNESCAPED_UNICODE);        $ret[$id] = $body;      }      return $ret;    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return [];    }  }  /* 通过批量导入商品信息到ES   * @param string $lang // 语言   * @return mix     */  public function importgoodss($lang = 'en') {    try {      $count = $this->where(['lang' => $lang])->count('id');      for ($i = 0; $i < $count; $i += 100) {        if ($i > $count) {          $i = $count;        }        echo $i, PHP_EOL;        ob_flush();        flush();        $goods = $this->where(['lang' => $lang])                        ->limit($i, 100)->select();        $spus = $skus = [];        if ($goods) {          foreach ($goods as $item) {            $skus[] = $item['sku'];            $spus[] = $item['spu'];          }        } else {          return false;        }        $spus = array_unique($spus);        $skus = array_unique($skus);        $espoducmodel = new EsproductModel();        $es = new ESClient();        $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);        $attachs = $this->getgoods_attachsbyskus($skus, $lang);        $goods_attrs = $this->getgoods_attrbyskus($skus, $lang);        $specs = $this->getgoods_specsbyskus($skus, $lang);        foreach ($goods as $item) {          $id = $item['sku'];          $body = $item;          $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];          $body['show_cats'] = $productattrs[$item['spu']]['show_cats'];          $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);          if (isset($specs[$item['sku']])) {            $body['specs'] = json_encode($specs[$item['sku']], 256);          } else {            $body['specs'] = '[]';          }          if (isset($attachs[$item['sku']])) {            $body['attachs'] = json_encode($attachs[$item['sku']], 256);          } else {            $body['attachs'] = '[]';          }          if (isset($goods_attrs[$item['sku']]) && $product_attrs) {            foreach ($goods_attrs[$item['sku']] as $attr) {              array_push($product_attrs, $attr);            }          } elseif (isset($goods_attrs[$item['sku']])) {            $product_attrs = $goods_attrs[$item['sku']];          }          $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);          $body['supplier_name'] = $productattrs[$item['spu']]['supplier_name'];          $body['brand'] = $productattrs[$item['spu']]['brand'];          $body['source'] = $productattrs[$item['spu']]['source'];          if ($body['source'] == 'ERUI') {            $body['sort_order'] = 100;          } else {            $body['sort_order'] = 1;          }          $body['supplier_id'] = $productattrs[$item['spu']]['supplier_id'];          $body['meterial_cat_no'] = $productattrs[$item['spu']]['meterial_cat_no'];          $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);          print_r($flag);          ob_flush();          flush();        }      }    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return false;    }  }  public function getInsertCodition($condition, $lang = 'en') {    $data = [];    if (isset($condition['id'])) {      $data['id'] = $condition['id'];    }    $data['lang'] = $lang;    $meterial_cat_no = null;    if (isset($condition['spu'])) {      $data['spu'] = $condition['spu'];      $mpmodel = new Materialcatproduct();      $meterial_cat_noinfo = $mpmodel->getcatnobyspu($data['spu']);      $meterial_cat_no = $meterial_cat_noinfo['cat_no'];    } else {      $data['spu'] = '';    }    if (isset($condition['sku'])) {      $data['sku'] = $condition['sku'];    } else {      $data['sku'] = '';    }    if ($meterial_cat_no) {      $material_cat_no = $data['meterial_cat_no'] = $condition['meterial_cat_no'];      $mcatmodel = new MaterialcatModel();      $data['meterial_cat'] = json_encode($mcatmodel->getinfo($material_cat_no, $lang), 256);      $smmodel = new ShowmaterialcatModel();      $show_cat_nos = $smmodel->getshowcatnosBymatcatno($material_cat_no, $lang);      $es_product_model = new EsproductModel();      $scats = $es_product_model->getshow_cats($show_cat_nos, $lang);      $data['show_cats'] = json_encode($scats[$material_cat_no], 256);    } else {      $data['meterial_cat_no'] = '';      $data['meterial_cat'] = json_encode(new \stdClass());      $data['show_cats'] = json_encode([]);    }    if (isset($condition['qrcode'])) {      $data['qrcode'] = $condition['qrcode'];    } else {      $data['qrcode'] = '';    }    if (isset($condition['name'])) {      $data['name'] = $condition['name'];    } else {      $data['name'] = '';    }    if (isset($condition['show_name'])) {      $data['show_name'] = $condition['show_name'];    } else {      $data['show_name'] = '';    }    if (isset($condition['model'])) {      $data['model'] = $condition['model'];    } else {      $data['model'] = '';    }    if (isset($condition['description'])) {      $data['description'] = $condition['description'];    } else {      $data['description'] = '';    }    if (isset($condition['package_quantity'])) {      $data['package_quantity'] = $condition['package_quantity'];    } else {      $data['package_quantity'] = '';    }    if (isset($condition['exw_day'])) {      $data['exw_day'] = $condition['exw_day'];    } else {      $data['exw_day'] = '';    }    if (isset($condition['purchase_price1'])) {      $data['purchase_price1'] = $condition['purchase_price1'];    } else {      $data['purchase_price1'] = '';    }    if (isset($condition['purchase_price2'])) {      $data['purchase_price2'] = $condition['purchase_price2'];    } else {      $data['purchase_price2'] = '';    }    if (isset($condition['purchase_price_cur'])) {      $data['purchase_price_cur'] = $condition['purchase_price_cur'];    } else {      $data['purchase_price_cur'] = '';    }    if (isset($condition['purchase_unit'])) {      $data['purchase_unit'] = $condition['purchase_unit'];    } else {      $data['purchase_unit'] = '';    }    if (isset($condition['pricing_flag'])) {      $data['pricing_flag'] = $condition['pricing_flag'] == 'Y' ? 'Y' : 'N';    } else {      $data['pricing_flag'] = 'N';    }    if (isset($condition['status']) && in_array($condition['status'], ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {      $data['status'] = strtoupper($condition['status']);    } else {      $data['status'] = 'CHECKING';    }    if (isset($condition['created_by'])) {      $data['created_by'] = $condition['created_by'];    } else {      $data['created_by'] = '';    }    if (isset($condition['created_at'])) {      $data['created_at'] = $condition['created_at'];    } else {      $data['created_at'] = '';    }    if (isset($condition['supplier_id']) && $condition['supplier_id']) {      $data['supplier_id'] = $condition['supplier_id'];    } else {      $data['supplier_id'] = '';    }    if (isset($condition['supplier_name']) && $condition['supplier_name']) {      $data['supplier_name'] = $condition['supplier_name'];    } else {      $data['supplier_name'] = '';    }    if (isset($condition['brand'])) {      $data['brand'] = $condition['brand'];    } else {      $data['brand'] = '';    }    if (isset($condition['source'])) {      $data['source'] = $condition['source'];    } else {      $data['source'] = '';    }    return $data;  }  /*   * 添加产品到Es   * @param string $lang // 语言 zh en ru es    * @return mix     */  public function create_data($data, $lang = 'en') {    try {      $es = new ESClient();      if (!isset($data['sku']) || empty($data['sku'])) {        return false;      }      $body = $this->getInsertCodition($data);      $id = $sku;      $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);      if ($flag['_shards']['successful'] !== 1) {        LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);        return true;      } else {        return false;      }    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return false;    }  }  /*   * 添加产品到Es   * @param string $lang // 语言 zh en ru es    * @return mix     */  public function update_data($data, $sku, $lang = 'en') {    try {      $es = new ESClient();      if (empty($sku)) {        return false;      } else {        $data['sku'] = $sku;      }      $body = $this->getInsertCodition($data);      $id = $sku;      $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);      if ($flag['_shards']['successful'] !== 1) {        LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);        return true;      } else {        return false;      }    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return false;    }  }  /* 上架   *    */  public function changestatus($sku, $lang = 'en') {    try {      $es = new ESClient();      if (empty($sku)) {        return false;      }      if (in_array(strtoupper($status), ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {        $data['status'] = strtoupper($status);      } else {        $data['status'] = 'CHECKING';      }      $id = $sku;      $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);      if ($flag['_shards']['successful'] !== 1) {        LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);        return true;      } else {        return false;      }    } catch (Exception $ex) {      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);      LOG::write($ex->getMessage(), LOG::ERR);      return false;    }  }  /* 更新属性规格   *    */  public function Update_Attrs($sku, $lang = 'en', $product_attrs = [], $product_specs = []) {    $es = new ESClient();    if (empty($sku)) {      return false;    }    $goodsmodel = new GoodsModel();    $goodsinfo = $goodsmodel->getInfo($sku, $lang);    $goods_attrs = $this->getgoods_attrbyskus([$sku], $lang);    $specs = $this->getgoods_specsbyskus([$sku], $lang);    $EsproductModel = new EsproductModel();    if (empty($product_attrs)) {      $product_attrs = $EsproductModel->getgoods_specsbyspus([$goodsinfo['spu']], $lang);    }    if (empty($product_specs)) {      $product_specs = $EsproductModel->getproductattrsbyspus([$goodsinfo['spu']], $lang);    }    $goods_attrs = $goods_attrs[$sku];    $specs = $specs[$sku];    if (isset($specs[$item['sku']])) {      $body['specs'] = json_encode($specs[$item['sku']], 256);    } else {      $body['specs'] = '[]';    }    if (isset($product_attrs[$goodsinfo['spu']]) && $goods_attrs) {      foreach ($product_attrs[$goodsinfo['spu']] as $attr) {        array_push($goods_attrs, $attr);      }    } elseif (isset($product_attrs[$goodsinfo['spu']])) {      $goods_attrs = $product_attrs[$goodsinfo['spu']];    }    if (isset($product_specs[$goodsinfo['spu']]) && $specs) {      foreach ($product_specs[$goodsinfo['spu']] as $spec) {        array_push($specs, $spec);      }    } elseif (isset($product_specs[$goodsinfo['spu']])) {      $specs = $product_specs[$goodsinfo['spu']];    }    $data['attrs'] = json_encode($goods_attrs, JSON_UNESCAPED_UNICODE);    $id = $sku;    $data['specs'] = json_encode($specs, 256);    $type = $this->tableName . '_' . $lang;    $es->update_document($this->dbName, $type, $data, $id);    return true;  }  /* 新增ES   *    */  public function Update_Attachs($sku, $lang = 'en') {    $es = new ESClient();    if (empty($spu)) {      return false;    }    $attachs = $this->getgoods_attachsbyskus([$sku], $lang);    if (isset($attachs[$sku])) {      $data['attachs'] = json_encode($attachs[$sku], 256);    } else {      $data['attachs'] = '[]';    }    $id = $spu;    $type = $this->tableName . '_' . $lang;    $es->update_document($this->dbName, $type, $data, $id);    return true;  }  public function delete_data($sku, $lang = 'en') {    $es = new ESClient();    if (empty($sku)) {      return false;    }    $data['status'] = self::STATUS_DELETED;    $id = $sku;    $type = $this->tableName . '_' . $lang;    $flag = $es->update_document($this->dbName, $type, $data, $id);    return true;  }}