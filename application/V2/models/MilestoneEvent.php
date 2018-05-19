<?php

/**
 * 里程碑事件
 * Created by PhpStorm.
 * 王帅
 */
class MilestoneEventModel extends Model {

    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'milestone_event';
    public function percentMilestoneEvent($data){
        $field=array(
            'event_time', //里程碑时间
            'event_name', //里程碑名称
            'event_content', //里程碑事件内容
            'event_contact' //里程碑负责人
        );
        $cond=array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N');
        $info=$this->field($field)->where($cond)->find();
        if(!empty($info)){
            foreach($info as $k => &$v){
                if(empty($v) || $v==0){
                    $v='';
                }
            }
        }else{
            $info=[];
            foreach($field as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
    public function editMilestoneEvent($data){
        if(!empty($data['event_time'])){
            $data['event_time']=substr($data['event_time'],0,10);
        }
        $arr=array(
            'event_time'=>isset($data['event_time'])?$data['event_time']:null, //时间date
            'event_name'=>isset($data['event_name'])?$data['event_name']:null, //事件名称project
            'event_content'=>isset($data['event_content'])?$data['event_content']:null, //事件内容Content
            'event_contact'=>isset($data['event_contact'])?$data['event_contact']:null, //该事件KERUI/ERUI负责人KERUI/ERUI
        );
        $arr['created_by']=$data['created_by'];
        $arr['created_at']=date('Y-m-d H:i:s');
        if(empty($data['id'])){
            $arr['buyer_id']=$data['buyer_id'];
            $this->add($arr);
        }else{
            $this->where(array('id'=>$data['id']))->save($arr);
        }
        return true;
    }
    //查看
    public function showMilestoneEvent($data){
        $fieldStr='id,buyer_id,event_name,event_content,event_contact,event_time';
        $info=$this->field($fieldStr)->where(array('id'=>$data['id'],'deleted_flag'=>'N'))->find();
        if(!empty($info)){
            $info['event_time']=substr($info['event_time'],0,10);
        }
        return $info;
    }
    public function MilestoneEventList($data){
        $fieldArr=array(
            'id', //事件名称project
            'buyer_id', //事件名称project
            'event_name', //事件名称project
            'event_content', //事件内容Content
            'event_contact', //该事件KERUI/ERUI负责人KERUI/ERUI
            'event_time' //时间date
        );
        $fieldStr=implode(',',$fieldArr);
        $info=$this->field($fieldStr)
            ->where(array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N'))
            ->order('event_time desc')
            ->select();
        if(empty($info)){
            return [];
        }
        foreach($info as $k => &$v){
            if(!empty($v['event_time'])){
                $v['event_time']=substr($v['event_time'],0,10);
            }else{
                $v['event_time']='';
            }
        }
        return $info;
    }
    public function delMilestoneEvent($data){
        if(empty($data['id'])){
            return false;
        }
        $this->where(array('id'=>$data['id']))->save(array('deleted_flag'=>'Y'));
        return true;
    }
    /**
     * @param $event 事件数据arr
     * @param $buyer_id 客户id
     * @param $created_by   创建人
     */
    public function createMilestoneEvent($event,$buyer_id,$created_by){
        $arr=array(
            'event_name', //事件名称project
            'event_content', //事件内容Content
            'event_contact', //该事件KERUI/ERUI负责人KERUI/ERUI
            'event_time', //时间date
        );
        $flag=true;
        foreach($event as $key => $value){
            if(empty($value['event_time'])){
                $value['event_time']=null;
            }
            $value['buyer_id']=$buyer_id;
            $value['created_by']=$created_by;
            $value['created_at']=date('Y-m-d H:i:s');
            $res=$this->add($value);
            if(!$res && $flag){
                $flag=false;
            }
        }
        return $flag;
    }
    public function showMilestoneEvent2($buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $info=$this->where($cond)->select();
        return $info;
    }
    public function updateMilestoneEvent($event,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $v){
            $arrId[]=$v['id'];
        }
        $inputId=array();
        foreach($event as $v){
            if(empty($v['event_time'])){
                $v['event_time']=null;
            }
            $v['buyer_id']=$buyer_id;
            $v['created_by']=$created_by;
            $v['created_at']=date('Y-m-d H:i:s');
            if(!empty($v['id'])){
                $inputId[]=$v['id'];
                $this->where(array('id'=>$v['id']))->save($v);
            }else{
                unset($v['id']);
                $this->add($v);
            }
        }
        $diff=array_diff($arrId,$inputId);
        if(!empty($diff)){
            $strId=implode(',',$diff);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        return true;
    }
}
