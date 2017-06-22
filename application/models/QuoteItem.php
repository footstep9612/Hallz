<?php
/**
 * @desc 报价单明细模型
 * @author liujf 2017-06-17
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';
    
    public function __construct() {
        parent::__construct();
    }

	/**
     * @desc 获取报价单项目列表
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getItemList($condition) {
    	if(isset($condition['inquiry_no'])) {
    		$where['inquiry_no'] = $condition['inquiry_no'];
    	}
    	
        return $this->where($where)->select();
    }
    
	/**
     * 获取sku询价单列表
     * @param $fields   array 筛选字段
     * @return array
     */
    public function get_quote_item_list($fields)
    {
        return $this->field($fields)->select();
    }

}
