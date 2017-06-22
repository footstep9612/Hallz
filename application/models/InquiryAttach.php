<?php
/**
 * name: InquiryAttach
 * desc: 询价单附件表
 * User: zhangyuliang
 * Date: 2017/6/17
 * Time: 10:14
 */
class InquiryAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_attach'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     *
     */
    protected function getcondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['inquiry_no'])) {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        if (!empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
        if (!empty($condition['attach_name'])) {
            $where['attach_name'] = $condition['attach_name'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        return $this->where($where)->count('id');
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);
        $page = isset($condition['page'])?$condition['page']:1;
        $pagesize = isset($condition['countPerPage'])?$condition['countPerPage']:10;

        try {
            if (isset($page) && isset($pagesize)) {
                $count = $this->getcount($condition);
                return $this->where($where)->select();
                    //->page($page, $pagesize)
                    //->select();
            } else {
                return $this->where($where)->select();
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {
        $data = $this->create($createcondition);

        try {
            return $this->add($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($createcondition = []) {
        $data = $this->create($createcondition);
        $where['id'] = $createcondition['id'];
        $where['inquiry_no'] = $createcondition['inquiry_no'];

        try {
            return $this->where($where)->save($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($createcondition = []) {
        $where['id'] = $createcondition['id'];
        $where['inquiry_no'] = $createcondition['inquiry_no'];

        try {
            return $this->where($where)->delete();
        } catch (Exception $e) {
            return false;
        }
    }
}