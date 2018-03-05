<?php
/*
 * @desc 供应商资质模型
 * 
 * @author liujf 
 * @time 2017-11-11
 */
class SupplierQualificationModel extends PublicModel {

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_qualification';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2017-11-11
     */
     public function getWhere($condition = []) {
         
         $where = [];
         
         if (!empty($condition['id'])) {
             $where['id'] = $condition['id'];
         }
         
         if (!empty($condition['supplier_id'])) {
             $where['supplier_id'] = $condition['supplier_id'];
         }
         
         return $where;
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int $count
     * @author liujf 
     * @time 2017-11-11
     */
    public function getCount($condition = []) {
        
    	$where = $this->getWhere($condition);
    	
    	$count = $this->where($where)->count('id');
    	
    	return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取列表
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2017-11-11
     */
    public function getList($condition = [], $field = '*') {
        
    	$where = $this->getWhere($condition);
    	
    	//$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	//$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	
    	return $this->field($field)
    	                   ->where($where)
    	                   //->page($currentPage, $pageSize)
    	                   ->order('id DESC')
    	                   ->select();
    }   
    
	/**
     * @desc 获取详情
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2017-11-11
     */
    public function getDetail($condition = [], $field = '*') {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->field($field)->where($where)->order('id DESC')->find();
    }
    
    /**
     * @desc 添加记录
     * 
     * @param array $condition
     * @return mixed
     * @author liujf 
     * @time 2017-11-11 
     */
    public function addRecord($condition = []) {
    
        $data = $this->create($condition);
    
        return $this->add($data);
    }

	/**
	 * @desc 修改信息
	 * 
	 * @param array $where , $condition
	 * @return bool
     * @author liujf 
     * @time 2017-11-11
	 */
	public function updateInfo($where = [], $condition = []) {

		$data = $this->create($condition);

		return $this->where($where)->save($data);
	}

	/**
	 * @desc 删除记录
	 * 
	 * @param array $condition
	 * @return bool
     * @author liujf 
     * @time 2017-11-11
	 */
	public function delRecord($condition = []) {

		if (!empty($condition['id'])) {
			$where['id'] = ['in', explode(',', $condition['id'])];
		} else {
			return false;
		}

		return $this->where($where)->delete();
	}
	
	/**
	 * @desc 获取资质过期的供应商ID集合
	 *
	 * @return array
	 * @author liujf
	 * @time 2018-03-02
	 */
	public function getOverdueSupplierIds() {
	    $list = $this->field('supplier_id, (UNIX_TIMESTAMP(MIN(expiry_date)) + 86399 - UNIX_TIMESTAMP()) AS expiry_time')
                            ->group('supplier_id')
                            ->having('expiry_time <= 0')
                            ->select();
        $supplierIds = [];
        foreach ($list as $item) {
            $supplierIds[] = $item['supplier_id'];
        }
        return $supplierIds;
	}
	
	/**
	 * @desc 获取资质到期天数
	 *
	 * @param int $supplierId 供应商ID
	 * @return mixed
	 * @author liujf
	 * @time 2018-03-02
	 */
	public function getExpiryDateCount($supplierId) {
	    return $this->field('CEIL((UNIX_TIMESTAMP(MIN(expiry_date)) + 86399 - UNIX_TIMESTAMP()) / 86400) AS count')->where(['supplier_id' => $supplierId])->find()['count'];
	}
	
	/**
	 * @desc 获取资质到期时间
	 *
	 * @param int $supplierId 供应商ID
	 * @return mixed
	 * @author liujf
	 * @time 2018-03-03
	 */
	public function getExpiryDate($supplierId) {
	    return $this->field('MIN(expiry_date) AS date')->where(['supplier_id' => $supplierId])->find()['date'];
	}
	
	/**
	 * @desc 获取资质过期时间段内的供应商ID集合
	 *
	 * @return array
	 * @author liujf
	 * @time 2018-03-03
	 */
	public function getOverduePeriodSupplierIds($startDate, $endDate) {
	    $list = $this->field('supplier_id, MIN(expiry_date) AS date')
                    	    ->group('supplier_id')
                    	    ->having('date >= \'' . $startDate . '\' AND date <= \'' . $endDate . '\'')
                    	    ->select();
	    $supplierIds = [];
	    foreach ($list as $item) {
	        $supplierIds[] = $item['supplier_id'];
	    }
	    return $supplierIds;
	}
}
