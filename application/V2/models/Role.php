<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class RoleModel extends PublicModel {

    //put your code here
    protected $tableName = 'role';
    Protected $autoCheckFields = true;
    protected $table_name ='role';
    protected $permtable ='func_perm';
    protected $rolepermtable ='role_access_perm';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

     public function getcount($data,$order='id desc'){
         $count =  $this->where($data)->count('id');
         return $count;
     }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='id desc') {
        if(!empty($limit)){
            $res= $this->field('role.id,role.name,role.name_en,role.remarks,role.created_by,emby.name as created_name ,role.created_at,role.status,group_concat(`em`.`name`) as employee_name,group_concat(`em`.`id`) as employee_id')
                            ->join('`erui_sys`.`role_member` rm on rm.role_id=role.id', 'left')
                            ->join('`erui_sys`.`employee` em on em.id=`rm`.`employee_id`', 'left')
                            ->join('`erui_sys`.`employee` emby on emby.id=role.created_by', 'left')
                            ->where($data)
                            ->limit( $limit['page']. ','. $limit['num'] )
                            ->group('role.id')
                            ->order($order)
                            ->select();
            return $res;
        }else{
            return $this->field('role.id,role.name,role.name_en,role_no,admin_show,role_group,role.remarks,role.created_by,emby.name as created_name ,role.created_at,role.status,group_concat(`em`.`name`) as employee_name,group_concat(`em`.`id`) as employee_id')
                ->join('`erui_sys`.`role_member` rm on rm.role_id=role.id', 'left')
                ->join('`erui_sys`.`employee` em on em.id=`rm`.`employee_id`', 'left')
                ->join('`erui_sys`.`employee` emby on emby.id=role.created_by', 'left')
                ->where($data)
                ->group('role.id')
                ->order($order)
                ->select();
        }
    }
    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRoleslist($id,$order='id desc') {

        $sql = 'SELECT `role_access_perm`.`func_perm_id`,`func_perm`.`url`, `func_perm`.`fn` , `func_perm`.`parent_id` ';
        $sql .= ' FROM '.$this->table_name;
        $sql .= ' LEFT JOIN  `role_access_perm` ON `role_access_perm`.`role_id` =`role`.`id`';
        $sql .= ' LEFT JOIN  `func_perm` ON `func_perm`.`id` =`role_access_perm`.`func_perm_id`';
        $sql_where = '';
        if(!empty($id)) {
            $sql_where .= ' WHERE `role`.`id` =' . $id;
            $sql .=$sql_where;
        }

//        if ( $where ){
//            $sql .= $sql_where;
//        }
        return $this->query( $sql );
    }
    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            $row = $this->where($where)
                ->field('id,role_no,admin_show,role_group,name,name_en,remarks,created_by,created_at,status')
                ->find();
            return $row;
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            return $this->where($where)
                ->save(['deleted_flag' => 'Y']);
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        $arr = $this->create($data);
        if(!empty($where)&&isset($arr)){
            $result =$this->where($where)->save($arr);
            if (false === $result) {
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }



    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        $data = $this->create($create);
        if($data){
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        return $this->add($data);
    }

}
