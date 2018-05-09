<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/21
 * Time: 18:04
 */
class OrderGoodsModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_goods';
    protected $dbName = 'erui_order'; //数据库名称

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }


    /* 获取商品详情
     * @param int $order_id // 订单ID
     */

    public function getList($condition) {
        $where = $this->_getCondition($condition);
        $field = 'id,order_no,sku,lang,name,model,spec_attrs,price,buy_number';
        $field .= ',min_pack_naked_qty,nude_cargo_unit,min_pack_unit,thumb,buyer_id,symbol';
        return $this->field($field)
                     ->where($where)
                     ->order('id asc')
                     ->select();
    }

    private function _getCondition($condition) {
        $where = [];
        $this->_getValue($where, $condition, 'order_no');
        $where['deleted_flag'] = "N";

        return $where;
    }

    /* 获取订单数量
     */

    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /**
     * 数据字典
     * @var $data
     * @author link 2017-12-20
     */
    private $_field = [
        'order_no',    //订单编号
        'sku',    //订货号
        'lang',    //语言
        'name',    //商品名称
        'model',    //型号
        'spec_attrs',    //规格属性
        'price',    //购买单价
        'buy_number',    //购买数量
        'min_pack_naked_qty',    //最小包装内裸货商品数量
        'nude_cargo_unit',    //商品裸货单位
        'min_pack_unit',    //最小包装单位
        'thumb',    //商品图
        'buyer_id',    //采购商id
        'created_at',    //创建时间
    ];

    /**
     * 格式化数据
     * @var $data
     * @author link 2017-12-20
     */
    private function _getData($data){
        if(empty($data)){
            return [];
        }
        foreach($data as $key =>$value){
            if(!in_array($key,$this->_field)){
                unset($data[$key]);
            }
            if(empty($value)){
                $data[$key] = null;
            }
        }
        return $data;
    }

    /**
     * 添加
     * @var $data
     * @author link 2017-12-20
     */
    public function addInfo($data){
        if(!isset($data['order_id'])){
            jsonReturn('订单地址添加，orer_id不能为空');
        }
        try{
            $data = $this->_getData($data);
            $data['created_at'] = date('Y-m-d H:i:s',time());
            $result = $this->add($this->create($data));
            return $result ? $result : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【OrderAddressModel】 add:' . $e , Log::ERR);
            return false;
        }

    }

}