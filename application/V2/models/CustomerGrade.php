<?php
/**
 *
 */
class CustomerGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'customer_grade';

    public function __construct() {
        parent::__construct();
    }
    public function buyerGradeList1($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $info=$this
        ->field('id,type,amount,position,year_keep,re_purchase,credit_grade,purchase,enterprise,income,scale')
        ->where(array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N'))
        ->select();
        $arr=[];
        foreach($info as $k => $v){
            $arr[$k]['id']=$v['id'];  //
            $arr[$k]['type']=$v['type'];  //
            $arr[$k]['customer_grade']=$v['amount'];  //客户等级
            $arr[$k]['created_name']=$v['position'];    //创建人
            $arr[$k]['created_at']=$v['year_keep'];   //创建时间
            $arr[$k]['updated_at']=$v['re_purchase']; //更新时间
            $arr[$k]['customer_admin']=$v['credit_grade'];    //1客户管理员
            $arr[$k]['checked_at']=$v['purchase'];    //1审核时间
            $arr[$k]['status']=$v['enterprise'];  //1状态
        }
        return $arr;
    }
    public function buyerGradeList($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $field='';
        $fieldArr=array(
            'id',   //
            'type',   //
            'customer_grade',   //客户等级
            'created_by',   //创建人
            'created_at',   //创建时间
            'updated_at',   //更新时间
            'checked_by',   //客户管理员
            'checked_at',   //审核时间
            'status',   //状态
        );
        foreach($fieldArr as $k => $v){
            $field.='grade.'.$v.',';
        }
        $field.="(select name from erui_sys.employee where id=grade.created_by and deleted_flag='N') as  created_name";
        $field.=",(select name from erui_sys.employee where id=grade.checked_by and deleted_flag='N') as customer_admin";
        $cond=array(
            'grade.buyer_id'=>$data['buyer_id'],
            'grade.deleted_flag'=>'N'
        );
        if(in_array('客户管理员',$data['role'])){
            $admin=1; //客户管理员角色
            $cond['grade.status']=[
                    'in',
                    [1,2,4]
                ];
        }
//        print_r($cond);die;
        $info=$this->alias('grade')
            ->field($field)
            ->where($cond)
            ->select();
//        $check=false;   //审核
//        $show=false;   //查看
//        $edit=false;   //编辑
//        $delete=false;   //删除
//        $submit=false;   //提交
        foreach($info as $k => &$v){
            unset($v['created_by']);
            if($v['status']==0){
                $v['status']='新建';
                $v['check']=false;  $v['show']=true;    $v['edit']=true;    $v['delete']=true;  $v['submit']=true;
            }else if($v['status']==1){
                $v['status']='待审核';
                if($admin===1){
                    $v['check']=true;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }else{
                    $v['check']=false;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }
            }else if($v['status']==2){
                $v['status']='已通过';
                if($admin===1){
                    $v['check']=false;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }else{
                    $v['check']=false;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }
            }else if($v['status']==4){
                $v['status']='未通过';
                if($admin===1){
                    $v['check']=false;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }else{
                    $v['check']=false;  $v['show']=true;    $v['edit']=false;    $v['delete']=false;  $v['submit']=false;
                }
            }
//            if($admin===1){
//                if($v['status']==1){
//                    $v['status']='待审核';
//                }else if($v['status']==2){
//                    $v['status']='已通过';
//                }else if($v['status']==4){
//                    $v['status']='未通过';
//                }
//                $check=true;   $show=true;    $edit=false;    $delete=false;  $submit=false;
//            }else{
//                if($v['status']==0){
//                    $v['status']='新建';
//                    $check=true;   $show=true;    $edit=true;    $delete=true;  $submit=true;
//                }else if($v['status']==1){
//                    $v['status']='待审核';
//                    $check=false;   $show=true;    $edit=true;    $delete=true;  $submit=false;
//                }else if($v['status']==2){
//                    $v['status']='已通过';
//                }else if($v['status']==4){
//                    $v['status']='未通过';
//                }
//            }
//
//
//
//
        }
        return $info;
    }
    private function oldBuyer($data){
        $field=array(
            'buyer_id',
            'amount', //客户历史成单金额
            'amount_score',
            'position', //易瑞产品采购量占客户总需求量地位
            'position_score',
            'year_keep',   //连续N年及以上履约状况良好
            'keep_score',
            're_purchase',   //年复购次数
            're_score',
            'final_score',  //综合分值
            'customer_grade',   //客户等级
            'flag'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>1];
        foreach($field as $k => $v){
            if(!empty($data[$v])){
                $arr[$v]=$data[$v];
            }else{
                $arr[$v]='';
            }
        }
        return $arr;
    }
    private function newBuyer($data){
        $arr['type']=0;
        $field=array(
            'buyer_id',
            'credit_grade', //客户资信等级
            'credit_score',
            'purchase', //零配件年采购额
            'purchase_score',
            'enterprise',   //企业性质
            'enterprise_score',
            'income',   //营业收入
            'income_score',
            'scale',    //资产规模
            'scale_score',
            'final_score',  //综合分值
            'customer_grade',   //客户等级
            'flag'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>0];
        foreach($field as $k => $v){
            if(!empty($data[$v])){
                $arr[$v]=$data[$v];
            }else{
                $arr[$v]='';
            }
        }
        return $arr;
    }
    public function addGrade($data){
        if($data['type']==1){
            $arr=$this->oldBuyer($data);    //老客户
        }else{
            $arr=$this->newBuyer($data);    //潜在客户
        }
        $arr['status']=$data['flag']==1?1:0;
        $arr['created_by']=$data['created_by'];
        $arr['created_at']=date('Y-m-d H:i:s');
        $res=$this->add($arr);
        if($res){
            return true;
        }
        return false;
    }
    public function saveGrade($data){
        if(empty($data['id'])){
            return false;
        }
        if($data['type']==1){
            $arr=$this->oldBuyer($data);    //老客户
        }else{
            $arr=$this->newBuyer($data);    //潜在客户
        }
        unset($arr['type']);
        unset($arr['buyer_id']);
        unset($arr['flag']);
        unset($arr['status']);
        $arr['updated_by']=$data['created_by'];
        $arr['updated_at']=date('Y-m-d H:i:s');
        $this->where(array('id'=>$data['id'],'deleted_flag'=>'N'))->save($arr);
        return true;
    }
    public function delGrade($data){
        if(empty($data['id'])){
            return false;
        }
        $cond=array('id'=>$data['id']);
        $save=array(
            'deleted_flag'=>'Y',
            'deleted_by'=>$data['created_by'],
            'deleted_at'=>date('Y-m-d H:i:s')
        );
        $res=$this->where($cond)->save($save);
        if($res){
            return true;
        }
        return false;
    }
    public function checkedGrade($data){
        if(empty($data['status']) || empty($data['id'])){
            return false;
        }
        if($data['status']==2){    //0,新建;1,待审核; 2,审核通过
            $arr['status']=2;
        }else{
            $arr['status']=4;
        }
        $arr['checked_by']=$data['created_by'];
        $arr['checked_at']=date('Y-m-d H:i:s');
        $cond=array(
            'id'=>$data['id'],
            'deleted_flag'=>'N',
            'status'=>1,
        );
        $res=$this->where($cond)->save($arr);
        if($res){
            return true;
        }
    }
}
