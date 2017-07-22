<?php
/**
 * Name: ProductShelves
 * Desc: 产品线管理
 * User: zhangyuliag
 * Date: 2017/7/21
 * Time: 14:28
 */
class ProductshelvesController extends PublicController {

    public function __init() {
        parent::__init();
    }

    //产品上架
    public function upShelvesAction(){
        $showcatproduct = new ShowCatProductModel();
        $showcatgoods = new ShowCatGoodsModel();
        $product = new ProductModel();
        $goods = new GoodsModel();
        $createcondition = $this->put_data;

        $showcatproduct->startTrans();
        $results = $showcatproduct->addData($createcondition);

        if($results['code']==1){
            $where['lang'] = $createcondition['lang'];
            $where['spu'] = $createcondition['spu'];
            $where['status'] = 'VALID';
            $goodslist = $goods->field('sku')->where($where)->select();
            $condition = $where;
            $condition['skus'] = $goodslist;
            $condition['show_cat'] = $createcondition['show_cat'];
            $condition['show_name'] = $createcondition['show_name'];
            $condition['created_by'] = $createcondition['created_by'];

            $goodsres = $showcatgoods->addData($condition);

            if($goodsres['code']==1){
                $productstatus = $product->where($where)->save(['shelves_status'=>'VALID']);
                $goodsstatus = $goods->where($where)->save(['shelves_status'=>'VALID']);
                if($productstatus && $goodsstatus){
                    $showcatproduct->commit();
                }else{
                    $showcatproduct->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '上架失败!';
                }
            }else{
                $showcatproduct->rollback();
                $results['code'] = '-101';
                $results['message'] = '上架失败!';
            }
        }else{
            $showcatproduct->rollback();
        }

        $this->jsonReturn($results);
    }

    //产品下架
    public function downShelvesAction(){
        $showcatproduct = new ShowCatProductModel();
        $showcatgoods = new ShowCatGoodsModel();
        $product = new ProductModel();
        $goods = new GoodsModel();
        $createcondition = $this->put_data;

        $showcatproduct->startTrans();
        $results = $showcatproduct->deleteData($createcondition);
        if($results['code']==1){
            $where['lang'] = $createcondition['lang'];
            $where['spu'] = $createcondition['spu'];
            $where['status'] = 'VALID';

            $goodsres = $showcatgoods->deleteData($createcondition);

            if($goodsres['code']==1){
                $productstatus = $product->where($where)->save(['shelves_status'=>'INVALID']);
                $goodsstatus = $goods->where($where)->save(['shelves_status'=>'INVALID']);
                if($productstatus && $goodsstatus){
                    $showcatproduct->commit();
                }else{
                    $showcatproduct->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '下架失败!';
                }
            }else{
                $showcatproduct->rollback();
                $results['code'] = '-101';
                $results['message'] = '下架失败!';
            }
        }else{
            $showcatproduct->rollback();
        }

        $this->jsonReturn($results);
    }

}