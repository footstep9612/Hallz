<?php
/**
 * @desc 报价单明细附件模型
 * @author liujf 2017-06-17
 */
class QuoteItemAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item_attach';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition = array()) {
    	$where = array();
    	
    	if (!empty($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];
        }
    	
    	if (!empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
    	
    	return $where;
    }
    
	/**
     * @desc 获取记录总数
 	 * @author liujf 2017-06-27
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);
    	
    	$count = $this->where($where)->count('id');
    	
    	return $count > 0 ? $count : 0;
    }

	/**
     * @desc 获取报价单明细附件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return array
     */
    public function getAttachList($condition = array()) {
    	
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->select();
    	} else {
    		return $this->where($where)->select();
    	}
    }

}
