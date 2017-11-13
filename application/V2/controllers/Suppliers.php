<?php
/*
 * @desc 供应商控制器
 * 
 * @author liujf 
 * @time 2017-11-10
 */
class SuppliersController extends PublicController {

	public function init() {
		parent::init();
		
		$this->suppliersModel = new SuppliersModel();
		$this->supplierBankInfoModel = new SupplierBankInfoModel();
		$this->supplierExtraInfoModel = new SupplierExtraInfoModel();
		$this->supplierContactModel = new SupplierContactModel();
		$this->supplierMaterialCatModel = new SupplierMaterialCatModel();
		$this->supplierAgentModel = new SupplierAgentModel();
		$this->supplierQualificationModel = new SupplierQualificationModel();
		$this->supplierCheckLogsModel = new SupplierCheckLogsModel();
		$this->inquiryModel = new InquiryModel();

        $this->time = date('Y-m-d H:i:s');
	}
	
	/**
	 * @desc 新增供应商记录接口
	 *
	 * @author liujf
	 * @time 2017-11-10
	 */
	public function addSupplierRecordAction() {
	    
	    $this->suppliersModel->startTrans();
	    
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	    
	    $res1 = $this->suppliersModel->addRecord($condition);
	    
	    if ($res1) $condition['supplier_id'] = $res1;
         
        $res2 = $this->supplierContactModel->addRecord($condition);
        
        $res3 = $this->supplierQualificationModel->addRecord($condition);
	    
	    if ($res1 && $res2 && $res3) {
	        $this->suppliersModel->commit();
	        $res['supplier_id'] = $res1;
	        $res['contact_id'] = $res2;
	        $res['qualification_id'] = $res3;
	    } else {
	        $this->suppliersModel->rollback();
	        $res = false;
	    }
	    
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 修改供应商信息接口
	 *
	 * @author liujf
	 * @time 2017-11-10
	 */
	public function updateSupplierInfoAction() {
	    $condition = $this->_trim($this->put_data);
	    
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商id参数!');

	    if ($condition['supplier_type'] == '') jsonReturn('', -101, '企业类型不能为空!');
	    
	    if ($condition['name'] == '') jsonReturn('', -101, '公司名称不能为空!');
	    
	    if (strlen($condition['name']) > 100 || strlen($condition['name_en']) > 100) jsonReturn('', -101, '您输入的公司名称超出长度!');
	    
	    if ($condition['country_bn'] == '') jsonReturn('', -101, '国家不能为空!');
	    
	    if ($condition['address'] == '') jsonReturn('', -101, '公司地址不能为空!');
	    
	    if (strlen($condition['address']) > 100) jsonReturn('', -101, '您输入的公司地址大于100字!');
	    
	    if ($condition['social_credit_code'] == '') jsonReturn('', -101, '营业执照（统一社会信用代码）编码不能为空!');
	    
	    if (strlen($condition['social_credit_code']) > 20) jsonReturn('', -101, '您输入的营业执照（统一社会信用代码）编码有误!');
	    
	    if ($condition['reg_capital'] == '') jsonReturn('', -101, '注册资本不能为空!');
	    
	    if (strlen($condition['reg_capital']) > 20) jsonReturn('', -101, '请输入正确的注册资本!');
	    
	    if (strlen($condition['profile']) > 500) jsonReturn('', -101, '您输入的企业简介大于500字!');
	    
	    if ($condition['bank_name'] == '') jsonReturn('', -101, '开户行名称不能为空!');
	    
	    if (strlen($condition['bank_name']) > 60) jsonReturn('', -101, '您输入的开户行名称大于60字!');
	    
	    if ($condition['bank_account'] == '') jsonReturn('', -101, '开户账号不能为空!');
	    
	    if (!is_numeric($condition['bank_account'])) jsonReturn('', -101, '开户账号只能输入数字!');
	    
	    if (strlen($condition['bank_account']) > 16) jsonReturn('', -101, '您输入的开户账号超过16位!');
	    
	    if (strlen($condition['bank_address']) > 100) jsonReturn('', -101, '开户地址最多输入100字!');
	    
	    if ($condition['org_id'] == '') jsonReturn('', -101, '所属事业部不能为空!');
	    
	    if ($condition['sign_agreement_flag'] == '') jsonReturn('', -101, '是否签订合作协议不能为空!');
	    
	    if ($condition['providing_sample_flag'] == '') jsonReturn('', -101, '是否提供样品不能为空!');
	    
	    if (strlen($condition['distribution_products']) > 200) jsonReturn('', -101, '您输入的铺货产品大于200字!');
	    
	    if ($condition['distribution_amount'] != '' && preg_match('/[^\.\d]/i', $condition['distribution_amount'])) jsonReturn('', -101, '易瑞铺货金额只能输入正数 0 和点（.）!');
	    
	    if (strlen($condition['stocking_place']) > 40) jsonReturn('', -101, '备货地点长度不超过40个字!');
	    
	    $this->suppliersModel->startTrans();
	    
	    // 供应商基本信息
	    $condition['updated_by'] = $this->user['id'];
	    $condition['updated_at'] = $this->time;
	    
	    $res1 = $this->suppliersModel->updateInfo(['id' => $condition['id']], $condition);
	    
	    $where['supplier_id'] = $condition['id'];
	    
	    $hasBank = $this->supplierBankInfoModel->field('id')->where($where)->find();
	    
	    // 供应商银行账户信息
	    $brandData = [
	        'bank_name' => $condition['bank_name'],
	        'bank_account' => $condition['bank_account'],
	        'address' => $condition['bank_address']
	    ];
	    
	    if ($hasBank) {
	        $brandData['updated_by'] = $this->user['id'];
	        $brandData['updated_at'] = $this->time;
	        $res2 = $this->supplierBankInfoModel->where($where)->save($brandData);
	    } else {
	        $brandData['supplier_id'] = $condition['id'];
	        $brandData['created_by'] = $this->user['id'];
	        $brandData['created_at'] = $this->time;
	        $res2 = $this->supplierBankInfoModel->add($brandData);
	    }
	    
	    $hasExtra = $this->supplierExtraInfoModel->field('id')->where($where)->find();
	    
	    // 供应商其他信息
	    $extraData = [
	        'sign_agreement_flag' => $condition['sign_agreement_flag'],
	        'providing_sample_flag' => $condition['providing_sample_flag'],
	        'distribution_products' => $condition['distribution_products'],
	        'est_time_arrival' => $condition['est_time_arrival'],
	        'distribution_amount' => $condition['distribution_amount'],
	        'stocking_place' => $condition['stocking_place'],
	        'info_upload_flag' => $condition['info_upload_flag'],
	        'photo_upload_flag' => $condition['photo_upload_flag']
	    ];
	    
	    if ($hasExtra) {
	        $extraData['updated_by'] = $this->user['id'];
	        $extraData['updated_at'] = $this->time;
	        $res3 = $this->supplierExtraInfoModel->updateInfo($where, $extraData);
	    } else {
	        $extraData['supplier_id'] = $condition['id'];
	        $extraData['created_by'] = $this->user['id'];
	        $extraData['created_at'] = $this->time;
	        $res3 = $this->supplierExtraInfoModel->addRecord($extraData);
	    }
	     
	    if ($res1 && $res2 && $res3) {
	        $this->suppliersModel->commit();
	        $res = true;
	    } else {
	        $this->suppliersModel->rollback();
	        $res = false;
	    }
	     
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 获取供应商列表接口
	 *
	 * @author liujf
	 * @time 2017-11-10
	 */
	public function getSupplierListAction() {
	    $condition = $this->put_data;
	    
	    $condition['org_id'] = $this->inquiryModel->getDeptOrgId($this->user['group_id']);
	    
	    $data = $this->suppliersModel->getJoinList($condition);
	
	    $this->_handleList($this->suppliersModel, $data, $condition, true);
	}
	
	/**
	 * @desc 获取供应商详情接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function getSupplierDetailAction() {
	    $condition = $this->put_data;
	    
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	
	    $res = $this->suppliersModel->getJoinDetail($condition);
    	
    	$this->jsonReturn($res);
	}
	
	/**
	 * @desc 新增供应商联系人记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function addSupplierContactRecordAction() {
	    $condition = $this->put_data;
	    
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	     
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	     
	    $res = $this->supplierContactModel->addRecord($condition);
	     
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 删除供应商联系人记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function delSupplierContactRecordAction() {
	    $condition = $this->put_data;
	     
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商联系人主键id参数!');
	
	    $res = $this->supplierContactModel->delRecord(['id' => $condition['id']]);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 批量修改供应商联系人信息接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function batchUpdateSupplierContactInfoAction() {
	    $condition = $this->_trim($this->put_data);
	    
	    if ($condition['items'] == '') jsonReturn('', -101, '缺少items参数!');
	        
        $flag = true;
        $data = [];
        
        foreach ($condition['items'] as $item) {
            $where['id'] = $item['id'];
            unset($item['id']);
            
            if ($item['contact_name'] == '') jsonReturn('', -101, '联系人姓名不能为空!');
             
            if (strlen($item['contact_name']) > 40) jsonReturn('', -101, '您输入的联系人姓名过长!');
            
            if ($item['phone'] == '') jsonReturn('', -101, '联系方式不能为空!');
             
            if (strlen($item['phone']) > 20) jsonReturn('', -101, '您输入的联系方式不正确!');
            
            if ($item['email'] == '') jsonReturn('', -101, '邮箱不能为空!');
             
            if (!preg_match('/.+@[^\.]+\.com/i', $item['email'])) jsonReturn('', -101, '您输入的邮箱格式不正确!');
            
            if (strlen($item['email']) > 20) jsonReturn('', -101, '您输入的邮箱大于20字!');
            
            if (strlen($item['title']) > 20) jsonReturn('', -101, '您输入的职位大于20字!');
            
            if (strlen($item['station']) > 20) jsonReturn('', -101, '您输入的岗位大于20字!');
            
            if (strlen($item['remarks']) > 100) jsonReturn('', -101, '您输入的负责产品大于100字!');
            
            $res = $this->supplierContactModel->updateInfo($where, $item);
            
            if (!$res) {
               $data[] = $where['id'];
               $flag = false;
            }
        }

        if ($flag) {
            $this->jsonReturn($flag);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn($data);
        }
	}
	
	/**
	 * @desc 获取供应商联系人列表接口
	 * 
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function getSupplierContactListAction() {
	    $condition = $this->put_data;
	     
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	     
	    $data = $this->supplierContactModel->getList($condition);
	    
	    $this->_handleList($this->supplierContactModel, $data, $condition);
	}
	
	/**
	 * @desc 新增供应商供货范围记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function addSupplierSupplyRecordAction() {
	    $condition = $this->put_data;
	    
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	    
	    if ($condition['material_cat_no1'] == '') jsonReturn('', -101, '一级物料分类编码不能为空!');
	
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	
	    $res = $this->supplierMaterialCatModel->addRecord($condition);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 删除供应商供货范围记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function delSupplierSupplyRecordAction() {
	    $condition = $this->put_data;
	
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商供货范围主键id参数!');
	
	    $res = $this->supplierMaterialCatModel->delRecord(['id' => $condition['id']]);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 新增供应商开发负责人记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function addSupplierAgentRecordAction() {
	    $condition = $this->put_data;
	
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	     
	    if ($condition['agent_type'] == '') jsonReturn('', -101, '开发责任人类别不能为空!');
	    
	    if ($condition['org_id'] == '') jsonReturn('', -101, '开发责任人不能为空!');
	
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	
	    $res = $this->supplierAgentModel->addRecord($condition);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 删除供应商开发负责人记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function delSupplierAgentRecordAction() {
	    $condition = $this->put_data;
	
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商开发负责人主键id参数!');
	
	    $res = $this->supplierMaterialCatModel->delRecord(['id' => $condition['id']]);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 新增供应商资质记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function addSupplierQualificationRecordAction() {
	    $condition = $this->put_data;
	     
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	
	    $res = $this->supplierQualificationModel->addRecord($condition);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 删除供应商资质记录接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function delSupplierQualificationRecordAction() {
	    $condition = $this->put_data;
	
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商资质主键id参数!');
	
	    $res = $this->supplierQualificationModel->delRecord(['id' => $condition['id']]);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 批量修改供应商资质信息接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function batchUpdateSupplierQualificationInfoAction() {
	    $condition = $this->_trim($this->put_data);
	     
	    if ($condition['items'] == '') jsonReturn('', -101, '缺少items参数!');
	     
	    $flag = true;
	    $data = [];
	
	    foreach ($condition['items'] as $item) {
	        $where['id'] = $item['id'];
	        unset($item['id']);
	         
	        if (strlen($item['name']) > 50) jsonReturn('', -101, '您输入的资质名称长度超过限制!');
	
	        if ($item['code'] == '') jsonReturn('', -101, '资质编码不能为空!');
	         
	        if (strlen($item['code']) > 50) jsonReturn('', -101, '您输入的资质编码长度超过限制!');
	
	        if ($item['issue_date'] == '') jsonReturn('', -101, '发证日期不能为空!');
	
	        if (strlen($item['issuing_authority']) > 50) jsonReturn('', -101, '您输入的发证机构长度超过限制!');
	        
	        if (strlen($item['remarks']) > 100) jsonReturn('', -101, '您输入的认证产品长度超过限制!');
	        
	        $item['updated_by'] = $this->user['id'];
	        $item['updated_at'] = $this->time;
	
	        $res = $this->supplierQualificationModel->updateInfo($where, $item);
	
	        if (!$res) {
	            $data[] = $where['id'];
	            $flag = false;
	        }
	    }
	
	    if ($flag) {
	        $this->jsonReturn($flag);
	    } else {
	        $this->setCode('-101');
	        $this->setMessage('失败!');
	        parent::jsonReturn($data);
	    }
	}
	
	/**
	 * @desc 获取供应商资质列表接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function getSupplierQualificationListAction() {
	    $condition = $this->put_data;
	
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	
	    $data = $this->supplierQualificationModel->getList($condition);
	     
	    $this->_handleList($this->supplierQualificationModel, $data, $condition);
	}
	
	/**
	 * @desc 提交供应商审核接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function submitSupplierCheckAction() {
	    $condition = $this->put_data;
	
	    if ($condition['id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	
	    $where['id'] = $condition['id'];
        
        $res = $this->suppliersModel->updateInfo($where, ['status' => 'CHECKING']);
        
        $this->jsonReturn($res);
	}
	
	/**
	 * @desc 获取供应商审核日志列表接口
	 *
	 * @author liujf
	 * @time 2017-11-11
	 */
	public function getSupplierCheckLogListAction() {
	    $condition = $this->put_data;
	
	    if ($condition['supplier_id'] == '') jsonReturn('', -101, '缺少供应商id参数!');
	
	    $data = $this->supplierCheckLogsModel->getList($condition);
	     
	    $this->_handleList($this->supplierCheckLogsModel, $data, $condition);
	}
    
	/**
	 * @desc 对获取列表数据的处理
	 * 
     * @author liujf 
     * @time 2017-11-10
	 */
	private function _handleList($model, $data = [], $condition = [], $join = false) {
	   if ($data) {
    		$res['code'] = 1;
    		$res['message'] = '成功!';
    		$res['data'] = $data;
    		$res['count'] = $join ? $model->getJoinCount($condition) : $model->getCount($condition);
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
	}
	
	/**
	 * @desc 去掉参数数据两侧的空格
	 *
	 * @author liujf
	 * @time 2017-11-10
	 */
	private function _trim($condition = []) {
	    foreach ($condition as $k => $v) {
	        if (is_array($v)) {
	            $condition[$k] = $this->_trim($v);
	        } else {
	            $condition[$k] = trim($v);
	        }
	    }
	    return $condition;
	}
    
	/**
     * @desc 重写jsonReturn方法
     * 
     * @author liujf 
     * @time 2017-11-10
     */
    public function jsonReturn($data = [], $type = 'JSON') {
    	if ($data) {
    		$this->setCode('1');
            $this->setMessage('成功!');
    		parent::jsonReturn($data, $type);
    	} else {
    		$this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn();
    	}
    }
}