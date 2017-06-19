<?php
/**
 * sku
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:48
 */
class ProductController extends PublicController{
    private $lang;
    public function init(){
        //语言处理  默认en
        $lang = $this->getRequest()->getQuery("lang",'');
        $this->lang = empty($lang) ? (browser_lang() ? browser_lang() : 'en'): strtolower($lang);
        if(!in_array($this->lang,array('en','ru','es','zh'))){
            $this->lang = 'en';
        }

    }

    /**
     * SKU列表
     */
    public function listAction(){
        $page = $this->getRequest()->getQuery("current_num",1);
        $pagesize = $this->getRequest()->getQuery('pagesize',10);

        $show_cat_no = $this->getRequest()->getQuery('show_cat_no','');
        if($show_cat_no==''){
            jsonReturn(array('code'=>10000,'message'=>'分类编码不能为空'));
            exit;
        }

        $product = new ShowCatProductModel();
        $return = $product->getSkuByCat($show_cat_no,$this->lang,$page,$pagesize);
        if($return){
            $return['code']=0;
            $return['message'] = '成功';
            jsonReturn($return);
        }else{
            jsonReturn(array('code'=>400,'message'=>'失败'));
        }
        exit;
    }

    /**
     * SKU详情
     */
    public function infoAction(){
        $sku = $this->getRequest()->getQuery("sku",'');
        if($sku == ''){
            jsonReturn(array('code'=>10000,'message'=>'SKU编码不能为空'));
            exit;
        }

        $goodsModel = new GoodsModel();
        $resolt = $goodsModel->getInfo($sku,$this->lang);
        if($resolt){
            $data = array(
                'code' => 0,
                'message' => '成功',
                'data' => $resolt
            );
            jsonReturn($data);
        }else {
            jsonReturn(array('code' => 400, 'message' => '失败'));
        }
        exit;
    }
}