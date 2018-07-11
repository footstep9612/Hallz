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
class System_UrlPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'func_perm';
    protected $dbName = 'erui_sys';
    Protected $autoCheckFields = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit = null, $order = 'sort') {
        if (!empty($limit)) {
            //,'false' as check
            return $this->field("id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,remarks,sort,parent_id,grant_flag,created_by,created_at,source")
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            //,'false' as `check`
            return $this->field("id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,url,remarks,sort,parent_id,grant_flag,created_by,created_at,source")
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取详情
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,url,sort,remarks,parent_id,grant_flag,created_by,created_at')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * @desc 获取指定菜单的一级父类菜单ID
     *
     * @param int $menuId 菜单ID
     * @return mixed
     * @author liujf
     * @time 2018-06-25
     */
    public function getOneLevelMenuId($menuId) {
        $parentId = $this->where(['id' => $menuId])->getField('parent_id');
        if ($parentId > 0) {
            return $this->getOneLevelMenuId($parentId);
        } else {
            return $menuId;
        }
    }

    /**
     * @desc 根据菜单名称获取菜单ID
     *
     * @param string $name 菜单名称
     * @return int
     * @author liujf
     * @time 2018-06-25
     */
    public function getMenuIdByName($name) {
        return $this->where(['fn' => $name, 'parent_id' => '0'])->getField('id') ?: 0;
    }

    public function update_data($data, $where) {
        $arr = $this->create($data);
        if (!empty($data['parent_id']) && $data['parent_id'] != $data['id']) {
            $arr['top_parent_id'] = $this->getOneLevelMenuId($data['parent_id']);
        } elseif (!empty($data['parent_id']) && $data['parent_id'] == $data['id']) {
            $arr['top_parent_id'] = $data['id'];
        } elseif (!empty($data['id'])) {
            $arr['top_parent_id'] = $this->getOneLevelMenuId($data['id']);
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

}