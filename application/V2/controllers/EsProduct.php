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
class EsProductController extends PublicController {

    protected $index = 'erui2_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

    //put your code here
    public function init() {

        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        if (!method_exists($this, $this->getRequest()->getActionName() . 'Action')) {
            $this->setCode(MSG::MSG_ERROR_ACTION);
            $this->jsonReturn();
        }
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
        $lang = $this->getPut('lang', 'en');
        $this->setLang($lang);
        if (!empty($jsondata["token"])) {
            $token = $jsondata["token"];
        }
        if (!empty($token)) {
            try {
                $tokeninfo = JwtInfo($token); //解析token
                $userinfo = json_decode(redisGet('user_info_' . $tokeninfo['id']), true);
                if (empty($userinfo)) {
                    $this->put_data['source'] = 'ERUI';
                } else {
                    $this->user = $userinfo;
                }
            } catch (Exception $e) {
                $this->put_data['source'] = 'ERUI';
            }
        } else {
            $this->put_data['source'] = 'ERUI';
        }
        $this->es = new ESClient();
    }

    /*
     * 获取列表
     */

    public function listAction() {
        $model = new EsProductModel();
        $ret = $model->getProducts($this->put_data, null, $this->getLang());
        if ($ret) {
            $data = $ret[0];

            $list = $this->_getdata($data);
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {
                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            if (isset($this->put_data['sku_count']) && $this->put_data['sku_count'] == 'Y') {
                $send['sku_count'] = $data['aggregations']['sku_num']['value'];
            }
            $send['data'] = $list;
            $this->_update_keywords();
            $this->setCode(MSG::MSG_SUCCESS);
            $send['code'] = $this->getCode();
            $send['message'] = $this->getMessage();
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 处理ES 数据
     */

    private function _getdata($data) {

        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = null;
            }
            $list[$key]['id'] = $item['_id'];
            $show_cats = json_decode($item["_source"]["show_cats"], true);
            if ($show_cats) {
                rsort($show_cats);
            }
            $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
            $list[$key]['skus'] = json_decode($list[$key]['skus'], true);
        }
        return $list;
    }

    /* 获取分类列表
     * @
     */

    private function _getcatlist($material_cat_nos, $material_cats) {
        ksort($material_cat_nos);
        $condition = $this->put_data;
        $condition['token'] = $condition['show_cat_no'] = null;
        unset($condition['token'], $condition['show_cat_no']);
        $catno_key = 'ShowCats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang() . http_build_query($condition));
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
                $condition['show_cat_no'] = $item['cat_no'];
                $item['count'] = $model->getcount($condition, $this->getLang());
                $new_showcats3[$key] = $item;
            }
            redisSet($catno_key, json_encode($new_showcats3), 86400);
            return $new_showcats3;
        }
        return $catlist;
    }

    /*
     * 更新关键词表
     */

    private function _update_keywords() {
        if ($this->put_data['keyword']) {
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