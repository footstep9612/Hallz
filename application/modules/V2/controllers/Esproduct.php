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
class EsproductController extends PublicController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

    //put your code here
    public function init() {
        parent::init();
        $this->es = new ESClient();
    }

    public function listAction() {
        $model = new EsproductModel();
        $_source = ['id', 'skus', 'meterial_cat_no', 'spu', 'name', 'show_name', 'attrs', 'specs'
            , 'profile', 'supplier_name', 'source', 'supplier_id', 'attachs', 'brand',
            'recommend_flag', 'supply_capabilitys', 'tech_paras', 'meterial_cat',
            'brand', 'supplier_name', 'created_by', 'created_at', 'updated_by', 'updated_at', 'status',
            'shelves_by', 'shelves_at', 'shelves_status', 'checked_by', 'checked_at'];
        $ret = $model->getproducts($this->put_data, $_source, $this->getLang());
        if ($ret) {
            $data = $ret[0];
            $list = $this->getdata($data);
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {
                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            if (isset($this->put_data['sku_count']) && $this->put_data['sku_count'] == 'Y') {
                $es_goods_model = new EsgoodsModel();
                $send['sku_count'] = $es_goods_model->getgoodscount($this->put_data);
            }
            if (!isset($this->put_data['show_cat_no']) || !$this->put_data['show_cat_no']) {
                $material_cat_nos = [];
                foreach ($data['aggregations']['meterial_cat_no']['buckets'] as $item) {
                    $material_cats[$item['key']] = $item['doc_count'];
                    $material_cat_nos[] = $item['key'];
                }
            } else {
                $condition = $this->put_data;
                unset($condition['show_cat_no']);
                $ret1 = $model->getproducts($condition, null, $this->getLang());
                if ($ret1) {
                    $material_cat_nos = [];
                    foreach ($ret1[0]['aggregations']['meterial_cat_no']['buckets'] as $item) {
                        $material_cats[$item['key']] = $item['doc_count'];
                        $material_cat_nos[] = $item['key'];
                    }
                }
            }
            $catlist = $this->getcatlist($material_cat_nos, $material_cats);
            $send['catlist'] = $catlist;
            $send['data'] = $list;
            $this->update_keywords();
            $this->setCode(MSG::MSG_SUCCESS);
            $send['code'] = $this->getCode();
            $send['message'] = $this->getMessage();
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    private function getdata($data) {

        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = null;
            }
            $show_cats = [];
            if (isset($item["_source"]["show_cats"])) {
                $show_cats = json_decode($item["_source"]["show_cats"], true);
            }
            if ($show_cats) {
                rsort($show_cats);
            }
            $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            if (isset($list[$key]['specs']) && $list[$key]['specs']) {
                $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            }

            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
            $list[$key]['skus'] = json_decode($list[$key]['skus'], true);
            $list[$key]['sku_num'] = count($list[$key]['skus']);
        }
        return $list;
    }

    private function getcatlist($material_cat_nos, $material_cats) {
        ksort($material_cat_nos);
        $catno_key = 'ShowCats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang() . md5(json_encode($this->put_data)));
        $catlist = json_decode(redisGet($catno_key), true);
        if (!$catlist) {
            $matshowcatmodel = new ShowmaterialcatModel();
            $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang());
            $new_showcats3 = [];
            foreach ($showcats as $showcat) {
                $material_cat_no = $showcat['material_cat_no'];
                unset($showcat['material_cat_no']);
                $new_showcats3[$showcat['cat_no']] = $showcat;
                if (isset($material_cats[$material_cat_no])) {
                    $new_showcats3[$showcat['cat_no']]['count'] = $material_cats[$material_cat_no];
                }
            }
            rsort($new_showcats3);
            foreach ($new_showcats3 as $key => $item) {
                $model = new EsproductModel();
                $this->put_data['show_cat_no'] = $item['cat_no'];
                $item['count'] = $model->getcount($this->put_data, $this->getLang());
                $new_showcats3[$key] = $item;
            }
            redisSet($catno_key, json_encode($new_showcats3), 86400);
            return $new_showcats3;
        }
        return $catlist;
    }

    private function update_keywords() {
        if (isset($this->put_data['keyword']) && $this->put_data['keyword']) {
            $search = [];
            $search['keywords'] = $this->put_data['keyword'];
            if ($this->user['email']) {
                $search['user_email'] = $this->user['email'];
            } else {
                $search['user_email'] = '';
            }
            $search['search_time'] = date('Y-m-d H:i:s');
            $usersearchmodel = new BuyersearchhisModel();
            $condition = ['user_email' => $search['user_email'], 'keywords' => $search['keywords']];
            $row = $usersearchmodel->exist($condition);
            if ($row) {
                $search['search_count'] = intval($row['search_count']) + 1;
                $search['id'] = $row['id'];
                $usersearchmodel->update_data($search);
            } else {
                $search['search_count'] = 1;
                $usersearchmodel->add($search);
            }
        }
    }

}