<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 * @author  zhongyg
 * @date    2017-9-12 13:09:26
 * @version V2.0
 * @desc
 */
class OrderModel extends PublicModel {

    //put your code here
    protected $tableName = 'order';
    protected $dbName = 'erui_order'; //数据库名称

    const SHOW_STATUS_UNCONFIRM = 'UNCONFIRM'; // 订单展示状态CONFIRM待确认
    const SHOW_STATUS_GOING = 'GOING'; // 订单展示状态  GOING.进行中
    const SHOW_STATUS_COMPLETED = 'COMPLETED'; // 订单展示状态 COMPLETED.已完成
    const PAY_STATUS_UNCONFIRM = 'UNPAY'; //支付状态 UNPAY未付款
    const PAY_STATUS_GOING = 'PARTPAY'; //支付状态 PARTPAY部分付款
    const PAY_STATUS_COMPLETED = 'PAY'; //支付状态  PAY已付款

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    /* 获取订单状态
     * @param int $show_status // 订单展示状态CONFIRM待确认 GOING.进行中  COMPLETED.已完成
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getShowStatus($show_status) {
        switch ($show_status) {

            case 'UNCONFIRM':
                return '待确认';

            case 'GOING':
                return '进行中';

            case 'COMPLETED':
                return '已完成';
                
            case 'OUTGOING':
                return '已出库';
            
            case 'DISPATCHED':
                return '已发运';

            default :return'待确认';
        }
    }

    /* 获取订单付款状态
     * @param int $status // 状态 支付状态 UNPAY未付款 PARTPAY部分付款  PAY已付款
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getPayStatus($pay_status) {
        switch ($pay_status) {
            case 'UNPAY':
                return '未收款';

            case 'PARTPAY':
                return '部分收款';

            case 'PAY':
                return '收款完成';

            default :return'未付款';
        }
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id, $lang = 'zh') {
        $field = 'id,order_no,po_no,execute_no,contract_date,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,(select trans_mode from erui_dict.trans_mode as t where t.bn=trans_mode_bn and t.lang=\'' . $lang . '\') as trans_mode';
        $field .= ',from_country_bn,(select name from erui_dict.country as t where t.bn=from_country_bn and t.lang=\'' . $lang . '\') as from_country';
        $field .= ',to_country_bn,(select name from erui_dict.country as t where t.bn=to_country_bn and t.lang=\'' . $lang . '\') as to_country';
        $field .= ',from_port_bn,(select name from erui_dict.port as t where t.bn=from_port_bn and t.lang=\'' . $lang . '\') as from_port';
        $field .= ',to_port_bn,(select name from erui_dict.port as t where t.bn=to_port_bn and t.lang=\',buyer_id' . $lang . '\') as to_port,quality,distributed';
        return $this->field($field)
                        ->where(['id' => $order_id])->find();
    }

    /* 查询条件
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _getCondition($condition) {

        $where = [];
        $where['order.deleted_flag'] = 'N';
        $this->_getValue($where, $condition, 'order_no'); //平台订单号
        $this->_getValue($where, $condition, 'po_no'); //po编号
        $this->_getValue($where, $condition, 'execute_no', 'like'); //执行编号
        if (isset($condition['show_status']) && $condition['show_status']) {
            if (in_array($condition['show_status'], ['UNCONFIRM', 'GOING', 'COMPLETED', 'OUTGOING', 'DISPATCHED'])) {
                $where['show_status'] = $condition['show_status'];
            }
        }
        if (isset($condition['pay_status']) && $condition['pay_status']) {
            if (in_array($condition['pay_status'], ['UNPAY', 'PARTPAY', 'PAY'])) {
                $where['pay_status'] = $condition['pay_status'];
            }
        }
        if (isset($condition['agent_id']) && $condition['agent_id']) {
            $where['agent_id'] = $condition['agent_id'];
        }


        $this->_getValue($where, $condition, 'contract_date', 'between'); //支付状态

        if (isset($condition['buyer_no']) && $condition['buyer_no']) {
            $where['buyer.buyer_no'] = $condition['buyer_no'];
        }
        if (isset($condition['name']) && $condition['name']) {
            $where['buyer.name'] = $condition['name'];
        }

        return $where;
    }

    /* 获取订单列表
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getList($condition) {

        $where = $this->_getCondition($condition);
        list($start_no, $pagesize) = $this->_getPage($condition);
        return $this
                        ->field('order.id,order.source,is_reply,order_no,po_no,execute_no,contract_date, buyer_id,order.status,show_status,pay_status,buyer.name as buyer_id_name,buyer.buyer_no')
                        ->join('`erui_buyer`.`buyer`  on buyer.id=order.buyer_id', 'left')
                        ->where($where)->limit($start_no, $pagesize)->order('order.created_at desc')->select();
    }

    /* 获取订单数量
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->join('`erui_buyer`.`buyer`  on buyer.id=order.buyer_id', 'left')->where($where)->count();
    }

    /**
     * @param $buyer_id
     * 获取订单数，金额-统计
     * wangs
     */
    public function statisOrder($buyer_id){
//        $sql="select level_at,expiry_at from erui_buyer.buyer WHERE id=$buyer_id AND deleted_flag='N' AND is_build=1 ";
//        $buyer=$this->query($sql);
//        $level_at=$buyer[0]['level_at'];
//        $expiry_at=$buyer[0]['expiry_at'];
//        $date=date('Y-m-d');    //今天
//        $prev=(substr($date,0,4)-1).substr($date,4,10); //一年前的今天
//        $sql = "select count(id) as `count`,FORMAT(sum(amount),2) as account,min(amount) as `min`,max(amount) as `max` from `erui_order`.`order` where buyer_id=$buyer_id";
//        $sql = "select amount,currency_bn from `erui_order`.`order` where buyer_id=$buyer_id AND deleted_flag='N'";
        $sqlOrder="select `order`.id AS order_id,`order`.amount,`order`.currency_bn from erui_order.order `order`";
//        $sqlOrder.=" left join erui_order.order_log order_log";
//        $sqlOrder.=" on order.id=order_log.order_id";
        $sqlOrder.=" WHERE `order`.buyer_id=$buyer_id";
//        $sqlOrder.=" AND `order`.show_status='GOING'";
        $sqlOrder.=" AND `order`.deleted_flag='N'";
//        $sqlOrder.=" AND order_log.deleted_flag='N'";
//        if(!empty($level_at) && !empty($expiry_at)){    //会员有效期内的回款
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') >=  DATE_FORMAT('$level_at','%Y-%m-%d') ";
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') <=  DATE_FORMAT('$expiry_at','%Y-%m-%d') ";
//        }else{
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') >=  DATE_FORMAT('$prev','%Y-%m-%d') ";
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') <=  DATE_FORMAT('$date','%Y-%m-%d') ";
//        }
        $order = $this->query($sqlOrder);
        //订单已完成
//        $sqlOrdero="select `order`.id as order_id,`order`.amount,`order`.currency_bn from erui_order.order `order`";
//        $sqlOrdero.=" WHERE `order`.buyer_id=$buyer_id";
//        $sqlOrdero.=" AND `order`.show_status='COMPLETED'";
//        $sqlOrdero.=" AND `order`.deleted_flag='N'";
//        if(!empty($level_at) && !empty($expiry_at)){    //会员有效期内的回款
//            $sqlOrdero.=" AND DATE_FORMAT(`order`.complete_at,'%Y-%m-%d') >=  DATE_FORMAT('$level_at','%Y-%m-%d') ";
//            $sqlOrdero.=" AND DATE_FORMAT(`order`.complete_at,'%Y-%m-%d') <=  DATE_FORMAT('$expiry_at','%Y-%m-%d') ";
//        }else{
//            $sqlOrdero.=" AND DATE_FORMAT(`order`.complete_at,'%Y-%m-%d') >=  DATE_FORMAT('$prev','%Y-%m-%d') ";
//            $sqlOrdero.=" AND DATE_FORMAT(`order`.complete_at,'%Y-%m-%d') <=  DATE_FORMAT('$date','%Y-%m-%d') ";
//        }
//        $ordero = $this->query($sqlOrdero);
//        $order=array_merge($orderi,$ordero);
        $orderArr=$this->sumAccountAtatis($order);  //order
        $orderAmount=$orderArr['amount'];   //order arr
        $orderCount=count($orderArr['count']); //order count

        $sqlNewOrder="select `order_account`.order_id,currency_bn,order_account.money as amount from erui_new_order.order `order`";
        $sqlNewOrder.=" left join erui_new_order.order_account order_account";
        $sqlNewOrder.=" on `order`.id=order_account.order_id";
        $sqlNewOrder.=" where crm_code=(SELECT buyer_code from erui_buyer.buyer where id=$buyer_id)";
        $sqlNewOrder.=" and (`order`.status=4 or `order`.status=3)";
        $sqlNewOrder.=" and `order`.delete_flag=0";
        $sqlNewOrder.=" and order_account.del_yn=1";
//        if(!empty($level_at) && !empty($expiry_at)){    //会员有效期内的回款
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') >=  DATE_FORMAT('$level_at','%Y-%m-%d') ";
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') <=  DATE_FORMAT('$expiry_at','%Y-%m-%d') ";
//        }else{
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') >=  DATE_FORMAT('$prev','%Y-%m-%d') ";
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') <=  DATE_FORMAT('$date','%Y-%m-%d') ";
//        }
        $newOrder=$this->query($sqlNewOrder);
        $orderNewArr=$this->sumAccountAtatis($newOrder);    //newOrder
        $orderNewAmount=$orderNewArr['amount'];   //newOrder arr
        $orderNewCount=count($orderNewArr['count']); //newOrder count

        $mergeAmount=array_merge($orderAmount,$orderNewAmount); //总订单金额arr
        sort($mergeAmount);
        $count=$orderCount+$orderNewCount;  //总订单个数
        $sum=array_sum($mergeAmount);   //总订单金额
        //返回数据
        if($count==0){
            $arr=array(
                'count'=>0,
                'account'=>0,
                'min'=>0,
                'max'=>0
            );
        }elseif($count==1){
            $arr=array(
                'count'=>1,
                'account'=>$sum,
                'min'=>0,
                'max'=>$sum
            );
        }else{
            $arr=array(
                'count'=>$count,
                'account'=>$sum,
                'min'=>reset($mergeAmount),
                'max'=>end($mergeAmount)
            );
        }
        $data=array(
            'count'=>$arr['count'],
            'account'=>sprintf("%.2f",$arr['account']),
            'min'=>sprintf("%.2f",$arr['min']),
            'max'=>sprintf("%.2f",$arr['max']),
        );
        return $data;
    }
    //计算统计订单金额,货币=USD
    public function sumAccountAtatis($order=[]){
        $count=array();
        $arr=[];
        $val=0;
        foreach($order as $k => $v){
            if($v['currency_bn']=='USD'){   //一次交易50万=高级
                $val=$v['amount'];
            }elseif($v['currency_bn']=='CNY'){
                $val=$v['amount']*0.1583;
            }elseif($v['currency_bn']=='EUR'){
                $val=$v['amount']*1.2314;
            }elseif($v['currency_bn']=='CAD'){
                $val=$v['amount']*0.7918;
            }elseif($v['currency_bn']=='RUB'){
                $val=$v['amount']*0.01785;
            }
            $arr[]=$val;
            $count[]=$v['order_id'];
        }
        $data['amount']=$arr;
        $data['count']=array_flip(array_flip($count));
        return $data;
    }
    /**
     * 客户首页订单，金额展示数据
     * wangs
     */
    public function getOrderStatis($ids){
        $arr = [];
        foreach($ids as $k => $v){
            $arr[$k]=$this->statisOrder($v);
        }
        return $arr;
    }
    //会员自动升级start-------------------------------------------------------------------------------------
    public function autoUpgradeByNewOrder($at,$buyer_id,$buyer_code,$orderArr){
//        $level_at_prev=$at['level_at_prev'];
//        $expiry_at=$at['expiry_at'];
//        $date=$at['date'];
//        $prev=$at['prev'];

//        //new_order=当年订单签约日期+订单执行中或完成
//        $sqlNewOrder="select `order`.id,crm_code,currency_bn,order_account.money as amount, order_account.payment_date as create_time from erui_new_order.order `order`";
//        $sqlNewOrder.=" left join erui_new_order.order_account order_account";
//        $sqlNewOrder.=" on `order`.id=order_account.order_id";
//        $sqlNewOrder.=" where `order`.crm_code='$buyer_code'";
////        $sqlNewOrder.=" and `order`.status=3";
//        $sqlNewOrder.=" and `order`.delete_flag=0";
//        $sqlNewOrder.=" and order_account.del_yn=1";
//        if(!empty($level_at) && !empty($expiry_at)){    //会员有效期内的回款
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') >=  DATE_FORMAT('$level_at_prev','%Y-%m-%d') ";
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') <=  DATE_FORMAT('$expiry_at','%Y-%m-%d') ";
//        }else{
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') >=  DATE_FORMAT('$prev','%Y-%m-%d') ";
//            $sqlNewOrder.=" AND DATE_FORMAT(order_account.payment_date,'%Y-%m-%d') <=  DATE_FORMAT('$date','%Y-%m-%d') ";
//        }
//        $sqlNewOrder.=" order by order_account.payment_date";
        $expiry_at=$at['expiry_at'];
        $date=$at['date'];
        $sqlNewOrder="select total_price as amount,currency_bn,signing_date as create_time from erui_new_order.order `order`";
        $sqlNewOrder.=" where crm_code='$buyer_code' and signing_date>='2018-01-01' and signing_date<='$date' and delete_flag=0";
        $sqlNewOrder.=" order by signing_date";
        $newOrderArr=$this->query($sqlNewOrder);   //订单所有回款
        $orderArr=$this->removeNullOrder($orderArr);
        $newOrderArr=$this->removeNullOrder($newOrderArr);
        $orderArr=array_values($orderArr);
        $newOrderArr=array_values($newOrderArr);
        //验证升级
        if(empty($newOrderArr) && empty($orderArr)){   //订单为空,无交易
            $this->autoUpgrade($buyer_id,null,null);
            return 'void';
        }
        if(empty($newOrderArr) && !empty($orderArr)){  //新订单为空,老订单有数据
            $orderRes=$this->sumAmount($orderArr);
            if(!empty($orderRes['time'])){    //高级
                $time=substr($orderRes['time'],0,10);
                $this->autoUpgrade($buyer_id,53,$time);
                return 'senior';
            }else{
                $time=substr($orderArr[0]['create_time'],0,10);
                $this->autoUpgrade($buyer_id,52,$time);
                return 'general';
            }
        }
        //newOrder有交易
        if(empty($orderArr)){
            $orderNew=$this->sumAmount($newOrderArr);
            if(!empty($orderNew['time'])){  //高级
                $time=substr($orderNew['time'],0,10);
                $this->autoUpgrade($buyer_id,53,$time);
                return 'senior';
            }else{
                $time=substr($newOrderArr[0]['create_time'],0,10);
                $this->autoUpgrade($buyer_id,52,$time);
                return 'general';
            }
        }
        $merge=array_merge($orderArr,$newOrderArr);
        $arrSort = array();
        foreach($merge AS $key => $value){
            foreach($value AS $k=>$v){
                $arrSort[$k][$key] = $v;
            }
        }
        array_multisort($arrSort['create_time'], SORT_ASC, $merge);
        //两订单总计
        $total=$this->sumAmount($merge);
        if(!empty($total['time'])){  //高级
            $time=substr($total['time'],0,10);
            $this->autoUpgrade($buyer_id,53,$time);
            return 'senior';
        }else{
            $time=substr($merge[0]['create_time'],0,10);
            $this->autoUpgrade($buyer_id,52,$time);
            return 'general';
        }
    }
    //会员自动升级,去除订单为0的空值-wangs
    public function removeNullOrder($order=[]){
        foreach($order as $k => $v){
            if($v['amount']==0 || empty($v['currency_bn'])){
                unset($order[$k]);
            }
        }
        return $order;
    }
    /**
     * 客户会员自动升级-wangs
     */
    public function autoUpgradeByOrder($data){
        set_time_limit(0);
        if(empty($data['buyer_id']) && empty($data['crm_code']) && empty($data['order_id'])){
            return 'param';
        }
        if(!empty($data['order_id'])){
            $order_id=$data['order_id'];
            $sql="select buyer_id from erui_order.order where id=$order_id";
            $orderOld=$this->query($sql);
            $buyer_id=$orderOld[0]['buyer_id'];
            $data['buyer_id']=$buyer_id;
            if(empty($data['buyer_id'])){
                return 'param';
            }
        }
        $buyer=new BuyerModel();
        if(!empty($data['buyer_id'])){  //order-buyer_id
            $info=$buyer->field('id,buyer_code,buyer_level,level_at,expiry_at')->where(array('id'=>$data['buyer_id'],'deleted_flag'=>'N'))->find();
        }
        if(!empty($data['crm_code'])){  //newOrder-crm_code
            $info=$buyer->field('id,buyer_code,buyer_level,level_at,expiry_at')->where(array('buyer_code'=>$data['crm_code'],'deleted_flag'=>'N'))->find();
        }
        if(empty($info)){
            return 'void';  //no buyer
        }
        $buyer_id=$info['id'];
        $buyer_code=$info['buyer_code'];
        $level_at=$info['level_at'];    //会员定级日期
        $expiry_at=$info['expiry_at'];  //会员过期日期
        $date=date('Y-m-d');    //今天
        //时间参数
        $at['expiry_at']=$expiry_at;
        $at['date']=$date;

        $sqlOrder="select `order`.amount,`order`.currency_bn,`order`.execute_date as create_time from erui_order.order `order`";
        $sqlOrder.=" where buyer_id=$buyer_id and execute_date>='2018-01-01' and execute_date<='$date' and
        deleted_flag='N'";
        $sqlOrder.=" order by execute_date";
        $order = $this->query($sqlOrder);
//        //会员等级有效期内的回款
//        $sqlOrder="select order_log.amount,`order`.currency_bn,order_log.log_at as create_time from erui_order.order `order`";
//        $sqlOrder.=" left join erui_order.order_log order_log";
//        $sqlOrder.=" on order.id=order_log.order_id";
//        $sqlOrder.=" WHERE `order`.buyer_id=$buyer_id";
////        $sqlOrder.=" AND `order`.show_status='GOING'";  //订单经行中
//        $sqlOrder.=" AND `order`.deleted_flag='N'";
//        $sqlOrder.=" AND order_log.deleted_flag='N'";
//        if(!empty($level_at) && !empty($expiry_at)){    //会员有效期内的回款
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') >=  DATE_FORMAT('$level_at_prev','%Y-%m-%d') ";
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') <=  DATE_FORMAT('$expiry_at','%Y-%m-%d') ";
//        }else{
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') >=  DATE_FORMAT('$prev','%Y-%m-%d') ";
//            $sqlOrder.=" AND DATE_FORMAT(order_log.log_at,'%Y-%m-%d') <=  DATE_FORMAT('$date','%Y-%m-%d') ";
//        }
//        $sqlOrder.=" order by order_log.log_at";
//        $order = $this->query($sqlOrder);
        //erui_order
//        if(!empty($order)){
//            $orderRes=$this->sumAmount($order);
//            if(!empty($orderRes['time'])){    //高级
//                $time=substr($orderRes['time'],0,10);
//                if($time>=$prev){
//                    $this->autoUpgrade($buyer_id,53,$time);
//                    return 'senior';
//                }
//            }else{
//                $time=substr(reset($order)['create_time'],0,10);
//                $orderRes['time']=$time;    //普通
//            }
//        }else{
//            $res=$this->autoUpgradeByNewOrder($at,$buyer_id,$buyer_code,$order); //null
//            return $res;    //senior or general or null
//        }
        //erui_new_order
        $res=$this->autoUpgradeByNewOrder($at,$buyer_id,$buyer_code,$order);
        return $res;    //senior or general
    }
    //实现自动升级
    public function autoUpgrade($buyer_id,$buyer_level,$time){
        $cond=array(
            'id'=>$buyer_id,
            'deleted_flag'=>'N'
        );
        if(!empty($time)){
            $expiry_at=(substr($time,0,4)+1).substr($time,4);
        }else{
            $expiry_at=null;
        }
        $data=array(
            'buyer_level'=>$buyer_level,
            'level_at'=>$time,
            'expiry_at'=>$expiry_at
        );
        $buyer=new BuyerModel();
        return $buyer->where($cond)->save($data);
    }
    //计算多订单总金额-wnags
    public function sumAmount($amount){
        $arr=array();
        $time=array();
        $num=0;
        $one=0;
        $oneArr=array();
        foreach($amount as $k => $v){
            if(empty($v['currency_bn'])){
                $v['currency_bn']='USD';
            }
            if($v['currency_bn']=='USD'){   //一次交易50万=高级
                $one=$v['amount'];
            }elseif($v['currency_bn']=='CNY'){
                $one=$v['amount']*0.1583;
            }elseif($v['currency_bn']=='EUR'){
                $one=$v['amount']*1.2314;
            }elseif($v['currency_bn']=='CAD'){
                $one=$v['amount']*0.7918;
            }elseif($v['currency_bn']=='RUB'){
                $one=$v['amount']*0.01785;
            }
            if($one>=500000){
                $oneArr[]=$v['amount']; //50万以上的集合
                $time[]=$v['create_time'];
            }
            if($v['currency_bn'] == 'USD'){ //累计100万=高级
                $num+=$v['amount'];
                if($num>=1000000){
                    $time[]=$v['create_time'];
                }
            }elseif($v['currency_bn']=='CNY'){
                $num+=$v['amount']*0.1583;
                if($num>=1000000){
                    $time[]=$v['create_time'];
                }
            }elseif($v['currency_bn']=='EUR'){
                $num+=$v['amount']*1.2314;
                if($num>=1000000){
                    $time[]=$v['create_time'];
                }
            }elseif($v['currency_bn']=='CAD'){
                $num+=$v['amount']*0.7918;
                if($num>=1000000){
                    $time[]=$v['create_time'];
                }
            }elseif($v['currency_bn']=='RUB'){
                $num+=$v['amount']*0.01785;
                if($num>=1000000){
                    $time[]=$v['create_time'];
                }
            }
        }
        $arr['num']=$num;   //总交易
        $arr['Single']=$oneArr;   //50万以上交易集合
        $arr['time']=reset($time);
        return $arr;
    }
    //会员自动升级end---------------------------------------------------------------------------------------
}
