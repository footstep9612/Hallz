<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierInquiry
 * @author  zhongyg
 * @date    2017-11-7 13:52:20
 * @version V2.0
 * @desc
 */
class SupplierInquiryModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui_supplier'; //数据库名称
    protected $areas = ['Middle East', 'South America', 'North America', 'Africa', 'Pan Russian', 'Asia-Pacific', 'Europe'];

    public function __construct() {

        parent::__construct();
    }

    private function _getCondition($condition, &$where) {
        if (!empty($condition['supplier_no'])) {
            $where .= ' AND tmp.supplier_no=\'' . trim($condition['supplier_no']) . '\'';
        }
        if (!empty($condition['supplier_name'])) {
            $where .= ' AND tmp.supplier_name like \'%' . trim($condition['supplier_name']) . '%\'';
        }
        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
            $where .= ' AND tmp.created_at between \'' . trim($condition['created_at_start']) . '\''
                    . ' AND \'' . trim($condition['created_at_end']) . '\'';
        } elseif (!empty($condition['created_at_start'])) {
            $where .= ' AND tmp.created_at > \'' . trim($condition['created_at_start']) . '\'';
        } elseif (!empty($condition['created_at_end'])) {
            $where .= ' AND tmp.created_at < \'' . trim($condition['created_at_end']) . '\'';
        }
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getList($condition) {
        $where = '';

        $this->_getCondition($condition, $where);
        list($offset, $length) = $this->_getPage($condition);

        $inquiry_model = new InquiryModel();
        $inquiry_table = $inquiry_model->getTableName();
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName();

        $marketareacountry_model = new MarketAreaCountryModel();

        $marketareacountry_table = $marketareacountry_model->getTableName();
        $field = 'supplier_no,supplier_name,supplier_id,';
        foreach ($this->areas as $area_bn) {
            $new_area_bn = str_replace(' ', '-', trim($area_bn));
            $field .= 'sum(if(tmp.area_bn=\'' . $area_bn . '\',1,0)) as \'' . $new_area_bn . '\',';
        }
        $field .= 'sum(tmp.area_bn is not null) as \'total\' ';

        $supplier_table = $this->getTableName();
        $areas = '\'Middle East\',\'South America\',\'North America\',\'Africa\',\'Pan Russian\',\'Asia-Pacific\',\'Europe\'';
        $sql = 'select ' . $field . ' from (SELECT s.supplier_no,s.name as supplier_name,s.id as supplier_id,fqi.inquiry_id,mac.market_area_bn as area_bn,s.created_at FROM '
                . $supplier_table . ' s left JOIN ' . $final_quote_item_table . ' fqi on fqi.supplier_id=s.id and fqi.deleted_flag=\'N\' and fqi.`status`=\'VALID\' '
                . ' left JOIN ' . $inquiry_table . ' i on i.id =fqi.inquiry_id '
                . ' AND i.deleted_flag = \'N\' AND i.status = \'QUOTE_SENT\' '
                . ' AND i.quote_status = \'COMPLETED\' '
                . ' left join ' . $marketareacountry_table . ' mac on mac.country_bn=i.country_bn  '
                . ' WHERE s.deleted_flag = \'N\' '
                . ' AND  s.`status` in (\'APPROVED\', \'VALID\', \'DRAFT\', \'APPROVING\',\'INVALID\') '
                . ' and  mac.market_area_bn  IN (' . $areas . ')'
                . ' GROUP BY fqi.inquiry_id,mac.market_area_bn,fqi.supplier_id  ) tmp WHERE 1=1 ' . $where
                . ' group by  supplier_id order by total desc ';

        $data = $this->query($sql . ' limit ' . $offset . ' ,' . $length);


        $count = $this->query('select count(*) as num from (' . $sql . ') t');

        return [$data, isset($count[0]['num']) ? intval($count[0]['num']) : 0];
    }

    /**
     * 供应商数量
     * @return mix
     * @author zyg
     */
    public function getCount($condition) {

        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $this->_getCondition($condition, $where);
        $count = $this
                // ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getAreaCountBySupplierId($supplier_id, $created_at_start, $created_at_end, &$item) {

        $inquiry_ids = $this->getInquiryIdsSupplierId($supplier_id);
        $item['total'] = 0;
        foreach ($this->areas as $area) {

            $areabn = str_replace(' ', '-', trim($area));
            $item[$areabn] = 0;
        }

        if (empty($inquiry_ids)) {
            return null;
        }

        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'area_bn' => ['in', $this->areas],
            'id' => ['in', $inquiry_ids]
        ];
        if ($created_at_start && $created_at_end) {
            $where['created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif ($created_at_start) {
            $where['created_at'] = ['egt', $created_at_start];
        } elseif ($created_at_end) {
            $where['created_at'] = ['elt', $created_at_end];
        }
        $inquiry_model = new InquiryModel();

        $areacounts = $inquiry_model
                ->field('count(\'id\') as area_count,area_bn ')
                ->where($where)
                ->group('area_bn')
                ->select();

        foreach ($areacounts as $areacount) {
            $area_bn = str_replace(' ', '-', trim($areacount['area_bn']));
            $item[$area_bn] = $areacount['area_count'];
            $item['total'] += $areacount['area_count'];
        }
    }

    /**
     * 获取供应商询单ID
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquiryIdsSupplierId($supplier_id) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $where = ['supplier_id' => $supplier_id,
            'deleted_flag' => 'N',
            'status' => 'VALID',
        ];
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where($where)->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        return $inquiry_ids;
    }

    /**
     * 供应商数量
     * @return mix
     * @author zyg
     */
    public function getSupplierCount() {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'DRAFT', 'APPROVING', 'INVALID']]
        ];
        $map['name'] = ['neq', ''];
        $map[] = '`name` is not null';
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $count = $this
                // ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 询单数量
     * @return mix
     * @author zyg
     */
    public function getInquiryCount($supplier_id = null) {

        $final_quote_item_model = new FinalQuoteItemModel();
        $supplier_model = new SupplierModel();
        $supplier_table = $supplier_model->getTableName();
        $final_where = ['fqi.supplier_id' => ['gt', 0],
            'fqi.deleted_flag' => 'N',
            'fqi.status' => 'VALID',
            's.id' => ['gt', 0],
            's.deleted_flag' => 'N',
        ];
        if ($supplier_id) {
            $final_where['fqi.supplier_id'] = $supplier_id;
        }
        $inquiryids = $final_quote_item_model
                        ->alias('fqi')
                        ->join($supplier_table . ' s on s.id=fqi.supplier_id')
                        ->field('fqi.inquiry_id')
                        ->where($final_where)->group('fqi.inquiry_id')->select();
        $inquiry_ids = [];


        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        if (empty($inquiry_ids)) {
            return 0;
        }
        $marketareacountry_model = new MarketAreaCountryModel();

        $marketareacountry_table = $marketareacountry_model->getTableName();
        $where = [
            'i.deleted_flag' => 'N',
            'i.status' => 'QUOTE_SENT',
            'i.quote_status' => 'COMPLETED',
            'i.id' => ['in', $inquiry_ids],
            'mac.market_area_bn' => ['in', $this->areas],
        ];
        $inquiry_model = new InquiryModel();
        $count = $inquiry_model
                ->alias('i')
                ->join($marketareacountry_table . ' mac on  mac.country_bn=i.country_bn  ')
                ->where($where)
                ->count();

        return $count > 0 ? $count : 0;
    }

    /**
     * 询单列表
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquirysBySupplierId($supplier_id, $condition) {
        list($offset, $length) = $this->_getPage($condition);
        $final_quote_item_model = new FinalQuoteItemModel();
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where(['supplier_id' => $supplier_id,
                            'deleted_flag' => 'N',
                            'status' => 'VALID',
                        ])->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }


        if (empty($inquiry_ids)) {
            return null;
        }
        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'id' => ['in', $inquiry_ids],
            'area_bn' => ['in', $this->areas]
        ];
        $inquiry_model = new InquiryModel();
        $list = $inquiry_model
                ->field('id as inquiry_id,inquiry_no,serial_no,created_at')
                ->where($where)
                ->order('created_at ASC')
                ->limit($offset, $length)
                ->select();

        return $list;
    }

    /**
     * 询单明细
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function Info($supplier_id) {

        $info = $this
                ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where(['id' => $supplier_id])
                ->find();

        return $info;
    }

    /**
     * 导出询单列表
     * @return mix
     * @author zyg
     */
    public function Inquiryexport() {
        $country_model = new CountryModel();
        $country_table = $country_model->getTableName(); //国家表
        $market_area_country_model = new MarketAreaCountryModel();
        $market_area_country_table = $market_area_country_model->getTableName(); //国家区域关系表
        $market_area_model = new MarketAreaModel();
        $market_area_table = $market_area_model->getTableName();   //营销区域表
        $employee_model = new EmployeeModel();
        $employee_table = $employee_model->getTableName(); //管理员表
        $org_model = new OrgModel(); //
        $org_table = $org_model->getTableName(); //组织表
        $field = 'i.serial_no,qt.sku,';
        $field .= '(select country.`name` from ' . $country_table . ' as country where country.bn=i.country_bn and country.lang=\'zh\' group by country.bn) as country_name ,'; //国家名称
        $field .= '(select ma.`name` from ' . $country_table . ' c left join ' . $market_area_country_table . ' mac'
                . ' on mac.country_bn=c.bn '
                . ' left join ' . $market_area_table . ' ma on ma.bn=mac.market_area_bn '
                . 'where c.bn=i.country_bn and ma.lang=\'zh\' group by ma.bn) as market_area_name ,'; //营销区域名称
        $field .= '(select `name` from ' . $org_table . ' where i.org_id=id ) as org_name,'; //事业部
        $field .= 'i.buyer_code,it.remarks,it.name_zh,it.name,it.model,it.qty,it.unit,';
        $field .= 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,';
        $field .= 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,';
        $field .= 'i.quote_deadline,qt.supplier_id,';


        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_item_model = new InquiryItemModel();
        $inquiry_item_table = $inquiry_item_model->getTableName(); //询单项明细表
        /*         * *************-----------询单项明细结束------------------- */

        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_check_log_model = new InquiryCheckLogModel();
        $inquiry_check_log_table = $inquiry_check_log_model->getTableName(); //询单项明细表
        $inquiry_check_log_sql = '(select max(out_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';
        $inquiry_check_in_log_sql = '(select min(into_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';
        $field .= $inquiry_check_in_log_sql . ' and in_node=\'BIZ_DISPATCHING\' group by inquiry_id) as inflow_time,'; //转入日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'BIZ_DISPATCHING\' group by inquiry_id) as inflow_time_out,'; //转入日期
        $field .= $inquiry_check_log_sql . ' and in_node=\'BIZ_QUOTING\' group by inquiry_id) as bq_time,'; //事业部报价日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'LOGI_DISPATCHING\' group by inquiry_id) as ld_time,'; //物流接收日期
        $field .= $inquiry_check_log_sql . ' and in_node=\'LOGI_QUOTING\' group by inquiry_id) as la_time,'; //物流报出日期
        $field .= $inquiry_check_log_sql . ' and in_node=\'MARKET_APPROVING\' group by inquiry_id) as qs_time,'; //报出日期
        /*         * *************-----------询单项明细结束------------------- */
        $field .= 'i.created_at,it.category,'; //报价用时 为qs_time-created_at 或当前时间-created_at;

        $employee_sql = '(select `name` from ' . $employee_table . ' where deleted_flag=\'N\' ';
        $field .= $employee_sql . ' AND id=i.agent_id)as agent_name,'; //市场负责人
        $field .= $employee_sql . ' AND id=i.quote_id)as quote_name,'; //商务技术部报价人
        $field .= $employee_sql . ' AND id=i.check_org_id)as check_org_name,'; //事业部负责人
        $field .= ' qt.brand,qt.quote_unit,qt.purchase_unit_price,qt.purchase_unit_price*qt.quote_qty as total,'; //total厂家总价（元）
        $field .= ' fqt.quote_unit_price,fqt.total_quote_price,(fqt.total_quote_price+fqt.total_logi_fee+fqt.total_bank_fee+fqt.total_insu_fee) as total_quoted_price,'; //报价总金额（美金）
        $field .= 'qt.gross_weight_kg,(qt.gross_weight_kg*qt.quote_qty) as total_kg,qt.package_size,qt.package_mode,qt.quote_qty,';
        $field .= 'qt.delivery_days,qt.period_of_validity,i.trade_terms_bn,';
        $field .= '(case i.status WHEN \'BIZ_DISPATCHING\' THEN \'事业部分单员\' '
                . 'WHEN \'CC_DISPATCHING\' THEN \'易瑞客户中心\' '
                . 'WHEN \'BIZ_QUOTING\' THEN \'事业部报价\' '
                . 'WHEN \'LOGI_DISPATCHING\' THEN \'物流分单员\' '
                . 'WHEN \'LOGI_QUOTING\' THEN \'物流报价\' '
                . 'WHEN \'LOGI_APPROVING\' THEN \'物流审核\' '
                . 'WHEN \'BIZ_APPROVING\' THEN \'事业部核算\' '
                . 'WHEN \'MARKET_APPROVING\' THEN \'市场主管审核\' '
                . 'WHEN \'MARKET_CONFIRMING\' THEN \'市场确认\' '
                . 'WHEN \'QUOTE_SENT\' THEN \'报价单已发出\' '
                . 'WHEN \'INQUIRY_CLOSED\' THEN \'报价关闭\' '
                . ' END) as istatus,';

        $field .= '(case i.quote_status WHEN \'NOT_QUOTED\' THEN \'未报价\' '
                . 'WHEN \'ONGOING\' THEN \'报价中\' '
                . 'WHEN \'QUOTED\' THEN \'已报价\' '
                . 'WHEN \'COMPLETED\' THEN \'已完成\' '
                . ' END) as iquote_status,i.quote_notes';
        /*         * ****报价单明细** */
        $quote_item_model = new QuoteItemModel();
        $quote_item_table = $quote_item_model->getTableName(); //报价单明细表
        /*         * ****报价单明细** */

        /*         * **最终报价单明细** */
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName(); //最终报价单明细
        /*         * **最终报价单明细** */

        $inquiry_model = new InquiryModel();
        $list = $inquiry_model->alias('i')
                ->join($inquiry_item_table . ' as it on it.deleted_flag=\'N\' and it.inquiry_id=i.id', 'left')
                ->join($quote_item_table . ' as qt on qt.deleted_flag=\'N\' and qt.inquiry_id=i.id and qt.sku=it.sku', 'left')
                ->join($final_quote_item_table . ' as fqt on fqt.deleted_flag=\'N\' and fqt.inquiry_id=i.id and fqt.sku=it.sku', 'left')
                ->field($field)
                ->where(['i.deleted_flag' => 'N', 'i.status' => ['neq', 'DRAFT']])
                ->select();

        $this->_setSupplierName($list);
        $this->_setquoted_time($list);
        $this->_setProductName($list);
        $this->_setConstPrice($list);
        $this->_setOilFlag($list);
        $this->_setMaterialCat($list, 'zh');

        return $this->_createXls($list);
    }

    /*
     * 对应表
     *
     */

    private function _getKeys() {
        return [
            'B' => ['serial_no', '报价单号'],
            'C' => ['country_name', '询价单位'],
            'D' => ['market_area_name', '所属地区部'],
            'E' => ['org_name', '事业部'],
            'F' => ['ie_erui', '是否走易瑞'],
            'G' => ['buyer_code', '客户名称或代码'],
            'H' => ['remarks', '客户及项目背景描述'],
            'I' => ['name_zh', '品名中文'],
            'J' => ['name', '品名外文'],
            'K' => ['product_name', '产品名称'],
            'L' => ['supplier_name', '供应商'],
            'M' => ['model', '规格'],
            'N' => [null, '图号'],
            'O' => ['qty', '数量'],
            'P' => ['unit', '单位'],
            'Q' => ['oil_flag', '油气or非油气'],
            'R' => ['material_cat_name', '平台产品分类'],
            'S' => ['category', '产品分类'],
            'T' => ['keruiflag', '是否科瑞设备用配件'],
            'U' => ['bidflag', '是否投标'],
            'V' => ['inflow_time', '转入日期'],
            'W' => ['quote_deadline', '需用日期'],
            'X' => [null, '澄清完成日期'],
            'Y' => ['bq_time', '事业部报出日期'],
            'Z' => ['ld_time', '物流接收日期'],
            'AA' => ['la_time', '物流报出日期'],
            'AB' => ['quoted_time', '报价用时(小时)'],
            'AC' => ['quoted_time', '获单主体单位)'],
            'AE' => ['quoted_time', '获取人)'],
            'AF' => ['agent_name', '市场负责人'],
            'AG' => ['quote_name', '商务技术部报价人'],
            'AH' => ['check_org_name', '事业部负责人'],
            'AI' => ['brand', '产品品牌'],
            'AJ' => ['supplier_name', '报价单位'],
            'AK' => [null, '报价人联系方式'],
            'AL' => ['purchase_unit_price', '厂家单价（元）'],
            'AM' => ['total', '厂家总价（元）'],
            'AN' => [null, '利润率'],
            'AO' => ['quote_unit_price', '报价单价（元）'],
            'AP' => ['total_quote_price', '报价总价（元）'],
            'AQ' => ['total_quoted_price', '报价总金额（美金）'],
            'AR' => ['gross_weight_kg', '单重(kg)'],
            'AS' => ['total_kg', '总重(kg)'],
            'AT' => ['package_size', '包装体积(mm)'],
            'AU' => ['package_mode', '包装方式'],
            'AV' => ['delivery_days', '交货期（天）'],
            'AW' => ['period_of_validity', '有效期（天）'],
            'AX' => ['trade_terms_bn', '贸易术语'],
            'AY' => ['istatus', '最新进度及解决方案'],
            'AZ' => ['iquote_status', '报价后状态'],
            'BA' => ['quote_notes', '备注'],
//            'BA' => [null, '报价超48小时原因类型'],
//            'BB' => [null, '报价超48小时分析'],
//            'BC' => [null, '成单或失单'],
//            'BD' => [null, '失单原因类型'],
//            'BE' => [null, '失单原因分析'],
        ];
    }

    private function _createXls($list) {
        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);
        $dirName = $tmpDir . date('YmdH', time());
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();

        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->setTitle('询报价单表');
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        $keys = $this->_getKeys();
        $objSheet->setCellValue('A1', '序号');
        foreach ($keys as $rowname => $key) {
            $objSheet->setCellValue($rowname . '1', $key[1]);
        }
        foreach ($list as $j => $item) {
            $objSheet->setCellValue('A' . ($j + 2), ($j + 1));
            foreach ($keys as $rowname => $key) {

                if ($key && isset($item)) {
                    $objSheet->setCellValue($rowname . ($j + 2), isset($item[$key[0]]) ? $item[$key[0]] : null);
                } else {
                    $objSheet->setCellValue($rowname . ($j + 2), '');
                }
            }
        }
        $objSheet->freezePaneByColumnAndRow(2, 2);
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
        $objSheet->getStyle('A1:BD' . ($j + 2))->applyFromArray($styleArray);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $file = $dirName . DS . '导出的询报价单' . date('YmdHi') . '.xls';
        $objWriter->save($file);

        if (file_exists($file)) {
            //把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $file;
            $data['type'] = 'application/xls';
            $data['name'] = '导出的询报价单' . date('YmdHi') . '.xls';
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($file);
                return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $file . ' 上传到FastDFS失败', Log::ERR);
            return false;
        }
        return false;
    }

    //产品名称、规格、价格、供应商等信息

    private function _setSupplierName(&$list) {
        $supplier_ids = [];
        foreach ($list as $item) {
            if ($item['supplier_id']) {
                $supplier_ids[] = $item['supplier_id'];
            }
        }
        $suppliers_model = new SuppliersModel();
        $supplier_names = $suppliers_model->getSupplierNameByIds($supplier_ids);

        foreach ($list as $key => $item) {
            if ($item['supplier_id'] && isset($supplier_names[$item['supplier_id']])) {
                $list[$key]['supplier_name'] = $supplier_names[$item['supplier_id']]['name'];
            }
        }
    }

    private function _setProductName(&$list) {
        $skus = [];
        foreach ($list as $item) {
            if ($item['sku']) {
                $skus[] = $item['sku'];
            }
        }
        $goods_model = new GoodsModel();
        $product_names = $goods_model->getProductNamesAndMaterialCatNoBySkus($skus);

        foreach ($list as $key => $item) {
            if ($item['sku'] && isset($product_names[$item['sku']])) {
                $list[$key]['product_name'] = $product_names[$item['sku']]['product_name'];
                $list[$key]['material_cat_no'] = $product_names[$item['sku']]['material_cat_no'];
            }
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setConstPrice(&$list) {
        if ($list) {

            $skus = [];
            foreach ($list as $key => $val) {
                $skus[] = $val['sku'];
            }

            $goods_cost_price_model = new GoodsCostPriceModel();
            $stockcostprices = $goods_cost_price_model->getCostPricesBySkus($skus);

            foreach ($list as $key => $val) {

                if ($val['sku'] && isset($stockcostprices[$val['sku']])) {
                    if (isset($stockcostprices[$val['sku']])) {
                        $price = '';
                        foreach ($stockcostprices[$val['sku']] as $stockcostprice) {
                            if ($stockcostprice['price'] && $stockcostprice['max_price']) {
                                $price = $stockcostprice['price'] . '-' . $stockcostprice['max_price'];
                            } elseif ($stockcostprice['price']) {
                                $price = $stockcostprice['price'];
                            } else {
                                $price = '';
                            }
                        }
                        $val['costprices'] = $price;
                    }
                } else {
                    $val['costprices'] = '';
                }
                $list[$key] = $val;
            }
        }
    }

    private function date_diff($datetime1, $datetime2) {
        $date_time2 = strtotime($datetime2);
        $date_time1 = strtotime($datetime1);
        $interval = ($date_time1 - $date_time2) / 3600;
        return $interval;
    }

    private function _setquoted_time(&$list) {
        foreach ($list as $key => $item) {
            $list[$key]['inflow_time'] = !empty($item['inflow_time']) ? $item['inflow_time'] : $item['inflow_time_out'];

            if ($item['qs_time']) {
                $list[$key]['quoted_time'] = $this->date_diff($item['qs_time'], $item['created_at']);
            } else {
                $list[$key]['quoted_time'] = $this->date_diff(date('Y-m-d H:i:s'), $item['created_at']);
            }
        }
    }

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setOilFlag(&$arr) {
        if ($arr) {
            $oil_flags = [
                '石油专用管材',
                '钻修井设备',
                '固井酸化压裂设备',
                '采油集输设备',
                '石油专用工具',
                '石油专用仪器仪表',
                '油田化学材料',];
            $not_oil_flags = [
                '通用机械设备',
                '劳动防护用品',
                '消防、医疗产品',
                '电力电工设备',
                '橡塑产品',
                '钢材',
                '包装物',
                '杂品',];

            foreach ($arr as $key => $val) {
                if ($val['category'] && in_array($val['category'], $oil_flags)) {
                    $val['oil_flag'] = '油气';
                } elseif ($val['category'] && in_array($val['category'], $not_oil_flags)) {
                    $val['oil_flag'] = '非油气';
                } else {
                    $val['oil_flag'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setMaterialCat(&$arr, $lang) {
        if ($arr) {
            $material_cat_model = new MaterialCatModel();
            $catnos = [];
            foreach ($arr as $key => $val) {
                $catnos[] = $val['material_cat_no'];
            }
            $catnames = $material_cat_model->getNameByCatNos($catnos, $lang);
            foreach ($arr as $key => $val) {
                if ($val['category'] && isset($catnames[$val['material_cat_no']])) {
                    $val['material_cat_name'] = $catnames[$val['material_cat_no']];
                } else {
                    $val['material_cat_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
