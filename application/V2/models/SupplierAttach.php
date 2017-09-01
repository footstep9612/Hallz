<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class SupplierAttachModel extends PublicModel
{

    protected $tableName = 'supplier_attach';
    protected $dbName = 'erui2_supplier'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }
    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author
     */
    public function update_data($data, $where)
    {

        if (isset($create['attach_url'])) {
            $arr['attach_url'] = $create['attach_url'];
        }
        if (isset($create['attach_name'])) {
            $arr['attach_name'] = $create['attach_name'];
        }
        if (isset($create['attach_group'])) {
            $arr['attach_group'] = $create['attach_group'];
        }
        if (!empty($where)) {
            $info = $this->where($where)->find();
            if(!$info){
                $arr['supplier_id']=$where['supplier_id'];
                if (isset($create['created_by'])) {
                    $arr['created_by'] = $create['created_by'];
                }
                $arr['created_at']= date("Y-m-d H:i:s");
                $this->create_data($arr);
            }else{
                return $this->where($where)->save($arr);
            }
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author
     */
    public function create_data($create = [])
    {
        if (isset($create['supplier_id'])) {
            $arr['supplier_id'] = $create['supplier_id'];
        }
        if (isset($create['attach_url'])) {
            $arr['attach_url'] = $create['attach_url'];
        }
        if (isset($create['attach_group'])) {
            $arr['attach_group'] = $create['attach_group'];
        }
        if (isset($create['attach_name'])) {
            $arr['attach_name'] = $create['attach_name'];
        }
        if (isset($create['created_by'])) {
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at']= date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
    public function deleteall($create = [])
    {
        if ($create) {
            return $data = $this->where($create)->delete();
        }
    }
    public function info($data = [])
    {
        if(!empty($data['supplier_id'])){
            $arr['supplier_id'] =$data['supplier_id'];

            if(!empty($data['attach_group'])){
                $arr['attach_group'] =$data['attach_group'];
            }
            $arr['deleted_flag'] ='N';
            $row = $this->field("supplier_id,id,attach_type,attach_group,attach_name as name,attach_code,attach_url as url")->where($arr)
                ->select();
            return $row;
        }else{
            return false;
        }
    }

}


