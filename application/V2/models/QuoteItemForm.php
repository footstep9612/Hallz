<?php

/**
 * @desc 报价单产品线平行表
 * @file Class QuoteItemFormModel
 * @author 买买提
 */
class QuoteItemFormModel extends PublicModel{
    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_rfq';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'quote_item_form';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 选择报价
     * @param $where
     *
     * @return mixed
     */
    public function getList($where){

        $field = 'id,created_by,brand,goods_desc,net_weight_kg,gross_weight_kg,package_size,package_mode,delivery_days,goods_source,stock_loc,status,supplier_id,contact_first_name,contact_last_name,contact_phone,purchase_unit_price,purchase_price_cur_bn,period_of_validity,reason_for_no_quote';
        //按价格由低到高显示
        return $this->where($where)->field($field)->order('purchase_unit_price ASC')->select();
    }


    /**
     * 获取报价单项对应的sku列表
     * @param array $condition
     *
     * @return mixed
     */
    public function getSkuList(array $condition,$uid){

        $where['a.quote_bizline_id'] = $condition['quote_bizline_id'];

        $where2 = "(a.updated_by=".$uid.") OR (a.status = 'NOT_QUOTED')";

        $field = 'a.id,a.quote_bizline_id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.updated_by,c.bizline_id';

        $data = $this->alias('a')
                    ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
                    ->join('erui2_rfq.quote_bizline c ON a.quote_bizline_id = c.id')
                    ->field($field)
                    ->where($where)
                    ->where($where2)
                    ->order('a.updated_by DESC')
                    ->select();
        //p($data);
        return $data;
    }

    /**
     * 统计sku总数
     * @param array $condition
     *
     * @return int
     */
    public function getSkuListCount(array $condition,$uid){

        $where['a.quote_bizline_id'] = $condition['quote_bizline_id'];
        $where2 = "(a.updated_by=".$uid.") OR (a.status = 'NOT_QUOTED')";

        $field = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.created_by,c.bizline_id';

        $count = $this->alias('a')
            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
            ->join('erui2_rfq.quote_bizline c ON a.quote_bizline_id = c.id')
            ->field($field)
            ->where($where)
            ->where($where2)
            ->group('a.sku')
            ->count('a.id');

        return $count > 0 ? $count : 0;
    }
}