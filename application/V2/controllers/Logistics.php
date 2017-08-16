<?php
/*
 * @desc 物流报价控制器
 * 
 * @author liujf 
 * @time 2017-08-02
 */
class LogisticsController extends PublicController {

	public function init() {
		parent::init();
		
		$this->quoteModel = new QuoteModel();
		$this->quoteLogiFeeModel = new QuoteLogiFeeModel();
		$this->quoteItemLogiModel = new QuoteItemLogiModel();
		$this->exchangeRateModel = new ExchangeRateModel();
		$this->userModel = new UserModel();
		$this->inquiryCheckLogModel = new InquiryCheckLogModel();

        $this->time = date('Y-m-d H:i:s');
	}
	
	/**
	 * @desc 获取报价单项物流报价列表接口
	 *
	 * @author liujf
	 * @time 2017-08-02
	 */
	public function getQuoteItemLogiListAction() {
	    $condition = $this->put_data;
	
	    if (empty($condition['quote_id'])) $this->jsonReturn(false);
	    
	    $data = $this->quoteItemLogiModel->getJoinList($condition);
	
	    $this->_handleList($this->quoteItemLogiModel, $data, $condition, true);
	}
	
	/**
	 * @desc 获取报价单项物流报价接口
	 *
	 * @author liujf
	 * @time 2017-08-02
	 */
	public function getQuoteItemLogiDetailAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['r_id'])) {
	        $condition['id'] = $condition['r_id'];
	        unset($condition['r_id']);
    	    $res = $this->quoteItemLogiModel->getJoinDetail($condition);
    	
    	    $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 修改报价单项物流报价信息接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function updateQuoteItemLogiInfoAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['items'])) {
	        
	        $flag = true;
	        $data = [];
	        
	        //$this->quoteItemLogiModel->startTrans();
	        
	        foreach ($condition['items'] as $item) {
	            $where['id'] = $item['id'];
	            unset($item['id']);
	            
	            $res = $this->quoteItemLogiModel->updateInfo($where, $item);
	            
	            /*if (!$res) {
	                $this->quoteItemLogiModel->rollback();
	                $flag = false;
	                break;
	            }*/
	            
	            if (!$res) {
	               $data[] = $where['id'];
	               $flag = false;
	            }
	        }
	        
	       // if ($flag) $this->quoteItemLogiModel->commit();
	
	        if ($flag) {
	            $this->jsonReturn($flag);
	        } else {
	            $this->setCode('-101');
	            $this->setMessage('失败!');
	            parent::jsonReturn($data);
	        }
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取报价单列表接口
	 *
	 * @author liujf
	 * @time 2017-08-07
	 */
	public function getQuoteLogiListAction() {
	    $condition = $this->put_data;
	    
	    if (!empty($condition['agent_name'])) {
	         $agent = $this->userModel->where(['name' => $condition['agent_name']])->find();
	         $condition['agent_id'] = $agent['id'];
	    }
	    
	    if (!empty($condition['pm_name'])) {
	        $pm = $this->userModel->where(['name' => $condition['pm_name']])->find();
	        $condition['pm_id'] = $pm['id'];
	    }
	
	    $quoteLogiFeeList= $this->quoteLogiFeeModel->getJoinList($condition);
	    
	    foreach ($quoteLogiFeeList as &$quoteLogiFee) {
            $userAgent = $this->userModel->info($quoteLogiFee['agent_id']);
            $userPm = $this->userModel->info($quoteLogiFee['pm_id']);
	        $quoteLogiFee['agent_name'] = $userAgent['name'];
	        $quoteLogiFee['pm_name'] = $userPm['name'];
	    }
	    
	    if ($quoteLogiFeeList) {
	        $res['code'] = 1;
	        $res['message'] = '成功!';
	        $res['data'] = $quoteLogiFeeList;
	        $res['count'] = $this->quoteLogiFeeModel->getListCount($condition);
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取报价单物流费用详情接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function getQuoteLogiFeeDetailAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        
    	    $quoteLogiFee = $this->quoteLogiFeeModel->getJoinDetail($condition);
    	    
	        $quoteLogiFee['overland_insu'] = $quoteLogiFee['total_exw_price'] * 1.1 * $quoteLogiFee['overland_insu_rate'];
	        $quoteLogiFee['shipping_insu'] = $quoteLogiFee['total_quote_price'] * 1.1 * $quoteLogiFee['shipping_insu_rate'];
	        $tmpTotalFee = $quoteLogiFee['total_exw_price'] + $quoteLogiFee['land_freight'] + $quoteLogiFee['overland_insu'] + $quoteLogiFee['port_surcharge'] + $quoteLogiFee['inspection_fee'] + $quoteLogiFee['inter_shipping'];
	        $quoteLogiFee['dest_tariff_fee'] = $tmpTotalFee * $quoteLogiFee['dest_tariff_rate'];
	        $quoteLogiFee['dest_va_tax_fee'] = $tmpTotalFee * (1 + $quoteLogiFee['dest_tariff_rate']) * $quoteLogiFee['dest_va_tax_rate'];
    	
    	    $this->jsonReturn($quoteLogiFee);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 修改报价单物流费用信息接口
	 *
	 * @author liujf
	 * @time 2017-08-10
	 */
	public function updateQuoteLogiFeeInfoAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        
	        $data = $condition;
	        
	        unset($data['from_port']);
	        unset($data['to_port']);
	        unset($data['trans_mode_bn']);
	        unset($data['box_type_bn']);
	        unset($data['quote_remarks']);
	        
	        $data['inspection_fee'] = 0;
	        $data['land_freight'] = 0;
	        $data['port_surcharge'] = 0;
	        $data['inter_shipping'] = 0;
	        $data['dest_delivery_fee'] = 0;
	        $data['dest_clearance_fee'] = 0;
	        $data['overland_insu_rate'] = 0;
	        $data['shipping_insu_rate'] = 0;
	        $data['dest_tariff_rate'] = 0;
	        $data['dest_va_tax_rate'] = 0;
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->getJoinDetail($condition);
	        $quote = $this->quoteModel->getDetail(['id' =>$quoteLogiFee['quote_id']]);
	        
	        if ($quoteLogiFee['logi_agent_id'] == '') {
	            $data['logi_agent_id'] = $this->user['id'];
	        }
	        
	        $data['updated_by'] = $this->user['id'];
	        $data['updated_at'] = $this->time;
	        
	        $data['inspection_fee'] = $condition['inspection_fee'] > 0 ? $condition['inspection_fee'] : 0;
	        
	        switch ($quoteLogiFee['trade_terms_bn']) {
	            case 'EXW' :
	                
	                break;
	            case 'FCA' || 'FAS' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                break;
	            case 'FOB' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	                break;
	            case 'CPT' || 'CFR' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	                break;
	            case 'CIF' || 'CIP' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	                break;
	            case 'DAP' || 'DAT' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
	                break;
	            case 'DDP' || '快递' :
	                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
	                $data['dest_clearance_fee'] = $condition['dest_clearance_fee'] > 0 ? $condition['dest_clearance_fee'] : 0;
	                $data['dest_tariff_rate'] = $condition['dest_tariff_rate'] > 0 ? $condition['dest_tariff_rate'] : 0;
	                $data['dest_va_tax_rate'] = $condition['dest_va_tax_rate'] > 0 ? $condition['dest_va_tax_rate'] : 0;
	        }
	        
	        $inspectionFeeUSD = $data['inspection_fee'] * $this->_getRateUSD($data['inspection_fee_cur']);
	        $landFreightUSD = $data['land_freight'] * $this->_getRateUSD($data['land_freight_cur']);
	        $overlandInsuUSD = $quote['total_exw_price'] * 1.1 * $data['overland_insu_rate'];
	        $portSurchargeUSD = $data['port_surcharge'] * $this->_getRateUSD($data['port_surcharge_cur']);
	        $interShippingUSD = $data['inter_shipping'] * $this->_getRateUSD($data['inter_shipping_cur']);
	        $destDeliveryFeeUSD = $data['dest_delivery_fee'] * $this->_getRateUSD($data['dest_delivery_fee_cur']);
	        $destClearanceFeeUSD = $data['dest_clearance_fee'] * $this->_getRateUSD($data['dest_clearance_fee_cur']);
	        $sumUSD = $quote['total_exw_price'] + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;
	        $destTariffUSD = $sumUSD * $data['dest_tariff_rate'];
	        $destVaTaxUSD = $sumUSD * (1 + $data['dest_tariff_rate']) * $data['dest_va_tax_rate'];
	        
	        $tmpRate1 = 1 - $quoteLogiFee['premium_rate'] - round($quote['payment_period'] * $quote['bank_interest'] * $quote['fund_occupation_rate'] / 365, 8);
	        $tmpRate2 = $tmpRate1 - 1.1 * $data['shipping_insu_rate'];
	        
	        switch ($quoteLogiFee['trade_terms_bn']) {
	            case 'EXW' :
	                $totalQuotePrice = round(($quote['total_exw_price'] + $inspectionFeeUSD) / $tmpRate1, 8);
	                break;
	            case 'FCA' || 'FAS' :
	                $totalQuotePrice = round(($quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD) / $tmpRate1, 8);
	                break;
	            case 'FOB' :
	                $totalQuotePrice = round(($quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD) / $tmpRate1, 8);
	                break;
	            case 'CPT' || 'CFR' :
	                $totalQuotePrice = round(($quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) / $tmpRate1, 8);
	                break;
	            case 'CIF' || 'CIP' :
	                $tmpCaFee = $quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD;
	                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	                break;
	            case 'DAP' || 'DAT' :
	                $tmpCaFee = $quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $destDeliveryFeeUSD;
	                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	                break;
	            case 'DDP' || '快递' :
	                $tmpCaFee = ($quote['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) * (1 + $data['dest_tariff_rate']) * (1 + $data['dest_va_tax_rate']) + $destDeliveryFeeUSD + $destClearanceFeeUSD;
	                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	        }
	        
	        $shippingInsuUSD = $totalQuotePrice * 1.1 * $data['shipping_insu_rate'];
	        $totalBankFeeUSD = round($totalQuotePrice * $quote['bank_interest'] * $quote['fund_occupation_rate'] * $quote['payment_period']  / 365, 8);
	        $totalInsuFeeUSD =$totalQuotePrice * $quoteLogiFee['premium_rate'];
	        
	        // 物流费用合计
	        $totalFeeUSD = $inspectionFeeUSD +  $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $shippingInsuUSD + $destDeliveryFeeUSD + $destClearanceFeeUSD + $destTariffUSD + $destVaTaxUSD;
	        $data['shipping_charge_cny'] = round($totalFeeUSD * $this->_getRateCNY('USD'), 4);
	        $data['shipping_charge_ncny'] = round($totalFeeUSD, 4);
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $this->quoteModel->startTrans();
	        
	        $res1 = $this->quoteLogiFeeModel->updateInfo(['quote_id' => $condition['quote_id']], $data);
	        
	        $quoteData = [
	            'from_port' => $condition['from_port'],
	            'to_port' => $condition['to_port'],
	            'trans_mode_bn' => $condition['trans_mode_bn'],
	            'box_type_bn' => $condition['box_type_bn'],
	            'quote_remarks' => $condition['quote_remarks'],
	            'total_logi_fee' => $data['shipping_charge_ncny'],
	            'total_quote_price' => round($totalQuotePrice, 4),
	            'total_bank_fee' => round($totalBankFeeUSD, 4),
	            'total_insu_fee' => round($totalInsuFeeUSD, 4)
	        ];
	        
	        $res2 = $this->quoteModel->updateQuote(['quote_no' => $quote['quote_no']], $quoteData);
	        
	        if ($res1 && $res2) {
	            $this->quoteLogiFeeModel->commit();
	            $this->quoteModel->commit();
	            $res = true;
	        } else {
	            $this->quoteLogiFeeModel->rollback();
	            $this->quoteModel->rollback();
	            $res = false;
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 分配物流报价人接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function assignLogiAgentAction() {
	    $condition = $this->put_data;
	    
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $res = $this->quoteLogiFeeModel->updateInfo($where, ['logi_agent_id' => $condition['logi_agent_id']]);
	        
	        $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
	}
	
	/**
	 * @desc 更改物流状态接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function updateLogiStatusAction() {
	    $condition = $this->put_data;
	     
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	         
	        $res = $this->quoteLogiFeeModel->updateStatus($where, $condition['status']);
	         
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 提交项目经理审核接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function submitLogiCheckAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $this->quoteModel->startTrans();
	
	        $res1 = $this->quoteLogiFeeModel->updateStatus($where, 'APPROVED');
	        
	        $res2 = $this->quoteModel->where(['id' => $condition['quote_id']])->save(['status' => 'QUOTED_BY_LOGI']);
	        
	        if ($res1 && $res2) {
	            $this->quoteLogiFeeModel->commit();
	            $this->quoteModel->commit();
	            $res = true;
	        } else {
	            $this->quoteLogiFeeModel->rollback();
	            $this->quoteModel->rollback();
	            $res = false;
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 物流报价驳回接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function rejectLogiAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->where($where)->find();
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $this->inquiryCheckLogModel->startTrans();
	
	        $res1 = $this->quoteLogiFeeModel->updateStatus($where, 'REJECTED');
	        
	        $checkLog= [
	            'inquiry_id' => $quoteLogiFee['inquiry_id'],
	            'quote_id' => $condition['quote_id'],
	            'category' => 'LOGI',
	            'action' => 'APPROVING',
	            'op_note' => $condition['op_note'],
	            'op_result' => 'REJECTED'
	        ];
	        
	        $res2 = $this->addCheckLog($checkLog, $this->inquiryCheckLogModel);
	        
	        if ($res1 && $res2) {
	            $this->quoteLogiFeeModel->commit();
	            $this->inquiryCheckLogModel->commit();
	            $res = true;
	        } else {
	            $this->quoteLogiFeeModel->rollback();
	            $this->inquiryCheckLogModel->rollback();
	            $res = false;
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取报出价格合计
	 *
	 * @param float $calcuFee, $shippingInsuRate, $calcuRate
	 * @return float
	 * @author liujf
	 * @time 2017-08-10
	 */
	private function _getTotalQuotePrice($calcuFee, $shippingInsuRate, $calcuRate) {
	
	    $tmpIfFee = round($calcuFee * 1.1 * $shippingInsuRate / $calcuRate, 8);
	    
	    if ($tmpIfFee >= 8 || $tmpIfFee == 0) {
	        $totalQuotePrice = round($calcuFee / $calcuRate, 8);
	    } else {
	        $totalQuotePrice = round(($calcuFee + 8) / $calcuRate, 8);
	    }
	    
	    return $totalQuotePrice;
	}
	
	/**
	 * @desc 获取币种兑换人民币汇率
	 *
	 * @param string $cur 币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRateCNY($cur) {
	
	    return $this->_getRate($cur, 'CNY');
	}
	
	/**
	 * @desc 获取币种兑换美元汇率
	 *
	 * @param string $cur 币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRateUSD($cur) {
	
	    return $this->_getRate($cur, 'USD');
	}
	
	/**
	 * @desc 获取币种兑换汇率
	 *
	 * @param string $cur 币种
	 * @param string $exchangeCur 兑换币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRate($cur, $exchangeCur = 'CNY') {
	    
	    if (!empty($cur)) {
	        $exchangeRate = $this->exchangeRateModel->where(['cur_bn1' => $cur, 'cur_bn2' => $exchangeCur])->field('rate')->find();
	        
	        return $exchangeRate['rate'];
	    } else {
	        return false;
	    }
	    
	}
    
	/**
	 * @desc 对获取列表数据的处理
	 * 
     * @author liujf 
     * @time 2017-08-02
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
     * @desc 重写jsonReturn方法
     * 
     * @author liujf 
     * @time 2017-08-02
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